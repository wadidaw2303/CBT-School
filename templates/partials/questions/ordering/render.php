<?php
/**
 * Ordering Question - Render Mode (Student Exam Taking)
 * Usage: @include('partials.questions.ordering.render', ['question' => $question, 'exam' => $exam, 'index' => $index, 'answer' => $answer])
 * Expects:
 *   - $question (array): id, type, question_text, items, points, explanation, media_path
 *   - $exam (array|null): exam config
 *   - $index (int): question number (1-based)
 *   - $answer (array|null): student's saved answer ['order' => ['item_1' => 2, 'item_3' => 1, ...]] or null
 *   - $mode (string): 'exam' | 'review' | 'print'
 */

$question = $question ?? [];
$exam = $exam ?? [];
$index = $index ?? 1;
$answer = $answer ?? null;
$mode = $mode ?? 'exam';

$questionId = $question['id'] ?? 'q_' . $index;
$questionText = $question['question_text'] ?? '';
$items = $question['items'] ?? [];
$points = $question['points'] ?? 0;
$explanation = $question['explanation'] ?? '';
$mediaPath = $question['media_path'] ?? '';

$isReview = $mode === 'review';
$isPrint = $mode === 'print';
$isExam = $mode === 'exam';

// Student's order: item_id => position (1-based)
$studentOrder = $answer['order'] ?? [];
$isAnswered = !empty($studentOrder);

// For review: calculate score
$score = null;
$graded = false;
$correctCount = 0;
$itemResults = [];
if ($isReview && isset($answer['score'])) {
    $score = $answer['score'];
    $graded = true;
    $correctCount = $answer['correct_count'] ?? 0;
    $itemResults = $answer['item_results'] ?? []; // ['item_1' => true/false, ...]
}

// Sort items by correct_order for answer key
$correctItems = $items;
usort($correctItems, fn($a, $b) => ($a['correct_order'] ?? 0) - ($b['correct_order'] ?? 0));

// Shuffle items for exam mode (deterministic based on questionId)
$shuffledItems = $items;
if ($isExam) {
    $seed = crc32($questionId);
    for ($i = count($shuffledItems) - 1; $i > 0; $i--) {
        $seed = ($seed * 1664525 + 1013904223) & 0xffffffff;
        $j = $seed % ($i + 1);
        [$shuffledItems[$i], $shuffledItems[$j]] = [$shuffledItems[$j], $shuffledItems[$i]];
    }
}
?>

<div
    class="question-card question-card--ordering"
    data-question-id="<?= h_attr($questionId) ?>"
    data-question-type="ordering"
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
                <span class="badge badge--<?= $graded ? ($correctCount === count($items) ? 'success' : ($correctCount > 0 ? 'warning' : 'danger')) : ($isAnswered ? 'info' : 'secondary') ?> badge--sm question-card__status">
                    <?= $graded ? "Terkoreksi: {$correctCount}/" . count($items) . " posisi benar" : ($isAnswered ? 'Dijawab' : 'Tidak Dijawab') ?>
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

    <!-- Ordering Interface -->
    <div class="ordering-render">
        <?php if ($isExam): ?>
            <!-- Drag-and-drop interface for exam -->
            <div class="ordering-render__drag-area" role="list" aria-label="Seret item untuk mengurutkan">
                <?php foreach ($shuffledItems as $displayIndex => $item): ?>
                    $itemId = $item['id'] ?? 'item_' . ($displayIndex + 1);
                    $correctPos = $item['correct_order'] ?? ($displayIndex + 1);
                    $studentPos = $studentOrder[$itemId] ?? null;
                    ?>
                    <div
                        class="ordering-render__draggable-item
                            <?= $studentPos ? ' ordering-render__draggable-item--placed' : '' ?>"
                        draggable="true"
                        data-item-id="<?= h_attr($itemId) ?>"
                        data-correct-position="<?= (int)$correctPos ?>"
                        data-current-position="<?= $studentPos ? (int)$studentPos : '' ?>"
                        role="listitem"
                        aria-label="Item <?= $displayIndex + 1 ?>: <?= h($item['text'] ?? '') ?>"
                        tabindex="0"
                    >
                        <span class="ordering-render__drag-handle" aria-label="Seret untuk memindahkan" tabindex="-1">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="9" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                        </span>
                        <span class="ordering-render__position-badge" aria-hidden="true">
                            <?= $studentPos ? $studentPos : ($displayIndex + 1) ?>
                        </span>
                        <span class="ordering-render__item-text"><?= h($item['text'] ?? '') ?></span>
                        <input type="hidden" name="answers[<?= h_attr($questionId) ?>][order][<?= h_attr($itemId) ?>]" value="<?= $studentPos ? (int)$studentPos : '' ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="form-hint mt-3"><em>Seret item ke posisi yang benar. Urutan 1 = pertama, dst.</em></p>

        <?php elseif ($isReview): ?>
            <!-- Review mode: show student's order vs correct order side by side -->
            <div class="ordering-render__cols">
                <div class="ordering-render__col ordering-render__col--left">
                    <h5>Urutan Anda</h5>
                    <?php
                    // Build student's order (1, 2, 3... positions)
                    $studentPositions = [];
                    foreach ($studentOrder as $itemId => $pos) {
                        $studentPositions[$pos] = $itemId;
                    }
                    ksort($studentPositions);
                    foreach ($studentPositions as $pos => $itemId): ?>
                        <?php
                        $item = null;
                        foreach ($items as $it) {
                            if (($it['id'] ?? '') === $itemId) {
                                $item = $it;
                                break;
                            }
                        }
                        $isCorrect = isset($itemResults[$itemId]) && $itemResults[$itemId];
                        ?>
                        <div class="ordering-render__item
                            <?= $isCorrect ? ' ordering-render__item--correct' : ' ordering-render__item--incorrect' ?>"
                            data-position="<?= $pos ?>">
                            <span class="ordering-render__badge"><?= $pos ?></span>
                            <span class="ordering-render__item-text"><?= h($item['text'] ?? '(item tidak ditemukan)') ?></span>
                            <?php if ($graded): ?>
                                <span class="ordering-render__feedback
                                    <?= $isCorrect ? ' ordering-render__feedback--correct' : ' ordering-render__feedback--incorrect' ?>"
                                    aria-hidden="true">
                                    <?= $isCorrect
                                        ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>'
                                        : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($studentPositions)): ?>
                        <p class="text-text-tertiary text-center py-4">Tidak dijawab</p>
                    <?php endif; ?>
                </div>

                <div class="ordering-render__col ordering-render__col--right">
                    <h5>Urutan Benar</h5>
                    <?php foreach ($correctItems as $i => $item): ?>
                        $itemId = $item['id'] ?? 'item_' . ($i + 1);
                        $isCorrect = isset($itemResults[$itemId]) && $itemResults[$itemId];
                        $studentPos = $studentOrder[$itemId] ?? null;
                        ?>
                        <div class="ordering-render__item ordering-render__item--correct
                            <?= $isCorrect ? '' : ' ordering-render__item--incorrect' ?>"
                            data-position="<?= $i + 1 ?>">
                            <span class="ordering-render__badge"><?= $i + 1 ?></span>
                            <span class="ordering-render__item-text"><?= h($item['text'] ?? '') ?></span>
                            <?php if ($graded && $studentPos !== null): ?>
                                <span class="ordering-render__student-pos">Anda: <?= $studentPos ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php elseif ($isPrint): ?>
            <!-- Print mode: show answer key -->
            <div class="ordering-render__cols">
                <div class="ordering-render__col ordering-render__col--left">
                    <h5>Urutan Benar</h5>
                    <?php foreach ($correctItems as $i => $item): ?>
                        <div class="ordering-render__item ordering-render__item--correct" data-position="<?= $i + 1 ?>">
                            <span class="ordering-render__badge"><?= $i + 1 ?></span>
                            <span class="ordering-render__item-text"><?= h($item['text'] ?? '') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="ordering-render__col ordering-render__col--right">
                    <h5>Untuk Diisi Siswa</h5>
                    <?php
                    $printItems = $shuffledItems; // Use same shuffle as exam for consistency
                    foreach ($printItems as $i => $item): ?>
                        <div class="ordering-render__item ordering-render__item--shuffled" data-correct-order="<?= $item['correct_order'] ?? $i + 1 ?>">
                            <span class="ordering-render__badge"><?= $i + 1 ?></span>
                            <span class="ordering-render__item-text"><?= h($item['text'] ?? '') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
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
</div>

