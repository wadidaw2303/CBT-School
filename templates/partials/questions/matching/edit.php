<?php
/**
 * Matching Question - Edit Mode
 * Usage: @include('partials.questions.matching.edit', ['question' => $question, 'examId' => $examId])
 * Expects: $question (array), $examId (int|string), $errors (array|null), $old (array|null)
 */

$question = $question ?? [];
$examId = $examId ?? ($question['exam_id'] ?? '');
$errors = $errors ?? [];
$old = $old ?? [];

// Merge question data with old input
$questionText = $old['question_text'] ?? $question['question_text'] ?? '';
$points = $old['points'] ?? $question['points'] ?? 10;
$difficulty = $old['difficulty'] ?? $question['difficulty'] ?? 'medium';
$explanation = $old['explanation'] ?? $question['explanation'] ?? '';
$tags = $old['tags'] ?? $question['tags'] ?? '';

// Pairs: merge old input with question data
$questionPairs = $question['pairs'] ?? [];
$oldPairs = $old['pairs'] ?? [];

$mergedPairs = [];
$maxIndex = max(count($questionPairs), count($oldPairs));
for ($i = 0; $i < $maxIndex; $i++) {
    $qPair = $questionPairs[$i] ?? [];
    $oPair = $oldPairs[$i] ?? [];
    $mergedPairs[] = [
        'id' => $oPair['id'] ?? $qPair['id'] ?? 'pair_' . ($i + 1),
        'left' => $oPair['left'] ?? $qPair['left'] ?? '',
        'right' => $oPair['right'] ?? $qPair['right'] ?? '',
    ];
}

// Ensure at least 3 pairs
if (count($mergedPairs) < 3) {
    $mergedPairs = array_pad($mergedPairs, 3, ['id' => '', 'left' => '', 'right' => '']);
}

// Generate unique IDs for pairs if missing
foreach ($mergedPairs as $i => &$pair) {
    if (empty($pair['id'])) {
        $pair['id'] = 'pair_' . ($i + 1);
    }
}
unset($pair);

$questionId = $question['id'] ?? '';
$hasErrors = !empty($errors);
?>

