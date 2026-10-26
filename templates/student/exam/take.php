<?php
/**
 * Exam Taking Page - Student View
 * Uses question partials for rendering each question type
 * Layout: templates/layouts/exam.php
 */

// Bootstrap
require_once __DIR__ . '/../../../src/bootstrap.php';

use Core\View;
use Core\Database;
use Core\Session;

// Ensure required variables are set
$exam = $exam ?? [];
$participant = $participant ?? [];
$questions = $questions ?? [];
$examConfig = $examConfig ?? [];

// Load student's saved answers for this exam
$answers = [];
if (!empty($participant['id']) && !empty($exam['id'])) {
    $db = Database::getInstance();
    // participant is exam_participants record, link to attempts via exam_participant_id
    $saved = $db->fetchAll(
        "SELECT a.question_id, a.answer FROM attempts a WHERE a.exam_participant_id = ?",
        [$participant['id']]
    );
    foreach ($saved as $row) {
        $answers[$row['question_id']] = $row['answer'] ?? null;
    }
}

// School config
$schoolConfig = [];

// Normalize question types to match partial names
$typeMap = [
    'mcq_single' => 'mcq',
    'mcq_multiple' => 'multiresponse',
    'true_false' => 'truefalse',
    'fill_blank' => 'fillblank',
    'matching' => 'matching',
    'ordering' => 'ordering',
    'essay' => 'essay',
];

// Prepare data for layout
$pageTitle = $exam['title'] ?? 'Ujian';
$examId = $exam['id'] ?? '';
$participantId = $participant['id'] ?? '';
$durationMinutes = $exam['duration_minutes'] ?? $examConfig['duration_minutes'] ?? 60;
$shuffleQuestions = $examConfig['shuffle_questions'] ?? false;
$shuffleOptions = $examConfig['shuffle_options'] ?? false;
$showPoints = $examConfig['show_points'] ?? true;
$allowFlagging = $examConfig['allow_flagging'] ?? true;

// Apply question shuffling if enabled
if ($shuffleQuestions) {
    $seed = crc32($examId . '_' . $participantId);
    $shuffled = $questions;
    for ($i = count($shuffled) - 1; $i > 0; $i--) {
        $seed = ($seed * 1664525 + 1013904223) & 0xffffffff;
        $j = $seed % ($i + 1);
        [$shuffled[$i], $shuffled[$j]] = [$shuffled[$j], $shuffled[$i]];
    }
    $questions = $shuffled;
}

// Build question pages data
$questionPages = [];
foreach ($questions as $index => $question) {
    $type = $typeMap[$question['type']] ?? $question['type'];
    $questionId = $question['id'] ?? 'q_' . ($index + 1);
    
    // Get student's saved answer for this question
    $answer = $answers[$questionId] ?? null;
    
    // Prepare question data for partial
    $questionData = [
        'id' => $questionId,
        'type' => $question['type'],
        'question_text' => $question['content']['question'] ?? $question['content']['text'] ?? '',
        'points' => $question['points'] ?? 1,
        'explanation' => $question['explanation'] ?? '',
        'media_path' => $question['media_path'] ?? '',
    ];

    // Add type-specific data
    switch ($type) {
        case 'mcq':
            $questionData['options'] = $question['content']['options'] ?? [];
            $questionData['correct_option'] = $question['content']['correct_option'] ?? '';
            break;
        case 'multiresponse':
            $questionData['options'] = $question['content']['options'] ?? [];
            $questionData['min_selections'] = $question['settings']['min_selections'] ?? 1;
            $questionData['max_selections'] = $question['settings']['max_selections'] ?? 0;
            break;
        case 'truefalse':
            $questionData['correct_answer'] = $question['content']['correct_answer'] ?? 'true';
            break;
        case 'essay':
            $questionData['rubric'] = $question['content']['rubric'] ?? [];
            $questionData['word_limit'] = $question['settings']['max_words'] ?? 0;
            $questionData['char_limit'] = $question['settings']['max_chars'] ?? 0;
            break;
        case 'fillblank':
            $questionData['blanks'] = $question['content']['blanks'] ?? [];
            $questionData['case_sensitive'] = $question['settings']['case_sensitive'] ?? false;
            break;
        case 'matching':
            $questionData['pairs'] = $question['content']['pairs'] ?? [];
            break;
        case 'ordering':
            $questionData['items'] = $question['content']['items'] ?? [];
            break;
    }

    $questionPages[] = [
        'index' => $index,
        'type' => $type,
        'question' => $questionData,
        'answer' => $answer,
        'exam' => [
            'shuffle_options' => $shuffleOptions,
            'show_points' => $showPoints,
            'allow_flagging' => $allowFlagging,
        ],
        'mode' => 'exam',
    ];
}

// Type labels for palette/tooltip
$typeLabels = [
    'mcq' => 'Pilihan Ganda',
    'multiresponse' => 'Jawaban Berganda',
    'truefalse' => 'Benar/Salah',
    'essay' => 'Essay',
    'fillblank' => 'Isian',
    'matching' => 'Pencocokan',
    'ordering' => 'Pengurutan',
];

// Pass to layout (exam.php is a full page, not a layout wrapper)
View::share('exam', (object)[
    'id' => $examId,
    'title' => $pageTitle,
    'duration_minutes' => $durationMinutes,
]);
View::share('participant', (object)$participant);
View::share('questionPages', $questionPages);
View::share('totalQuestions', count($questionPages));
View::share('examConfig', [
    'shuffle_questions' => $shuffleQuestions,
    'shuffle_options' => $shuffleOptions,
    'show_points' => $showPoints,
    'allow_flagging' => $allowFlagging,
]);
View::share('schoolConfig', $schoolConfig);
View::share('typeLabels', $typeLabels);
View::share('rtl', false);

// Render the exam layout
echo View::render('layouts/exam');