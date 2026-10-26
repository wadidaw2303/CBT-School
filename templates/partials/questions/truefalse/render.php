<?php
/**
 * True/False Question - Render Mode (Student Exam Taking)
 * Usage: @include('partials.questions.truefalse.render', ['question' => $question, 'exam' => $exam, 'index' => $index, 'answer' => $answer])
 * Expects:
 *   - $question (array): id, type, question_text, correct_answer, points, explanation, media_path
 *   - $exam (array|null): exam config
 *   - $index (int): question number (1-based)
 *   - $answer (array|null): student's saved answer ['answer' => 'true'] or null
 *   - $mode (string): 'exam' | 'review' | 'print'
 */

$question = $question ?? [];
$exam = $exam ?? [];
$index = $index ?? 1;
$answer = $answer ?? null;
$mode = $mode ?? 'exam';

$questionId = $question['id'] ?? 'q_' . $index;
$questionText = $question['question_text'] ?? '';
$correctAnswer = $question['correct_answer'] ?? 'true';
$points = $question['points'] ?? 0;
$explanation = $question['explanation'] ?? '';
$mediaPath = $question['media_path'] ?? '';

$isReview = $mode === 'review';
$isPrint = $mode === 'print';
$isExam = $mode === 'exam';

$selectedAnswer = $answer['answer'] ?? null;
$isAnswered = !is_null($selectedAnswer);
$isCorrect = $isReview && $selectedAnswer === $correctAnswer;
?>

<div
    class="question-card question-card--truefalse"
    data-question-id="<?= h_attr($questionId) ?>"
    data-question-type="truefalse"
    data-question-index="<?= $index ?>"
    data-points="<?= (int)$points ?>"
    data-correct-answer="<?= h_attr($correctAnswer) ?>"
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
                <span class="badge badge--<?= $isAnswered ? ($isCorrect ? 'success' : 'danger') : 'warning' ?> badge--sm question-card__status">
                    <?= $isAnswered ? ($isCorrect ? 'Benar' : 'Salah') : 'Tidak Dijawab' ?>
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

    <!-- Question Text -->
    <div class="question-card__text" id="q-<?= h_attr($questionId) ?>-text">
        <?= $questionText ?>
    </div>

    <!-- Options: True / False -->
    <div class="question-card__options tf-render-options" role="radiogroup" aria-label="Pilih Benar atau Salah">
        <?php foreach (['true' => 'BENAR', 'false' => 'SALAH'] as $value => $label): ?>
            <?php
            $isSelected = $selectedAnswer === $value;
            $isCorrectOpt = $correctAnswer === $value;
            $icon = $value === 'true'
                ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>'
                : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
            ?>
            <label
                class="tf-render-option
                    <?= $isSelected ? ' tf-render-option--selected' : '' ?>
                    <?= $isReview && $isCorrectOpt ? ' tf-render-option--correct' : '' ?>
                    <?= $isReview && $isSelected && !$isCorrectOpt ? ' tf-render-option--incorrect' : '' ?>"
            >
                <input
                    type="radio"
                    name="answers[<?= h_attr($questionId) ?>]"
                    value="<?= h_attr($value) ?>"
                    <?= $isSelected ? 'checked' : '' ?>
                    <?= $isReview || $isPrint ? 'disabled' : '' ?>
                    class="tf-render-option__input"
                    id="opt-<?= h_attr($questionId) ?>-<?= h_attr($value) ?>"
                    aria-label="<?= $label ?>"
                    data-option-value="<?= h_attr($value) ?>"
                >
                <span class="tf-render-option__visual" aria-hidden="true">
                    <span class="tf-render-option__icon"><?= $icon ?></span>
                    <span class="tf-render-option__label"><?= $label ?></span>
                </span>

                <?php if ($isReview): ?>
                    <?php if ($isCorrectOpt): ?>
                        <span class="tf-render-option__feedback tf-render-option__feedback--correct" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        </span>
                    <?php elseif ($isSelected): ?>
                        <span class="tf-render-option__feedback tf-render-option__feedback--incorrect" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            </label>
        <?php endforeach; ?>
    </div>

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

    <!-- Print Mode: Show correct answer -->
    <?php if ($isPrint): ?>
        <div class="question-card__print-answer" style="margin-top: var(--space-4); padding: var(--space-3); background: var(--color-bg-tertiary); border-radius: var(--radius-md); border-left: 3px solid var(--color-success);">
            <strong>Jawaban Benar: </strong>
            <span class="font-medium text-lg">
                <?= $correctAnswer === 'true' ? 'BENAR' : 'SALAH' ?>
            </span>
        </div>
    <?php endif; ?>
</div>

<?php if ($isExam): ?>
<script>
// TrueFalse Render - Exam mode interactions
(function() {
    const card = document.querySelector('.question-card--truefalse[data-question-id="<?= h_js($questionId) ?>"]');
    if (!card) return;

    const radios = card.querySelectorAll('input[type="radio"][name^="answers["]');
    const flagBtn = card.querySelector('.question-flag');

    // Radio change -> auto-save trigger
    radios.forEach(radio => {
        radio.addEventListener('change', () => {
            card.dataset.answered = 'true';
            window.dispatchEvent(new CustomEvent('exam:answer-changed', {
                detail: { questionId: '<?= h_js($questionId) ?>', value: { answer: radio.value } }
            }));
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