<div class="question-editor question-editor--matching" data-type="matching" data-question-id="<?= h_attr($questionId) ?>">
    <input type="hidden" name="type" value="matching">
    <input type="hidden" name="exam_id" value="<?= h_attr($examId) ?>">
    <input type="hidden" name="question_id" value="<?= h_attr($questionId) ?>">
    <input type="hidden" name="_method" value="PUT">

    <!-- Question Text / Instruction -->
    <div class="form-group">
        <label class="form-label" for="question_text">Instruksi Soal <span class="text-danger">*</span></label>
        <textarea
            id="question_text"
            name="question_text"
            class="form-control <?= isset($errors['question_text']) ? 'form-control--error' : '' ?>"
            rows="3"
            placeholder="contoh: Cocokkan setiap istilah di kolom kiri dengan definisinya di kolom kanan."
            required
            aria-describedby="<?= isset($errors['question_text']) ? 'question_text-error' : 'question_text-hint' ?>"
        ><?= h($questionText) ?></textarea>
        <?php if (isset($errors['question_text'])): ?>
            <p id="question_text-error" class="form-error" role="alert"><?= h($errors['question_text']) ?></p>
        <?php else: ?>
            <p id="question_text-hint" class="form-hint">Tulis instruksi untuk mengocokkan item kiri ke kanan.</p>
        <?php endif; ?>
    </div>

    <!-- Matching Pairs -->
    <fieldset class="form-group">
        <legend class="form-label">Pasangan Pencocokan <span class="text-danger">*</span></legend>
        <p class="form-hint">Minimal 3 pasang, maksimal 10 pasang. Kolom kiri = pertanyaan/istilah, kolom kanan = jawaban/definisi. Urutan kanan akan diacak otomatis saat ujian.</p>

        <div class="matching-pairs" data-max-pairs="10">
            <div class="matching-pairs__header">
                <div class="matching-pair__col matching-pair__col--left">KIRI (Istilah / Pertanyaan)</div>
                <div class="matching-pair__col matching-pair__col--right">KANAN (Jawaban / Definisi)</div>
                <div class="matching-pair__col matching-pair__col--actions" style="width: 80px;">Aksi</div>
            </div>

            <?php foreach ($mergedPairs as $index => $pair): ?>
                <div class="matching-pair" data-index="<?= $index ?>">
                    <input type="hidden" name="pairs[<?= $index ?>][id]" value="<?= h_attr($pair['id']) ?>">
                    <div class="matching-pair__col matching-pair__col--left">
                        <input
                            type="text"
                            name="pairs[<?= $index ?>][left]"
                            class="form-control matching-pair__input <?= isset($errors["pairs.{$index}.left"]) ? 'form-control--error' : '' ?>"
                            value="<?= h_attr($pair['left']) ?>"
                            placeholder="Istilah / Pertanyaan <?= $index + 1 ?>"
                            required
                            aria-label="Item kiri <?= $index + 1 ?>"
                        >
                        <?php if (isset($errors["pairs.{$index}.left"])): ?>
                            <p class="form-error"><?= h($errors["pairs.{$index}.left"]) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="matching-pair__col matching-pair__col--right">
                        <input
                            type="text"
                            name="pairs[<?= $index ?>][right]"
                            class="form-control matching-pair__input <?= isset($errors["pairs.{$index}.right"]) ? 'form-control--error' : '' ?>"
                            value="<?= h_attr($pair['right']) ?>"
                            placeholder="Jawaban / Definisi <?= $index + 1 ?>"
                            required
                            aria-label="Item kanan <?= $index + 1 ?>"
                        >
                        <?php if (isset($errors["pairs.{$index}.right"])): ?>
                            <p class="form-error"><?= h($errors["pairs.{$index}.right"]) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="matching-pair__col matching-pair__col--actions">
                        <div class="matching-pair__actions">
                            <button
                                type="button"
                                class="btn btn--ghost btn--xs btn-icon matching-pair__duplicate"
                                title="Duplikasi"
                                aria-label="Duplikasi pasangan <?= $index + 1 ?>"
                            >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                            <button
                                type="button"
                                class="btn btn--ghost btn--xs btn-icon matching-pair__remove"
                                title="Hapus"
                                aria-label="Hapus pasangan <?= $index + 1 ?>"
                                <?= count($mergedPairs) <= 3 ? 'disabled' : '' ?>
                            >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="form-group mt-4">
            <button
                type="button"
                class="btn btn--outline btn--sm matching-add-pair"
                data-max="10"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Pasangan
            </button>
            <?php if (isset($errors['pairs'])): ?>
                <p class="form-error" role="alert"><?= h($errors['pairs']) ?></p>
            <?php endif; ?>
        </div>
    </fieldset>

    <!-- Settings Row -->
    <div class="form-row">
        <div class="form-group">
            <label class="form-label" for="points">Bobot (Poin Total)</label>
            <input
                type="number"
                id="points"
                name="points"
                class="form-control <?= isset($errors['points']) ? 'form-control--error' : '' ?>"
                value="<?= h_attr($points) ?>"
                min="1"
                max="100"
                step="1"
                required
            >
            <p class="form-hint">Poin dibagi rata ke setiap pasangan yang benar.</p>
            <?php if (isset($errors['points'])): ?>
                <p class="form-error" role="alert"><?= h($errors['points']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="difficulty">Tingkat Kesulitan</label>
            <select
                id="difficulty"
                name="difficulty"
                class="form-control <?= isset($errors['difficulty']) ? 'form-control--error' : '' ?>"
            >
                <option value="easy" <?= $difficulty === 'easy' ? 'selected' : '' ?>>Mudah</option>
                <option value="medium" <?= $difficulty === 'medium' ? 'selected' : '' ?>>Sedang</option>
                <option value="hard" <?= $difficulty === 'hard' ? 'selected' : '' ?>>Sulit</option>
            </select>
            <?php if (isset($errors['difficulty'])): ?>
                <p class="form-error" role="alert"><?= h($errors['difficulty']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Explanation -->
    <div class="form-group">
        <label class="form-label" for="explanation">Penjelasan (opsional)</label>
        <textarea
            id="explanation"
            name="explanation"
            class="form-control"
            rows="3"
            placeholder="Penjelasan kunci jawaban (ditampilkan saat review)..."
        ><?= h($explanation) ?></textarea>
        <p class="form-hint">Ditampilkan kepada siswa setelah ujian selesai (mode review).</p>
    </div>

    <!-- Tags -->
    <div class="form-group">
        <label class="form-label" for="tags">Tag (pisahkan dengan koma)</label>
        <input
            type="text"
            id="tags"
            name="tags"
            class="form-control"
            value="<?= h_attr($tags) ?>"
            placeholder="contoh: kimia, unsur, periodik, matching"
        >
        <p class="form-hint">Memudahkan pencarian di bank soal.</p>
    </div>

    <!-- Media Upload (optional) -->
    <div class="form-group">
        <label class="form-label">Media Pendukung (opsional)</label>
        <div class="file-dropzone" id="matching-media-dropzone">
            <input
                type="file"
                id="media_file"
                name="media_file"
                class="file-dropzone__input"
                accept="image/*,video/*,audio/*,.pdf"
                hidden
            >
            <div class="file-dropzone__content">
                <svg class="file-dropzone__icon" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <p class="file-dropzone__text">Seret file atau <button type="button" class="file-dropzone__browse">klik untuk memilih</button></p>
                <p class="file-dropzone__hint">Gambar, video, audio, atau PDF (maks 10MB)</p>
            </div>
            <div class="file-dropzone__preview" hidden></div>
        </div>
        <input type="hidden" name="media_path" id="matching-media-path" value="<?= h_attr($question['media_path'] ?? '') ?>">
        <?php if (!empty($question['media_path'])): ?>
            <p class="form-hint mt-2">File saat ini: <a href="<?= asset($question['media_path']) ?>" target="_blank"><?= h(basename($question['media_path'])) ?></a></p>
        <?php endif; ?>
        <?php if (isset($errors['media_file'])): ?>
            <p class="form-error" role="alert"><?= h($errors['media_file']) ?></p>
        <?php endif; ?>
    </div>

    <!-- Preview Toggle -->
    <details class="question-preview">
        <summary class="btn btn--ghost btn--sm">Pratinjau Soal</summary>
        <div class="question-preview__content" id="matching-preview">
            <!-- Rendered via JS -->
        </div>
    </details>

    <!-- Delete Action (for edit mode) -->
    <div class="form-group pt-4 border-t">
        <button
            type="button"
            class="btn btn--danger btn--sm question-delete"
            data-question-id="<?= h_attr($questionId) ?>"
            data-route="<?= route('admin.questions.destroy', ['id' => $questionId]) ?>"
        >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            Hapus Soal Ini
        </button>
        <p class="form-hint text-danger">Tindakan ini tidak dapat dibatalkan.</p>
    </div>
</div>

<script>
// Matching Edit - Client-side interactions (same as create + delete)
(function() {
    const container = document.querySelector('.question-editor--matching');
    if (!container) return;

    const pairsContainer = container.querySelector('.matching-pairs');
    const addBtn = container.querySelector('.matching-add-pair');
    let pairCounter = pairsContainer.querySelectorAll('.matching-pair').length;

    // Add pair
    addBtn?.addEventListener('click', () => {
        const max = parseInt(addBtn.dataset.max, 10) || 10;
        if (pairCounter >= max) return;

        const newIndex = pairCounter;
        const newId = 'pair_' + (newIndex + 1);
        const div = document.createElement('div');
        div.className = 'matching-pair';
        div.dataset.index = newIndex;
        div.innerHTML = `
            <input type="hidden" name="pairs[${newIndex}][id]" value="${newId}">
            <div class="matching-pair__col matching-pair__col--left">
                <input type="text" name="pairs[${newIndex}][left]" class="form-control matching-pair__input" placeholder="Istilah / Pertanyaan ${newIndex + 1}" required aria-label="Item kiri ${newIndex + 1}">
            </div>
            <div class="matching-pair__col matching-pair__col--right">
                <input type="text" name="pairs[${newIndex}][right]" class="form-control matching-pair__input" placeholder="Jawaban / Definisi ${newIndex + 1}" required aria-label="Item kanan ${newIndex + 1}">
            </div>
            <div class="matching-pair__col matching-pair__col--actions">
                <div class="matching-pair__actions">
                    <button type="button" class="btn btn--ghost btn--xs btn-icon matching-pair__duplicate" title="Duplikasi" aria-label="Duplikasi pasangan ${newIndex + 1}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    </button>
                    <button type="button" class="btn btn--ghost btn--xs btn-icon matching-pair__remove" title="Hapus" aria-label="Hapus pasangan ${newIndex + 1}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                </div>
            </div>
        `;
        pairsContainer.appendChild(div);
        pairCounter++;
        updateRemoveButtons();
    });

    // Event delegation
    pairsContainer?.addEventListener('click', (e) => {
        const pair = e.target.closest('.matching-pair');
        if (!pair) return;

        if (e.target.closest('.matching-pair__remove')) {
            if (pairCounter <= 3) return;
            pair.remove();
            pairCounter--;
            reindexPairs();
            updateRemoveButtons();
        }

        if (e.target.closest('.matching-pair__duplicate')) {
            const max = parseInt(addBtn?.dataset.max, 10) || 10;
            if (pairCounter >= max) return;

            const clone = pair.cloneNode(true);
            const newIndex = pairCounter;
            const newId = 'pair_' + (newIndex + 1);
            clone.dataset.index = newIndex;
            clone.querySelector('input[name^="pairs["][name$="[id]"]').value = newId;
            clone.querySelector('input[name^="pairs["][name$="[id]"]').name = `pairs[${newIndex}][id]`;
            clone.querySelector('input[name^="pairs["][name$="[left]"]').name = `pairs[${newIndex}][left]`;
            clone.querySelector('input[name^="pairs["][name$="[right]"]').name = `pairs[${newIndex}][right]`;
            clone.querySelector('input[name^="pairs["][name$="[left]"]').value = '';
            clone.querySelector('input[name^="pairs["][name$="[right]"]').value = '';
            pair.after(clone);
            pairCounter++;
            reindexPairs();
            updateRemoveButtons();
        }
    });

    function reindexPairs() {
        const pairs = pairsContainer.querySelectorAll('.matching-pair');
        pairs.forEach((p, i) => {
            p.dataset.index = i;
            p.querySelector('input[name^="pairs["][name$="[id]"]').name = `pairs[${i}][id]`;
            p.querySelector('input[name^="pairs["][name$="[left]"]').name = `pairs[${i}][left]`;
            p.querySelector('input[name^="pairs["][name$="[right]"]').name = `pairs[${i}][right]`;
            p.querySelector('[aria-label*="kiri"]').setAttribute('aria-label', `Item kiri ${i + 1}`);
            p.querySelector('[aria-label*="kanan"]').setAttribute('aria-label', `Item kanan ${i + 1}`);
            p.querySelector('[placeholder*="Istilah"]').placeholder = `Istilah / Pertanyaan ${i + 1}`;
            p.querySelector('[placeholder*="Jawaban"]').placeholder = `Jawaban / Definisi ${i + 1}`;
            p.querySelector('[aria-label*="Duplikasi"]').setAttribute('aria-label', `Duplikasi pasangan ${i + 1}`);
            p.querySelector('[aria-label*="Hapus"]').setAttribute('aria-label', `Hapus pasangan ${i + 1}`);
        });
    }

    function updateRemoveButtons() {
        const removeBtns = pairsContainer.querySelectorAll('.matching-pair__remove');
        const disabled = pairCounter <= 3;
        removeBtns.forEach(btn => btn.disabled = disabled);
    }

    // Preview logic
    const previewDetails = container.querySelector('.question-preview');
    const previewContent = container.querySelector('#matching-preview');
    const questionText = container.querySelector('#question_text');

    function updatePreview() {
        const qText = questionText?.value || '(kosong)';
        const pairs = pairsContainer.querySelectorAll('.matching-pair');
        let html = `<div class="question-card question-card--matching" data-question-type="matching">`;
        html += `<div class="question-card__text">${escapeHtml(qText)}</div>`;
        html += `<div class="matching-render">`;

        const leftItems = [];
        const rightItems = [];
        pairs.forEach((p, i) => {
            const left = p.querySelector('input[name$="[left]"]').value || `(kosong ${i + 1})`;
            const right = p.querySelector('input[name$="[right]"]').value || `(kosong ${i + 1})`;
            leftItems.push({ idx: i, text: left });
            rightItems.push({ idx: i, text: right });
        });

        // Render as two columns (preview shows correct matches)
        html += `<div class="matching-render__cols">`;
        html += `<div class="matching-render__col matching-render__col--left">`;
        leftItems.forEach((item, i) => {
            const letter = String.fromCharCode(65 + i); // A, B, C...
            html += `<div class="matching-render__item" data-idx="${item.idx}"><span class="matching-render__label">${letter}.</span>${escapeHtml(item.text)}</div>`;
        });
        html += `</div>`;

        html += `<div class="matching-render__col matching-render__col--right">`;
        rightItems.forEach((item, i) => {
            const num = i + 1;
            html += `<div class="matching-render__item" data-idx="${item.idx}"><span class="matching-render__label">${num}.</span>${escapeHtml(item.text)}</div>`;
        });
        html += `</div>`;
        html += `</div>`;
        html += `<p class="form-hint mt-2"><em>Saar ujian: kolom kanan akan diacak. Siswa mencocokkan huruf (kiri) ke angka (kanan).</em></p>`;
        html += `</div></div>`;
        previewContent.innerHTML = html;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Live preview
    questionText?.addEventListener('input', updatePreview);
    pairsContainer?.addEventListener('input', updatePreview);
    previewDetails?.addEventListener('toggle', () => { if (previewDetails.open) updatePreview(); });

    // Delete handler
    const deleteBtn = container.querySelector('.question-delete');
    deleteBtn?.addEventListener('click', async () => {
        if (!confirm('Yakin ingin menghapus soal ini? Tindakan ini tidak dapat dibatalkan.')) return;
        const route = deleteBtn.dataset.route;
        try {
            const res = await fetch(route, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            if (res.ok) {
                window.location.href = route.replace('/destroy', '');
            } else {
                alert('Gagal menghapus soal');
            }
        } catch (e) {
            console.error(e);
            alert('Terjadi kesalahan');
        }
    });

    // Initial
    updateRemoveButtons();
    updatePreview();
})();
</script>