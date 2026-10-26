<?php
/**
 * Essay Question - Render Mode (Student Exam Taking)
 * Usage: @include('partials.questions.essay.render', ['question' => $question, 'exam' => $exam, 'index' => $index, 'answer' => $answer])
 * Expects:
 *   - $question (array): id, type, question_text, rubric, word_limit, char_limit, points, explanation, media_path
 *   - $exam (array|null): exam config
 *   - $index (int): question number (1-based)
 *   - $answer (array|null): student's saved answer ['text' => '...', 'word_count' => 123, 'char_count' => 456] or null
 *   - $mode (string): 'exam' | 'review' | 'print'
 */

$question = $question ?? [];
$exam = $exam ?? [];
$index = $index ?? 1;
$answer = $answer ?? null;
$mode = $mode ?? 'exam';

$questionId = $question['id'] ?? 'q_' . $index;
$questionText = $question['question_text'] ?? '';
$rubric = $question['rubric'] ?? [];
$wordLimit = $question['word_limit'] ?? 0;
$charLimit = $question['char_limit'] ?? 0;
$points = $question['points'] ?? 0;
$explanation = $question['explanation'] ?? '';
$mediaPath = $question['media_path'] ?? '';

$isReview = $mode === 'review';
$isPrint = $mode === 'print';
$isExam = $mode === 'exam';

$answerText = $answer['text'] ?? '';
$wordCount = $answer['word_count'] ?? 0;
$charCount = $answer['char_count'] ?? 0;
$isAnswered = !empty(trim($answerText));

// For review mode: score from grading
$score = null;
$graded = false;
if ($isReview && isset($answer['score'])) {
    $score = $answer['score'];
    $graded = true;
}
?>

<div
    class="question-card question-card--essay"
    data-question-id="<?= h_attr($questionId) ?>"
    data-question-type="essay"
    data-question-index="<?= $index ?>"
    data-points="<?= (int)$points ?>"
    data-word-limit="<?= (int)$wordLimit ?>"
    data-char-limit="<?= (int)$charLimit ?>"
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
                <span class="badge badge--<?= $graded ? ($score >= ($points * 0.7) ? 'success' : 'warning') : ($isAnswered ? 'info' : 'secondary') ?> badge--sm question-card__status">
                    <?= $graded ? 'Terkoreksi: ' . (int)$score . '/' . (int)$points . ' poin' : ($isAnswered ? 'Dijawab' : 'Tidak Dijawab') ?>
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

    <!-- Limits Display (Exam/Review mode) -->
    <?php if (($wordLimit > 0 || $charLimit > 0) && ($isExam || $isReview)): ?>
        <div class="essay-limits" aria-live="polite">
            <?php if ($wordLimit > 0): ?>
                <span class="essay-limit essay-limit--words">
                    <span class="essay-limit__count" id="wc-<?= h_attr($questionId) ?>"><?= $wordCount ?></span> / <?= $wordLimit ?> kata
                </span>
            <?php endif; ?>
            <?php if ($charLimit > 0): ?>
                <span class="essay-limit essay-limit--chars">
                    <span class="essay-limit__count" id="cc-<?= h_attr($questionId) ?>"><?= $charCount ?></span> / <?= $charLimit ?> karakter
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Answer Area -->
    <div class="question-card__answer">
        <?php if ($isExam): ?>
            <textarea
                name="answers[<?= h_attr($questionId) ?>][text]"
                class="form-control essay-textarea"
                id="answer-<?= h_attr($questionId) ?>"
                rows="8"
                placeholder="Tulis jawaban Anda di sini..."
                data-word-limit="<?= (int)$wordLimit ?>"
                data-char-limit="<?= (int)$charLimit ?>"
                aria-describedby="<?= ($wordLimit > 0 || $charLimit > 0) ? 'limits-' . h_attr($questionId) : '' ?>"
            ><?= h($answerText) ?></textarea>
            <?php if ($wordLimit > 0 || $charLimit > 0): ?>
                <div id="limits-<?= h_attr($questionId) ?>" class="essay-limits essay-limits--live" aria-live="polite" aria-atomic="true">
                    <?php if ($wordLimit > 0): ?>
                        <span class="essay-limit essay-limit--words">
                            <span class="essay-limit__count">0</span> / <?= $wordLimit ?> kata
                        </span>
                    <?php endif; ?>
                    <?php if ($charLimit > 0): ?>
                        <span class="essay-limit essay-limit--chars">
                            <span class="essay-limit__count">0</span> / <?= $charLimit ?> karakter
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php elseif ($isReview): ?>
            <div class="essay-review-answer">
                <div class="essay-review-answer__header">
                    <span class="badge badge--outline">Jawaban Siswa</span>
                    <?php if ($graded): ?>
                        <span class="badge badge--<?= $score >= ($points * 0.7) ? 'success' : 'warning' ?>">
                            Skor: <?= (int)$score ?> / <?= (int)$points ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="essay-review-answer__content"><?= nl2br(h($answerText)) ?: '<em class="text-text-tertiary">(tidak dijawab)</em>' ?></div>
                <?php if ($wordLimit > 0 || $charLimit > 0): ?>
                    <div class="essay-review-answer__stats">
                        <?php if ($wordLimit > 0): ?>
                            <span><?= $wordCount ?> / <?= $wordLimit ?> kata</span>
                        <?php endif; ?>
                        <?php if ($charLimit > 0): ?>
                            <span><?= $charCount ?> / <?= $charLimit ?> karakter</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif ($isPrint): ?>
            <div class="essay-print-answer">
                <label>Jawaban:</label>
                <div class="essay-print-answer__lines">
                    <?php for ($i = 0; $i < 10; $i++): ?>
                        <div class="essay-print-line"></div>
                    <?php endfor; ?>
                </div>
                <?php if ($wordLimit > 0 || $charLimit > 0): ?>
                    <p class="form-hint mt-2">Batas: <?= $wordLimit > 0 ? $wordLimit . ' kata' : '' ?> <?= $wordLimit > 0 && $charLimit > 0 ? ', ' : '' ?> <?= $charLimit > 0 ? $charLimit . ' karakter' : '' ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Rubric (Review/Print mode) -->
    <?php if (($isReview || $isPrint) && !empty($rubric)): ?>
        <details class="question-card__rubric" open>
            <summary class="question-card__rubric-toggle">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                Rubrik Penilaian
            </summary>
            <table class="table table--sm question-card__rubric-table">
                <thead>
                    <tr>
                        <th>Kriteria</th>
                        <th style="width: 80px;">Maks Poin</th>
                        <?php if ($isReview && $graded): ?>
                            <th style="width: 80px;">Skor</th>
                        <?php endif; ?>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rubric as $crit): ?>
                        <tr>
                            <td><?= h($crit['criterion'] ?? '-') ?></td>
                            <td class="text-center"><?= (int)($crit['max_points'] ?? 0) ?></td>
                            <?php if ($isReview && $graded && isset($answer['rubric_scores'][$crit['criterion']])): ?>
                                <td class="text-center font-medium"><?= (int)$answer['rubric_scores'][$crit['criterion']] ?></td>
                            <?php elseif ($isReview && $graded): ?>
                                <td class="text-center text-text-tertiary">-</td>
                            <?php endif; ?>
                            <td><?= h($crit['description'] ?? '-') ?></td>
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
                Penjelasan / Jawaban Model
            </summary>
            <div class="question-card__explanation-content">
                <?= $explanation ?>
            </div>
        </details>
    <?php endif; ?>
