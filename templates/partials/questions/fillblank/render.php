<?php
/**
 * Fill in the Blank Question - Render Mode (Student Exam Taking)
 * Usage: @include('partials.questions.fillblank.render', ['question' => $question, 'exam' => $exam, 'index' => $index, 'answer' => $answer])
 * Expects:
 *   - $question (array): id, type, question_text, blanks, case_sensitive, points, explanation, media_path
 *   - $exam (array|null): exam config
 *   - $index (int): question number (1-based)
 *   - $answer (array|null): student's saved answer ['blanks' => ['1' => 'jakarta', '2' => 'indonesia']] or null
 *   - $mode (string): 'exam' | 'review' | 'print'
 */

$question = $question ?? [];
$exam = $exam ?? [];
$index = $index ?? 1;
$answer = $answer ?? null;
$mode = $mode ?? 'exam';

$questionId = $question['id'] ?? 'q_' . $index;
$questionText = $question['question_text'] ?? '';
$blanks = $question['blanks'] ?? [];
$caseSensitive = $question['case_sensitive'] ?? false;
$points = $question['points'] ?? 0;
$explanation = $question['explanation'] ?? '';
$mediaPath = $question['media_path'] ?? '';

$isReview = $mode === 'review';
$isPrint = $mode === 'print';
$isExam = $mode === 'exam';

$studentBlanks = $answer['blanks'] ?? [];
$isAnswered = !empty($studentBlanks);

// For review: calculate score
$score = null;
$graded = false;
$correctCount = 0;
$blankResults = [];
if ($isReview && isset($answer['score'])) {
    $score = $answer['score'];
    $graded = true;
    $correctCount = $answer['correct_count'] ?? 0;
    $blankResults = $answer['blank_results'] ?? []; // ['1' => true/false, ...]
}
?>

<div
    class="question-card question-card--fillblank"
    data-question-id="<?= h_attr($questionId) ?>"
    data-question-type="fillblank"
    data-question-index="<?= $index ?>"
    data-points="<?= (int)$points ?>"
    data-case-sensitive="<?= $caseSensitive ? 'true' : 'false' ?>"
    <?= $isAnswered ? 'data-answered="true"' : '' ?>
    <?= $isReview ? 'data-review="true"' : '' ?>
    <?= $isPrint ? 'data-print="true"' : '' ?>
    role="group"
    aria-labelledby="q-<?= h_attr($questionId) ?>-label"