<?php if ($isExam): ?>
<script>
// Ordering Render - Exam mode drag-and-drop
(function() {
    const card = document.querySelector('.question-card--ordering[data-question-id="<?= h_js($questionId) ?>"]');
    if (!card) return;

    const dragArea = card.querySelector('.ordering-render__drag-area');
    const flagBtn = card.querySelector('.question-flag');
    let draggedItem = null;

    if (!dragArea) return;

    const items = dragArea.querySelectorAll('.ordering-render__draggable-item');

    // Drag events
    items.forEach(item => {
        item.addEventListener('dragstart', (e) => {
            draggedItem = item;
            item.classList.add('ordering-render__draggable-item--dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', item.dataset.itemId);
        });

        item.addEventListener('dragend', () => {
            item.classList.remove('ordering-render__draggable-item--dragging');
            draggedItem = null;
            updatePositions();
        });

        item.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            const afterElement = getDragAfterElement(dragArea, e.clientY);
            const draggable = document.querySelector('.ordering-render__draggable-item--dragging');
            if (afterElement == null) {
                dragArea.appendChild(draggable);
            } else {
                dragArea.insertBefore(draggable, afterElement);
            }
        });

        // Keyboard support for accessibility
        item.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                e.preventDefault();
                const itemsArray = Array.from(items);
                const currentIndex = itemsArray.indexOf(item);
                if (e.key === 'ArrowUp' && currentIndex > 0) {
                    dragArea.insertBefore(item, itemsArray[currentIndex - 1]);
                } else if (e.key === 'ArrowDown' && currentIndex < itemsArray.length - 1) {
                    dragArea.insertBefore(item, itemsArray[currentIndex + 1].nextSibling);
                }
                updatePositions();
            }
        });
    });

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.ordering-render__draggable-item:not(.ordering-render__draggable-item--dragging)')];
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    function updatePositions() {
        const items = dragArea.querySelectorAll('.ordering-render__draggable-item');
        const order = {};
        items.forEach((item, index) => {
            const position = index + 1;
            item.dataset.currentPosition = position;
            item.querySelector('.ordering-render__position-badge').textContent = position;
            item.querySelector('input[type="hidden"]').value = position;
            order[item.dataset.itemId] = position;
        });
        card.dataset.answered = items.length > 0 ? 'true' : 'false';
        window.dispatchEvent(new CustomEvent('exam:answer-changed', {
            detail: { questionId: '<?= h_js($questionId) ?>', value: { order } }
        }));
    }

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