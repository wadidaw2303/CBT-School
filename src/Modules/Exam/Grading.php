<?php

declare(strict_types=1);

namespace Modules\Exam;

use Core\Database;

/**
 * Exam Grading Module
 * Handles auto-grading for all question types except essay (manual)
 * 
 * Question types supported:
 * - mcq (mcq_single): Single correct answer
 * - multiresponse (mcq_multiple): Multiple correct answers
 * - truefalse: True/False
 * - fillblank: Fill in the blank (exact match, case-insensitive by default)
 * - matching: Match left items to right items
 * - ordering: Order items correctly
 * - essay: Manual grading only (handled separately)
 */

class Grading
{
    private Database $db;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Grade a single attempt for a question
     * Returns array with: is_correct, points_earned, score (0-100), details
     */
    public function gradeAttempt(array $question, array $attempt, array $examConfig = []): array
    {
        $type = $question['type'] ?? '';
        $points = (float)($question['points'] ?? 1);
        $content = $question['content'] ?? [];

        // Dispatch to type-specific grader
        return match ($type) {
            'mcq_single' => $this->gradeMcq($content, $attempt, $points),
            'mcq_multiple' => $this->gradeMultiResponse($content, $attempt, $points),
            'true_false' => $this->gradeTrueFalse($content, $attempt, $points),
            'fill_blank' => $this->gradeFillBlank($content, $attempt, $points, $examConfig),
            'matching' => $this->gradeMatching($content, $attempt, $points),
            'ordering' => $this->gradeOrdering($content, $attempt, $points),
            'essay' => $this->gradeEssay($content, $attempt, $points), // Returns ungraded marker
            default => [
                'is_correct' => null,
                'points_earned' => 0,
                'score' => 0,
                'details' => ['error' => 'Unknown question type: ' . $type],
                'needs_manual' => false,
            ],
        };
    }

    /**
     * Grade all attempts for an exam participant
     * Returns summary: total_score, max_score, percentage, is_passed, graded_count, pending_manual
     */
    public function gradeParticipant(int $examParticipantId, bool $updateDatabase = true): array
    {
        // Get exam info
        $participant = $this->db->fetch(
            "SELECT ep.*, e.passing_score, e.settings as exam_settings
             FROM exam_participants ep
             JOIN exams e ON ep.exam_id = e.id
             WHERE ep.id = ?",
            [$examParticipantId]
        );

        if (!$participant) {
            throw new \InvalidArgumentException("Exam participant not found: $examParticipantId");
        }

        // Get all attempts for this participant with question data
        $attempts = $this->db->fetchAll(
            "SELECT a.*, q.type, q.content, q.points, q.settings as question_settings, q.explanation
             FROM attempts a
             JOIN questions q ON a.question_id = q.id
             WHERE a.exam_participant_id = ?
             ORDER BY a.created_at",
            [$examParticipantId]
        );

        $examSettings = json_decode($participant['exam_settings'] ?? '{}', true) ?? [];
        $questionSettings = json_decode($participant['settings'] ?? '{}', true) ?? []; // legacy

        $totalScore = 0.0;
        $maxScore = 0.0;
        $gradedCount = 0;
        $pendingManual = 0;
        $results = [];

        foreach ($attempts as $attempt) {
            $question = [
                'id' => $attempt['question_id'],
                'type' => $attempt['type'],
                'content' => json_decode($attempt['content'], true) ?? [],
                'points' => $attempt['points'],
                'settings' => json_decode($attempt['question_settings'] ?? '{}', true) ?? [],
                'explanation' => $attempt['explanation'],
            ];

            $studentAnswer = json_decode($attempt['answer'] ?? 'null', true);
            $result = $this->gradeAttempt($question, $studentAnswer, $examSettings);

            $isCorrect = $result['is_correct'];
            $pointsEarned = $result['points_earned'];
            $score = $result['score'];
            $details = $result['details'];
            $needsManual = $result['needs_manual'] ?? false;

            $results[] = [
                'attempt_id' => $attempt['id'],
                'question_id' => $attempt['question_id'],
                'type' => $attempt['type'],
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
                'score' => $score,
                'details' => $details,
                'needs_manual' => $needsManual,
            ];

            $maxScore += (float)$attempt['points'];

            if ($needsManual) {
                $pendingManual++;
            } else {
                $gradedCount++;
                $totalScore += $pointsEarned;
            }
        }

        $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 2) : 0;
        $isPassed = $percentage >= (float)($participant['passing_score'] ?? 70);

