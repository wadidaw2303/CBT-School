<?php
/**
 * Matching Question - Render Mode (Student Exam Taking)
 * Usage: @include('partials.questions.matching.render', ['question' => $question, 'exam' => $exam, 'index' => $index, 'answer' => $answer])
 * Expects:
 *   - $question (array): id, type, question_text, pairs, points, explanation, media_path
 *   - $exam (array|null): exam config
 *   - $index (int): question number (1-based)
 *   - $answer (array|null): student's saved answer ['matches' => ['pair_1' => 2, 'pair_2' => 1, ...]] or null
 *   - $mode (string): 'exam' | 'review' | 'print'
 */

$question = $question ?? [];
$exam = $exam ?? [];
$index = $index ?? 1;
$answer = $answer ?? null;
$mode = $mode ?? 'exam';

$questionId = $question['id'] ?? 'q_' . $index;
$questionText = $question['question_text'] ?? '';
$pairs = $question['pairs'] ?? [];
$points = $question['points'] ?? 0;
$explanation = $question['explanation'] ?? '';
$mediaPath = $question['media_path'] ?? '';

$isReview = $mode === 'review';
$isPrint = $mode === 'print';
$isExam = $mode === 'exam';

// Student's matches: pair_id (left) => right_index (1-based)
$studentMatches = $answer['matches'] ?? [];
$isAnswered = !empty($studentMatches);

// For review: calculate score
$score = null;
$graded = false;
$correctCount = 0;
if ($isReview && isset($answer['score'])) {
    $score = $answer['score'];
    $graded = true;
    $correctCount = $answer['correct_count'] ?? 0;
}

// Prepare left items (fixed order) and right items (shuffled for exam)
$leftItems = [];
$rightItems = [];
foreach ($pairs as $i => $pair) {
    $leftItems[] = [
        'id' => $pair['id'] ?? 'pair_' . ($i + 1),
        'text' => $pair['left'] ?? '',
        'correct_right_idx' => $i, // 0-based index of correct right item
    ];
    $rightItems[] = [
        'id' => 'right_' . ($i + 1),
        'text' => $pair['right'] ?? '',
        'original_idx' => $i,
    ];
}

// Shuffle right items for exam mode (deterministic based on questionId)
if ($isExam) {
    // Simple deterministic shuffle using questionId
    $seed = crc32($questionId);
    $shuffledIndices = range(0, count($rightItems) - 1);
    // Fisher-Yates with seed
    for ($i = count($shuffledIndices) - 1; $i > 0; $i--) {
        $seed = ($seed * 1664525 + 1013904223) & 0xffffffff;
        $j = $seed % ($i + 1);
        [$shuffledIndices[$i], $shuffledIndices[$j]] = [$shuffledIndices[$j], $shuffledIndices[$i]];
    }
    $rightItems = array_map(fn($idx) => $rightItems[$idx], $shuffledIndices);
}

$letterLabels = [];
foreach ($leftItems as $i => $item) {
    $letterLabels[$item['id']] = String.fromCharCode(65 + $i); // A, B, C...
}
$numberLabels = [];
foreach ($rightItems as $i => $item) {
    $numberLabels[$item['id']] = $i + 1; // 1, 2, 3...
}
?>