</div>

<?php if ($isExam): ?>
<script>
// Essay Render - Exam mode interactions
(function() {
    const card = document.querySelector('.question-card--essay[data-question-id="<?= h_js($questionId) ?>"]');
    if (!card) return;

    const textarea = card.querySelector('.essay-textarea');
    const flagBtn = card.querySelector('.question-flag');
    const wordLimit = parseInt(card.dataset.wordLimit, 10) || 0;
    const charLimit = parseInt(card.dataset.charLimit, 10) || 0;
    const wcDisplay = card.querySelector('#wc-<?= h_js($questionId) ?>');
    const ccDisplay = card.querySelector('#cc-<?= h_js($questionId) ?>');
    const liveLimits = card.querySelector('.essay-limits--live');

    function countWords(text) {
        return text.trim() ? text.trim().split(/\s+/).length : 0;
    }

    function updateCounters() {
        const text = textarea.value;
        const wc = countWords(text);
        const cc = text.length;

        if (wcDisplay) wcDisplay.textContent = wc;
        if (ccDisplay) ccDisplay.textContent = cc;

        if (liveLimits) {
            const wcEl = liveLimits.querySelector('.essay-limit--words .essay-limit__count');
            const ccEl = liveLimits.querySelector('.essay-limit--chars .essay-limit__count');
            if (wcEl) wcEl.textContent = wc;
            if (ccEl) ccEl.textContent = cc;

            // Visual warning
            if (wordLimit > 0 && wc > wordLimit) {
                liveLimits.querySelector('.essay-limit--words')?.classList.add('essay-limit--exceeded');
            } else {
                liveLimits.querySelector('.essay-limit--words')?.classList.remove('essay-limit--exceeded');
            }
            if (charLimit > 0 && cc > charLimit) {
                liveLimits.querySelector('.essay-limit--chars')?.classList.add('essay-limit--exceeded');
            } else {
                liveLimits.querySelector('.essay-limit--chars')?.classList.remove('essay-limit--exceeded');
            }
        }

        card.dataset.answered = text.trim() ? 'true' : 'false';
    }

    // Auto-save on input (debounced)
    let saveTimer = null;
    textarea?.addEventListener('input', () => {
        updateCounters();
        clearTimeout(saveTimer);
        saveTimer = setTimeout(() => {
            window.dispatchEvent(new CustomEvent('exam:answer-changed', {
                detail: {
                    questionId: '<?= h_js($questionId) ?>',
                    value: {
                        text: textarea.value,
                        word_count: countWords(textarea.value),
                        char_count: textarea.value.length
                    }
                }
            }));
        }, 800);
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

    // Initial
    updateCounters();
})();
</script>
<?php endif; ?>