>
    <!-- Question Header -->
    <header class="question-card__header">
        <div class="question-card__meta">
            <span id="q-<?= h_attr($questionId) ?>-label" class="question-card__number">
                Soal <?= $index ?>
            </span>
            <?php if ($points && ($isExam || $isPrint)): ?>
                <span class="question-card__points" aria-label="Bobot soal"><?= (int)$points ?> poin</span>
            <?php endif; ?>
            <?php if ($isReview): ?>
                <span class="badge badge--<?= $graded ? ($correctCount === count($blanks) ? 'success' : ($correctCount > 0 ? 'warning' : 'danger')) : ($isAnswered ? 'info' : 'secondary') ?> badge--sm question-card__status">
                    <?= $graded ? "Terkoreksi: {$correctCount}/" . count($blanks) . " benar" : ($isAnswered ? 'Dijawab' : 'Tidak Dijawab') ?>
                </span>
            <?php endif; ?>
        </div>
        <?php if ($isExam && can('assessment.exams.flag')): ?>
            <button
                type="button"
                class="btn btn--ghost btn--sm btn-icon question-flag"
                data-question-id="<?= h_attr($questionId) ?>"
                aria-label="Tandai soal ini untuk direview"
                title="Tandai (Alt+F)"
            >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
            </button>
        <?php endif; ?>
    </header>

    <!-- Media (if any) -->
    <?php if ($mediaPath): ?>
        <div class="question-card__media">
            <?php
            $ext = strtolower(pathinfo($mediaPath, PATHINFO_EXTENSION));
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
            $isVideo = in_array($ext, ['mp4', 'webm', 'ogg']);
            $isAudio = in_array($ext, ['mp3', 'wav', 'ogg', 'm4a']);
            $isPdf = $ext === 'pdf';
            ?>
            <?php if ($isImage): ?>
                <img src="<?= asset($mediaPath) ?>" alt="Media soal" loading="lazy" class="question-media__image">
            <?php elseif ($isVideo): ?>
                <video controls class="question-media__video" preload="metadata">
                    <source src="<?= asset($mediaPath) ?>" type="video/<?= $ext === 'mp4' ? 'mp4' : $ext ?>">
                </video>
            <?php elseif ($isAudio): ?>
                <audio controls class="question-media__audio" preload="metadata">
                    <source src="<?= asset($mediaPath) ?>" type="audio/<?= $ext === 'mp3' ? 'mpeg' : $ext ?>">
                </audio>
            <?php elseif ($isPdf): ?>
                <a href="<?= asset($mediaPath) ?>" target="_blank" class="question-media__pdf">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    <span>Lihat PDF: <?= h(basename($mediaPath)) ?></span>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Question Text with Input Fields -->
    <div class="question-card__text fillblank-render__text" id="q-<?= h_attr($questionId) ?>-text">
        <?php
        // Replace {1}, {2}... with input fields or answer display
        $displayText = $questionText;
        $blankNum = 1;
        // Use preg_replace_callback to handle each blank
        $displayText = preg_replace_callback('/\{(\d+)\}/', function($matches) use ($questionId, $blanks, $studentBlanks, $isExam, $isReview, $isPrint, $blankResults, $caseSensitive) {
            $num = $matches[1];
            $blank = $blanks[$num - 1] ?? null;
            $studentAnswer = $studentBlanks[$num] ?? '';
            $isCorrect = $isReview && isset($blankResults[$num]) && $blankResults[$num];
            $isIncorrect = $isReview && isset($studentBlanks[$num]) && !$isCorrect;

            $correctAnswer = $blank['answer'] ?? '';
            $alternatives = $blank['alternatives'] ?? '';
            $allAnswers = array_filter(array_merge([$correctAnswer], explode('|', $alternatives)));

            if ($isExam) {
                return '<input'
                    . ' type="text"'
                    . ' name="answers[' . h_attr($questionId) . '][blanks][' . h_attr($num) . ']"'
                    . ' class="fillblank-render__input' . ($studentAnswer ? ' fillblank-render__input--filled' : '') . '"'
                    . ' value="' . h_attr($studentAnswer) . '"'
                    . ' placeholder="..."'
                    . ' data-blank="' . h_attr($num) . '"'
                    . ' autocomplete="off"'
                    . ' aria-label="Isi blank ' . $num . '"'
                    . '>';
            } elseif ($isReview) {
                $displayAnswer = $studentAnswer !== '' ? h($studentAnswer) : '<em class="text-text-tertiary">(kosong)</em>';
                $icon = $isCorrect
                    ? '<svg class="fillblank-render__icon fillblank-render__icon--correct" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>'
                    : ($isIncorrect
                        ? '<svg class="fillblank-render__icon fillblank-render__icon--incorrect" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'
                        : '');
                return '<span class="fillblank-render__answer-display fillblank-render__answer-display--review'
                    . ($isCorrect ? ' fillblank-render__answer-display--correct' : '')
                    . ($isIncorrect ? ' fillblank-render__answer-display--incorrect' : '') . '">'
                    . '<span class="fillblank-render__blank-label">{' . $num . '}</span>'
                    . '<span class="fillblank-render__student-answer">' . $displayAnswer . '</span>'
                    . $icon
                    . '</span>';
            } else { // print
                return '<span class="fillblank-render__answer-display fillblank-render__answer-display--print">'
                    . '<span class="fillblank-render__blank-label">{' . $num . '}</span>'
                    . '<span class="fillblank-render__print-line"></span>'
                    . '</span>';
            }
        }, $displayText);

        echo $displayText;
        ?>
    </div>

    <?php if ($isReview && $graded && !empty($blankResults)): ?>
        <!-- Blank-by-blank result summary -->
        <details class="question-card__rubric" open>
            <summary class="question-card__rubric-toggle">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                Detail Per Blank
            </summary>
            <table class="table table--sm question-card__rubric-table">
                <thead>
                    <tr><th>Blank</th><th>Jawaban Siswa</th><th>Kunci Jawaban</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($blanks as $i => $blank): ?>
                        $num = $i + 1;
                        $studentAns = $studentBlanks[$num] ?? '';
                        $isCorrect = isset($blankResults[$num]) && $blankResults[$num];
                        $correctAns = $blank['answer'] ?? '';
                        $alts = $blank['alternatives'] ?? '';
                        $allAnswers = array_filter(array_merge([$correctAns], explode('|', $alts)));
                    ?>
                        <tr>
                            <td>{<?= $num ?>}</td>
                            <td><?= $studentAns !== '' ? h($studentAns) : '<em class="text-text-tertiary">(kosong)</em>' ?></td>
                            <td><?= h(implode(' | ', $allAnswers)) ?></td>
                            <td>
                                <span class="badge badge--<?= $isCorrect ? 'success' : 'danger' ?> badge--sm">
                                    <?= $isCorrect ? 'Benar' : 'Salah' ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </details>
    <?php endif; ?>

    <!-- Explanation (Review Mode Only) -->
    <?php if ($isReview && $explanation): ?>
        <details class="question-card__explanation" open>
            <summary class="question-card__explanation-toggle">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                Penjelasan
            </summary>
            <div class="question-card__explanation-content">
                <?= $explanation ?>
            </div>
        </details>
    <?php endif; ?>

    <!-- Print Mode: Show answer key -->
    <?php if ($isPrint): ?>
        <div class="question-card__print-answer" style="margin-top: var(--space-4); padding: var(--space-3); background: var(--color-bg-tertiary); border-radius: var(--radius-md); border-left: 3px solid var(--color-success);">
            <strong>Kunci Jawaban:</strong>
            <table class="table table--sm mt-2">
                <thead><tr><th>Blank</th><th>Jawaban</th></tr></thead>
                <tbody>
                    <?php foreach ($blanks as $i => $blank): ?>
                        $num = $i + 1;
                        $correctAns = $blank['answer'] ?? '';
                        $alts = $blank['alternatives'] ?? '';
                        $allAnswers = array_filter(array_merge([$correctAns], explode('|', $alts)));
                    ?>
                        <tr>
                            <td>{<?= $num ?>}</td>
                            <td><?= h(implode(' | ', $allAnswers)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="form-hint mt-2"><em>Case-sensitive: <?= $caseSensitive ? 'Ya' : 'Tidak' ?></em></p>
        </div>
    <?php endif; ?>
</div>

<?php if ($isExam): ?>
<script>
// FillBlank Render - Exam mode interactions
(function() {
    const card = document.querySelector('.question-card--fillblank[data-question-id="<?= h_js($questionId) ?>"]');
    if (!card) return;

    const inputs = card.querySelectorAll('.fillblank-render__input');
    const flagBtn = card.querySelector('.question-flag');
    const caseSensitive = card.dataset.caseSensitive === 'true';

    // Input change -> auto-save trigger (debounced)
    let saveTimer = null;
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            const blanks = {};
            let filledCount = 0;
            inputs.forEach(inp => {
                if (inp.value.trim()) {
                    blanks[inp.dataset.blank] = inp.value.trim();
                    filledCount++;
                }
            });
            card.dataset.answered = filledCount > 0 ? 'true' : 'false';
            clearTimeout(saveTimer);
            saveTimer = setTimeout(() => {
                window.dispatchEvent(new CustomEvent('exam:answer-changed', {
                    detail: { questionId: '<?= h_js($questionId) ?>', value: { blanks } }
                }));
            }, 800);
        });
    });

    // Flag button
    flagBtn?.addEventListener('click', () => {
        card.classList.toggle('question-card--flagged');
        const isFlagged = card.classList.contains('question-card--flagged');
        flagBtn.setAttribute('aria-pressed', isFlagged);
        flagBtn.title = isFlagged ? 'Batalkan tanda (Alt+F)' : 'Tandai soal ini (Alt+F)';
        window.dispatchEvent(new CustomEvent('exam:flag-toggled', {
            detail: { questionId: '<?= h_js($questionId) ?>', flagged: isFlagged }
        }));
    });

    // Keyboard: Alt+F to flag
    const handleKeydown = (e) => {
        if (e.altKey && e.key.toLowerCase() === 'f') {
            e.preventDefault();
            flagBtn?.click();
        }
    };
    card.addEventListener('focusin', () => document.addEventListener('keydown', handleKeydown));
    card.addEventListener('focusout', () => document.removeEventListener('keydown', handleKeydown));
})();
</script>
<?php endif; ?>