<div
    class="question-card question-card--matching"
    data-question-id="<?= h_attr($questionId) ?>"
    data-question-type="matching"
    data-question-index="<?= $index ?>"
    data-points="<?= (int)$points ?>"
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
                <span class="badge badge--<?= $graded ? ($correctCount === count($leftItems) ? 'success' : ($correctCount > 0 ? 'warning' : 'danger')) : ($isAnswered ? 'info' : 'secondary') ?> badge--sm question-card__status">
                    <?= $graded ? "Terkoreksi: {$correctCount}/" . count($leftItems) . " benar" : ($isAnswered ? 'Dijawab' : 'Tidak Dijawab') ?>
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

    <!-- Matching Interface -->
    <div class="matching-render">
        <div class="matching-render__cols">
            <!-- Left Column (Items to Match) -->
            <div class="matching-render__col matching-render__col--left" role="list" aria-label="Item untuk dicocokkan">
                <?php foreach ($leftItems as $i => $left): ?>
                    <?php
                    $letter = $letterLabels[$left['id']];
                    $selectedRightId = $studentMatches[$left['id']] ?? null;
                    $selectedRightNum = $selectedRightId ? $numberLabels[$selectedRightId] : null;
                    $isCorrect = $isReview && $selectedRightId && $rightItems[array_search($selectedRightId, array_column($rightItems, 'id'))]['original_idx'] === $left['correct_right_idx'];
                    $isIncorrect = $isReview && $selectedRightId && !$isCorrect;
                    ?>
                    <div class="matching-render__item matching-render__item--left
                        <?= $selectedRightId ? ' matching-render__item--answered' : '' ?>
                        <?= $isCorrect ? ' matching-render__item--correct' : '' ?>
                        <?= $isIncorrect ? ' matching-render__item--incorrect' : '' ?>"
                        data-left-id="<?= h_attr($left['id']) ?>"
                        role="listitem"
                    >
                        <span class="matching-render__label"><?= $letter ?>.</span>
                        <span class="matching-render__text"><?= h($left['text']) ?></span>

                        <?php if ($isExam): ?>
                            <select
                                name="answers[<?= h_attr($questionId) ?>][matches][<?= h_attr($left['id']) ?>]"
                                class="matching-render__select"
                                aria-label="Cocokkan <?= $letter ?> dengan"
                                data-left-id="<?= h_attr($left['id']) ?>"
                            >
                                <option value="">— Pilih —</option>
                                <?php foreach ($rightItems as $right): ?>
                                    <option
                                        value="<?= h_attr($right['id']) ?>"
                                        <?= $selectedRightId === $right['id'] ? 'selected' : '' ?>
                                    >
                                        <?= $numberLabels[$right['id']] ?>. <?= h($right['text']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif ($isReview || $isPrint): ?>
                            <div class="matching-render__answer-display">
                                <?php if ($selectedRightNum): ?>
                                    <span class="matching-render__matched-number"><?= $selectedRightNum ?></span>
                                    <?php if ($isReview): ?>
                                        <?php if ($isCorrect): ?>
                                            <span class="matching-render__feedback matching-render__feedback--correct" aria-hidden="true">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                            </span>
                                        <?php elseif ($isIncorrect): ?>
                                            <span class="matching-render__feedback matching-render__feedback--incorrect" aria-hidden="true">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-text-tertiary">—</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Right Column (Options) -->
            <div class="matching-render__col matching-render__col--right" role="list" aria-label="Pilihan jawaban">
                <?php foreach ($rightItems as $right): ?>
                    <?php
                    $num = $numberLabels[$right['id']];
                    $isMatched = in_array($right['id'], $studentMatches);
                    $matchedLeftId = array_search($right['id'], $studentMatches);
                    $matchedLetter = $matchedLeftId ? $letterLabels[$matchedLeftId] : null;
                    ?>
                    <div class="matching-render__item matching-render__item--right
                        <?= $isMatched ? ' matching-render__item--matched' : '' ?>"
                        data-right-id="<?= h_attr($right['id']) ?>"
                        role="listitem"
                    >
                        <span class="matching-render__label"><?= $num ?>.</span>
                        <span class="matching-render__text"><?= h($right['text']) ?></span>
                        <?php if ($isReview && $matchedLetter): ?>
                            <span class="matching-render__matched-to" aria-label="Dicocokkan ke <?= $matchedLetter ?>">← <?= $matchedLetter ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($isExam): ?>
            <p class="form-hint mt-3"><em>Pilih nomor dari kolom kanan untuk setiap huruf di kolom kiri.</em></p>
        <?php endif; ?>
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

    <!-- Print Mode: Show answer key -->
    <?php if ($isPrint): ?>
        <div class="question-card__print-answer" style="margin-top: var(--space-4); padding: var(--space-3); background: var(--color-bg-tertiary); border-radius: var(--radius-md); border-left: 3px solid var(--color-success);">
            <strong>Kunci Jawaban:</strong>
            <table class="table table--sm mt-2">
                <thead><tr><th>Kiri</th><th>Kanan</th></tr></thead>
                <tbody>
                    <?php foreach ($leftItems as $i => $left): ?>
                        <?php
                        $letter = $letterLabels[$left['id']];
                        $correctRight = $rightItems[$left['correct_right_idx']] ?? null;
                        $correctNum = $correctRight ? $numberLabels[$correctRight['id']] : '?';
                        ?>
                        <tr>
                            <td><?= $letter ?>. <?= h($left['text']) ?></td>
                            <td><?= $correctNum ?>. <?= $correctRight ? h($correctRight['text']) : '(tidak ditemukan)' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php if ($isExam): ?>
<script>
// Matching Render - Exam mode interactions
(function() {
    const card = document.querySelector('.question-card--matching[data-question-id="<?= h_js($questionId) ?>"]');
    if (!card) return;

    const selects = card.querySelectorAll('.matching-render__select');
    const flagBtn = card.querySelector('.question-flag');

    // Select change -> auto-save trigger
    selects.forEach(select => {
        select.addEventListener('change', () => {
            const matches = {};
            let answeredCount = 0;
            selects.forEach(s => {
                if (s.value) {
                    matches[s.dataset.leftId] = s.value;
                    answeredCount++;
                }
            });
            card.dataset.answered = answeredCount > 0 ? 'true' : 'false';
            window.dispatchEvent(new CustomEvent('exam:answer-changed', {
                detail: { questionId: '<?= h_js($questionId) ?>', value: { matches } }
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