        // Calculate grade letter
        $grade = $this->calculateGrade($percentage);

        if ($updateDatabase) {
            $this->saveGradingResults($examParticipantId, $results, $totalScore, $maxScore, $percentage, $isPassed, $grade, $pendingManual > 0);
        }

        return [
            'exam_participant_id' => $examParticipantId,
            'total_score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'is_passed' => $isPassed,
            'grade' => $grade,
            'graded_count' => $gradedCount,
            'pending_manual' => $pendingManual,
            'total_questions' => count($attempts),
            'results' => $results,
        ];
    }

    /**
     * Save grading results to database
     */
    private function saveGradingResults(
        int $examParticipantId,
        array $results,
        float $totalScore,
        float $maxScore,
        float $percentage,
        bool $isPassed,
        string $grade,
        bool $hasPendingManual
    ): void {
        $status = $hasPendingManual ? 'submitted' : 'graded';
        $gradedAt = $hasPendingManual ? null : date('Y-m-d H:i:s');
        $gradedBy = $hasPendingManual ? null : (auth()?->id() ?? null);

        $this->db->execute(
            "UPDATE exam_participants 
             SET score = ?, max_score = ?, percentage = ?, is_passed = ?, 
                 status = ?, graded_at = ?, graded_by = ?
             WHERE id = ?",
            [$totalScore, $maxScore, $percentage, $isPassed ? 1 : 0, $status, $gradedAt, $gradedBy, $examParticipantId]
        );

        // Update each attempt
        foreach ($results as $r) {
            if ($r['needs_manual']) {
                // Leave is_correct as NULL for manual grading
                $this->db->execute(
                    "UPDATE attempts SET is_correct = NULL, points_earned = 0, graded_at = ?, graded_by = ? WHERE id = ?",
                    [$gradedAt, $gradedBy, $r['attempt_id']]
                );
            } else {
                // Get correct answer from question for display
                $attempt = $this->db->fetch("SELECT q.content, q.type FROM attempts a JOIN questions q ON a.question_id = q.id WHERE a.id = ?", [$r['attempt_id']]);
                $correctAnswer = [];
                if ($attempt) {
                    $content = json_decode($attempt['content'], true) ?? [];
                    $type = $attempt['type'] ?? '';
                    $correctAnswer = match ($type) {
                        'mcq_single' => ['selected' => $content['correct_option'] ?? ''],
                        'mcq_multiple' => ['selected' => array_column(array_filter($content['options'] ?? [], fn($o) => !empty($o['is_correct'])), 'id')],
                        'true_false' => ['answer' => $content['correct_answer'] ?? 'true'],
                        'fill_blank' => ['blanks' => array_column($content['blanks'] ?? [], 'answer')],
                        'matching' => ['matches' => array_column($content['pairs'] ?? [], 'correct_right_idx', 'id')],
                        'ordering' => ['order' => array_column($content['items'] ?? [], 'correct_order', 'id')],
                        'essay' => ['text' => $content['answer'] ?? ''],
                        default => [],
                    };
                }
                $this->db->execute(
                    "UPDATE attempts SET is_correct = ?, points_earned = ?, correct_answer = ?, graded_at = ?, graded_by = ? WHERE id = ?",
                    [$r['is_correct'] ? 1 : 0, $r['points_earned'], json_encode($correctAnswer), $gradedAt, $gradedBy, $r['attempt_id']]
                );
            }
        }
    }

    /**
     * MCQ Single Choice Grading
     */
    private function gradeMcq(array $content, array $attempt, float $points): array
    {
        $correctOption = $content['correct_option'] ?? '';
        $selectedOption = $attempt['option_id'] ?? ($attempt['selected'] ?? '');

        $isCorrect = !empty($selectedOption) && $selectedOption === $correctOption;

        return [
            'is_correct' => $isCorrect,
            'points_earned' => $isCorrect ? $points : 0,
            'score' => $isCorrect ? 100 : 0,
            'details' => [
                'correct_option' => $correctOption,
                'selected_option' => $selectedOption,
            ],
            'needs_manual' => false,
        ];
    }

    /**
     * Multi-Response (Multiple Choice Multiple Answers) Grading
     * Scoring: Partial credit - each correct selection = points / total_correct
     * Penalty for incorrect selections (optional, configurable)
     */
    private function gradeMultiResponse(array $content, array $attempt, float $points): array
    {
        $options = $content['options'] ?? [];
        $selected = $attempt['selected'] ?? [];

        // Find correct options
        $correctOptionIds = [];
        foreach ($options as $opt) {
            if (!empty($opt['is_correct'])) {
                $correctOptionIds[] = $opt['id'] ?? '';
            }
        }

        $totalCorrect = count($correctOptionIds);
        if ($totalCorrect === 0) {
            return [
                'is_correct' => false,
                'points_earned' => 0,
                'score' => 0,
                'details' => ['error' => 'No correct options defined'],
                'needs_manual' => false,
            ];
        }

        $correctSelected = 0;
        $incorrectSelected = 0;

        foreach ($selected as $selId) {
            if (in_array($selId, $correctOptionIds, true)) {
                $correctSelected++;
            } else {
                $incorrectSelected++;
            }
        }

        $correctNotSelected = $totalCorrect - $correctSelected;

        // Scoring: each correct selection earns proportional points
        // Partial credit formula: (correct_selected - incorrect_selected) / total_correct * points
        // Minimum 0
        $rawScore = max(0, ($correctSelected - $incorrectSelected) / $totalCorrect);
        $pointsEarned = round($rawScore * $points, 2);
        $isCorrect = $correctSelected === $totalCorrect && $incorrectSelected === 0;

        return [
            'is_correct' => $isCorrect,
            'points_earned' => $pointsEarned,
            'score' => round($rawScore * 100),
            'details' => [
                'correct_selected' => $correctSelected,
                'incorrect_selected' => $incorrectSelected,
                'correct_not_selected' => $correctNotSelected,
                'total_correct' => $totalCorrect,
                'selected' => $selected,
                'correct_options' => $correctOptionIds,
            ],
            'needs_manual' => false,
        ];
    }

    /**
     * True/False Grading
     */
    private function gradeTrueFalse(array $content, array $attempt, float $points): array
    {
        $correctAnswer = $content['correct_answer'] ?? 'true';
        $selectedAnswer = $attempt['answer'] ?? null;

        $isCorrect = $selectedAnswer !== null && $selectedAnswer === $correctAnswer;

        return [
            'is_correct' => $isCorrect,
            'points_earned' => $isCorrect ? $points : 0,
            'score' => $isCorrect ? 100 : 0,
            'details' => [
                'correct_answer' => $correctAnswer,
                'selected_answer' => $selectedAnswer,
            ],
            'needs_manual' => false,
        ];
    }

    /**
     * Fill in the Blank Grading
     * Supports multiple correct answers per blank (alternatives separated by |)
     * Case sensitivity configurable per question
     */
    private function gradeFillBlank(array $content, array $attempt, float $points, array $examConfig = []): array
    {
        $blanks = $content['blanks'] ?? [];
        $studentBlanks = $attempt['blanks'] ?? [];
        $caseSensitive = $content['case_sensitive'] ?? ($examConfig['case_sensitive'] ?? false);

        if (empty($blanks)) {
            return [
                'is_correct' => false,
                'points_earned' => 0,
                'score' => 0,
                'details' => ['error' => 'No blanks defined'],
                'needs_manual' => false,
            ];
        }

        $totalBlanks = count($blanks);
        $correctCount = 0;
        $blankResults = [];

        foreach ($blanks as $i => $blank) {
            $num = $i + 1;
            $correctAnswer = trim((string)($blank['answer'] ?? ''));
            $alternatives = trim((string)($blank['alternatives'] ?? ''));
            
            // Build list of all acceptable answers
            $acceptableAnswers = array_filter(array_merge(
                [$correctAnswer],
                explode('|', $alternatives)
            ));

            // Normalize for case-insensitive comparison
            if (!$caseSensitive) {
                $acceptableAnswers = array_map('strtolower', $acceptableAnswers);
                $studentAnswer = strtolower(trim((string)($studentBlanks[$num] ?? '')));
            } else {
                $studentAnswer = trim((string)($studentBlanks[$num] ?? ''));
            }

            $isCorrect = in_array($studentAnswer, $acceptableAnswers, true);
            if ($isCorrect) {
                $correctCount++;
            }

            $blankResults[$num] = $isCorrect;
        }

        $scorePercentage = $totalBlanks > 0 ? round(($correctCount / $totalBlanks) * 100) : 0;
        $pointsEarned = round(($correctCount / $totalBlanks) * $points, 2);
        $isCorrect = $correctCount === $totalBlanks;

        return [
            'is_correct' => $isCorrect,
            'points_earned' => $pointsEarned,
            'score' => $scorePercentage,
            'details' => [
                'correct_count' => $correctCount,
                'total_blanks' => $totalBlanks,
                'blank_results' => $blankResults,
                'student_blanks' => $studentBlanks,
            ],
            'needs_manual' => false,
        ];
    }

    /**
     * Matching Grading
     * Each correct pair = points / total_pairs
     */
    private function gradeMatching(array $content, array $attempt, float $points): array
    {
        $pairs = $content['pairs'] ?? [];
        $studentMatches = $attempt['matches'] ?? [];

        if (empty($pairs)) {
            return [
                'is_correct' => false,
                'points_earned' => 0,
                'score' => 0,
                'details' => ['error' => 'No pairs defined'],
                'needs_manual' => false,
            ];
        }

        $totalPairs = count($pairs);
        $correctCount = 0;

        // pairs structure: [{id, left, right}, ...]
        // studentMatches: pair_id (left) => right_id
        foreach ($pairs as $pair) {
            $leftId = $pair['id'] ?? '';
            $correctRightIdx = $pair['correct_right_idx'] ?? 0;
            
            // Find the correct right item's ID
            $correctRightId = 'right_' . ($correctRightIdx + 1);
            
            $studentRightId = $studentMatches[$leftId] ?? null;
            
            if ($studentRightId === $correctRightId) {
                $correctCount++;
            }
        }

        $scorePercentage = $totalPairs > 0 ? round(($correctCount / $totalPairs) * 100) : 0;
        $pointsEarned = round(($correctCount / $totalPairs) * $points, 2);
        $isCorrect = $correctCount === $totalPairs;

        return [
            'is_correct' => $isCorrect,
            'points_earned' => $pointsEarned,
            'score' => $scorePercentage,
            'details' => [
                'correct_count' => $correctCount,
                'total_pairs' => $totalPairs,
                'student_matches' => $studentMatches,
            ],
            'needs_manual' => false,
        ];
    }

    /**
     * Ordering Grading
     * Each item in correct position = points / total_items
     */
    private function gradeOrdering(array $content, array $attempt, float $points): array
    {
        $items = $content['items'] ?? [];
        $studentOrder = $attempt['order'] ?? [];

        if (empty($items)) {
            return [
                'is_correct' => false,
                'points_earned' => 0,
                'score' => 0,
                'details' => ['error' => 'No items defined'],
                'needs_manual' => false,
            ];
        }

        $totalItems = count($items);
        $correctCount = 0;
        $itemResults = [];

        // items structure: [{id, text, correct_order}, ...]
        // studentOrder: item_id => position (1-based)
        foreach ($items as $item) {
            $itemId = $item['id'] ?? '';
            $correctPos = (int)($item['correct_order'] ?? 0);
            $studentPos = isset($studentOrder[$itemId]) ? (int)$studentOrder[$itemId] : null;

            $isCorrect = $studentPos !== null && $studentPos === $correctPos;
            if ($isCorrect) {
                $correctCount++;
            }
            $itemResults[$itemId] = $isCorrect;
        }

        $scorePercentage = $totalItems > 0 ? round(($correctCount / $totalItems) * 100) : 0;
        $pointsEarned = round(($correctCount / $totalItems) * $points, 2);
        $isCorrect = $correctCount === $totalItems;

        return [
            'is_correct' => $isCorrect,
            'points_earned' => $pointsEarned,
            'score' => $scorePercentage,
            'details' => [
                'correct_count' => $correctCount,
                'total_items' => $totalItems,
                'item_results' => $itemResults,
                'student_order' => $studentOrder,
            ],
            'needs_manual' => false,
        ];
    }

    /**
     * Essay Grading - Returns marker for manual grading
     */
    private function gradeEssay(array $content, array $attempt, float $points): array
    {
        $answerText = $attempt['text'] ?? '';
        $isAnswered = !empty(trim($answerText));

        return [
            'is_correct' => null, // NULL = pending manual grading
            'points_earned' => 0,
            'score' => 0,
            'details' => [
                'answer_text' => $answerText,
                'word_count' => $attempt['word_count'] ?? 0,
                'char_count' => $attempt['char_count'] ?? 0,
                'is_answered' => $isAnswered,
            ],
            'needs_manual' => true,
        ];
    }

    /**
     * Calculate letter grade from percentage
     */
    private function calculateGrade(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'A',
            $percentage >= 80 => 'B',
            $percentage >= 70 => 'C',
            $percentage >= 60 => 'D',
            default => 'E',
        };
    }

    /**
     * Manual grading for essay questions
     * Called by teacher when grading essays
     */
    public function gradeEssayManually(
        int $attemptId,
        float $score,
        float $maxScore,
        string $feedback = '',
        array $rubricScores = [],
        int $gradedBy = null
    ): bool {
        $gradedBy = $gradedBy ?? (auth()?->id() ?? 0);

        // Validate score range
        $score = max(0, min($score, $maxScore));

        // Insert or update essay_gradings
        $existing = $this->db->fetch(
            "SELECT id FROM essay_gradings WHERE attempt_id = ?",
            [$attemptId]
        );

        $rubricJson = !empty($rubricScores) ? json_encode($rubricScores) : null;

        if ($existing) {
            $this->db->execute(
                "UPDATE essay_gradings SET score = ?, max_score = ?, feedback = ?, rubric_scores = ?, graded_by = ?, graded_at = NOW() WHERE attempt_id = ?",
                [$score, $maxScore, $feedback, $rubricJson, $gradedBy, $attemptId]
            );
        } else {
            $this->db->execute(
                "INSERT INTO essay_gradings (attempt_id, score, max_score, feedback, rubric_scores, graded_by, graded_at) VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [$attemptId, $score, $maxScore, $feedback, $rubricJson, $gradedBy]
            );
        }

        // Update attempt
        $percentage = $maxScore > 0 ? ($score / $maxScore) * 100 : 0;
        $isCorrect = $percentage >= 70; // Default passing threshold for essay

        $this->db->execute(
            "UPDATE attempts SET is_correct = ?, points_earned = ?, graded_at = NOW(), graded_by = ? WHERE id = ?",
            [$isCorrect ? 1 : 0, $score, $gradedBy, $attemptId]
        );

        return true;
    }

    /**
     * Check if participant has all questions graded (no pending manual)
     */
    public function isFullyGraded(int $examParticipantId): bool
    {
        $pending = $this->db->fetch(
            "SELECT COUNT(*) as cnt FROM attempts 
             WHERE exam_participant_id = ? AND is_correct IS NULL",
            [$examParticipantId]
        );
        return ($pending['cnt'] ?? 0) === 0;
    }

    /**
     * Release grades for an exam participant (change status from submitted to graded)
     */
    public function releaseGrades(int $examParticipantId, int $releasedBy = null): bool
    {
        $releasedBy = $releasedBy ?? (auth()?->id() ?? 0);

        $participant = $this->db->fetch(
            "SELECT * FROM exam_participants WHERE id = ?",
            [$examParticipantId]
        );

        if (!$participant) {
            return false;
        }

        $this->db->execute(
            "UPDATE exam_participants SET status = 'graded', graded_at = NOW(), graded_by = ? WHERE id = ?",
            [$releasedBy, $examParticipantId]
        );

        // Update all ungraded attempts as graded
        $this->db->execute(
            "UPDATE attempts SET graded_at = NOW(), graded_by = ? WHERE exam_participant_id = ? AND graded_at IS NULL",
            [$releasedBy, $examParticipantId]
        );

        return true;
    }

    /**
     * Bulk release grades for all participants of an exam
     */
    public function releaseGradesForExam(int $examId, int $releasedBy = null): int
    {
        $releasedBy = $releasedBy ?? (auth()?->id() ?? 0);

        $participants = $this->db->fetchAll(
            "SELECT id FROM exam_participants WHERE exam_id = ? AND status IN ('submitted', 'in_progress')",
            [$examId]
        );

        $count = 0;
        foreach ($participants as $p) {
            if ($this->releaseGrades($p['id'], $releasedBy)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get grading summary for an exam (admin/teacher view)
     */
    public function getExamGradingSummary(int $examId): array
    {
        $stats = $this->db->fetch(
            "SELECT 
                COUNT(DISTINCT ep.id) as total_participants,
                COUNT(DISTINCT CASE WHEN ep.status = 'graded' THEN ep.id END) as graded,
                COUNT(DISTINCT CASE WHEN ep.status = 'submitted' THEN ep.id END) as submitted,
                COUNT(DISTINCT CASE WHEN ep.status = 'in_progress' THEN ep.id END) as in_progress,
                COUNT(DISTINCT CASE WHEN ep.status = 'not_started' THEN ep.id END) as not_started,
                AVG(ep.percentage) as avg_percentage,
                MIN(ep.percentage) as min_percentage,
                MAX(ep.percentage) as max_percentage
             FROM exam_participants ep
             WHERE ep.exam_id = ?",
            [$examId]
        );

        // Question-level stats
        $questionStats = $this->db->fetchAll(
            "SELECT 
                q.id, q.type, q.content, q.points,
                COUNT(a.id) as attempts_count,
                AVG(CASE WHEN a.is_correct = 1 THEN 100 ELSE 0 END) as avg_correct,
                SUM(CASE WHEN a.is_correct = 1 THEN 1 ELSE 0 END) as correct_count,
                SUM(CASE WHEN a.is_correct IS NULL THEN 1 ELSE 0 END) as pending_manual
             FROM exam_questions eq
             JOIN questions q ON eq.question_id = q.id
             LEFT JOIN attempts a ON a.question_id = q.id
             LEFT JOIN exam_participants ep ON a.exam_participant_id = ep.id AND ep.exam_id = ?
             WHERE eq.exam_id = ?
             GROUP BY q.id, q.type, q.content, q.points",
            [$examId, $examId]
        );

        return [
            'exam_id' => $examId,
            'summary' => $stats,
            'questions' => $questionStats,
        ];
    }
}