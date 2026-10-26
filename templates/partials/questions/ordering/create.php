<?php
/**
 * Ordering Question - Create Mode
 * Usage: @include('partials.questions.ordering.create', ['examId' => $examId])
 * Expects: $examId (int|string), $errors (array|null), $old (array|null)
 */

$examId = $examId ?? '';
$errors = $errors ?? [];
$old = $old ?? [];

$oldVal = function($key, $default = '') use ($old) {
    return $old[$key] ?? $default;
};

$questionText = $oldVal('question_text', '');
$points = $oldVal('points', 10);
$difficulty = $oldVal('difficulty', 'medium');
$explanation = $oldVal('explanation', '');
$tags = $oldVal('tags', '');

// Items to order
$oldItems = $old['items'] ?? [
    ['text' => '', 'order' => 1],
    ['text' => '', 'order' => 2],
    ['text' => '', 'order' => 3],
    ['text' => '', 'order' => 4],
];
?>

<div class="question-editor question-editor--ordering" data-type="ordering">
    <input type="hidden" name="type" value="ordering">
    <input type="hidden" name="exam_id" value="<?= h_attr($examId) ?>">

    <!-- Question Text / Instruction -->
    <div class="form-group">
        <label class="form-label" for="question_text">Instruksi Soal <span class="text-danger">*</span></label>
        <textarea
            id="question_text"
            name="question_text"
            class="form-control <?= isset($errors['question_text']) ? 'form-control--error' : '' ?>"
            rows="3"
            placeholder="contoh: Urutkan langkah-langkah berikut dari yang pertama ke terakhir:"
            required
            aria-describedby="<?= isset($errors['question_text']) ? 'question_text-error' : 'question_text-hint' ?>"
        ><?= h($questionText) ?></textarea>
        <?php if (isset($errors['question_text'])): ?>
            <p id="question_text-error" class="form-error" role="alert"><?= h($errors['question_text']) ?></p>
        <?php else: ?>
            <p id="question_text-hint" class="form-hint">Tulis instruksi untuk mengurutkan item (misal: kronologis, ukuran, prioritas, dll).</p>
        <?php endif; ?>
    </div>

    <!-- Orderable Items -->
    <fieldset class="form-group">
        <legend class="form-label">Item yang Diurutkan <span class="text-danger">*</span></legend>
        <p class="form-hint">Minimal 3 item, maksimal 12 item. Masukkan item dalam urutan BENAR (jawaban kunci). Saat ujian, urutan akan diacak otomatis.</p>

        <div class="ordering-items" data-max-items="12">
            <div class="ordering-items__header">
                <div class="ordering-item__col ordering-item__col--num" style="width: 60px;">Urutan</div>
                <div class="ordering-item__col ordering-item__col--text">Isi Item</div>
                <div class="ordering-item__col ordering-item__col--actions" style="width: 100px;">Aksi</div>
            </div>

            <?php foreach ($oldItems as $index => $item): ?>
                <div class="ordering-item" data-index="<?= $index ?>">
                    <input type="hidden" name="items[<?= $index ?>][id]" value="<?= h_attr($item['id'] ?? 'item_' . ($index + 1)) ?>">
                    <input type="hidden" name="items[<?= $index ?>][correct_order]" value="<?= $index + 1 ?>">
                    <div class="ordering-item__col ordering-item__col--num">
                        <span class="ordering-item__order-badge" data-order="<?= $index + 1 ?>"><?= $index + 1 ?></span>
                    </div>
                    <div class="ordering-item__col ordering-item__col--text">
                        <input
                            type="text"
                            name="items[<?= $index ?>][text]"
                            class="form-control ordering-item__text <?= isset($errors["items.{$index}.text"]) ? 'form-control--error' : '' ?>"
                            value="<?= h_attr($item['text']) ?>"
                            placeholder="Item <?= $index + 1 ?> (urutan benar)"
                            required
                            aria-label="Item urutan <?= $index + 1 ?>"
                        >
                        <?php if (isset($errors["items.{$index}.text"])): ?>
                            <p class="form-error"><?= h($errors["items.{$index}.text"]) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="ordering-item__col ordering-item__col--actions">
                        <div class="ordering-item__actions">
                            <button
                                type="button"
                                class="btn btn--ghost btn--xs btn-icon ordering-item__move-up"
                                title="Naikkan"
                                aria-label="Naikkan item <?= $index + 1 ?>"
                                <?= $index === 0 ? 'disabled' : '' ?>
                            >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="18 15 12 9 6 15"/></svg>
                            </button>
                            <button
                                type="button"
                                class="btn btn--ghost btn--xs btn-icon ordering-item__move-down"
                                title="Turunkan"
                                aria-label="Turunkan item <?= $index + 1 ?>"
                                <?= $index === count($oldItems) - 1 ? 'disabled' : '' ?>
                            >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <button
                                type="button"
                                class="btn btn--ghost btn--xs btn-icon ordering-item__duplicate"
                                title="Duplikasi"
                                aria-label="Duplikasi item <?= $index + 1 ?>"
                            >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                            <button
                                type="button"
                                class="btn btn--ghost btn--xs btn-icon ordering-item__remove"
                                title="Hapus"
                                aria-label="Hapus item <?= $index + 1 ?>"
                                <?= count($oldItems) <= 3 ? 'disabled' : '' ?>
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
                class="btn btn--outline btn--sm ordering-add-item"
                data-max="12"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Item
            </button>
            <?php if (isset($errors['items'])): ?>
                <p class="form-error" role="alert"><?= h($errors['items']) ?></p>
            <?php endif; ?>
        </div>
    </fieldset>

    <!-- Settings Row -->
    <div class="form-row">
        <div class="form-group">
            <label class="form-label" for="points">Bobot (Poin)</label>
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
            <p class="form-hint">Poin dibagi rata per item yang posisinya benar.</p>
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
            placeholder="Penjelasan urutan yang benar (ditampilkan saat review)..."
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
            placeholder="contoh: sejarah, kronologi, ordering"
        >
        <p class="form-hint">Memudahkan pencarian di bank soal.</p>
    </div>

    <!-- Media Upload (optional) -->
    <div class="form-group">
        <label class="form-label">Media Pendukung (opsional)</label>
        <div class="file-dropzone" id="ordering-media-dropzone">
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
        <input type="hidden" name="media_path" id="ordering-media-path" value="">
        <?php if (isset($errors['media_file'])): ?>
            <p class="form-error" role="alert"><?= h($errors['media_file']) ?></p>
        <?php endif; ?>
    </div>

    <!-- Preview Toggle -->
    <details class="question-preview">
        <summary class="btn btn--ghost btn--sm">Pratinjau Soal</summary>
        <div class="question-preview__content" id="ordering-preview">
            <!-- Rendered via JS -->
        </div>
    </details>
</div>

<script>
// Ordering Create - Client-side interactions
(function() {
    const container = document.querySelector('.question-editor--ordering');
    if (!container) return;

    const itemsContainer = container.querySelector('.ordering-items');
    const addBtn = container.querySelector('.ordering-add-item');
    let itemCounter = itemsContainer.querySelectorAll('.ordering-item').length;

    // Add item
    addBtn?.addEventListener('click', () => {
        const max = parseInt(addBtn.dataset.max, 10) || 12;
        if (itemCounter >= max) return;

        const newIndex = itemCounter;
        const newId = 'item_' + (newIndex + 1);
        const div = document.createElement('div');
        div.className = 'ordering-item';
        div.dataset.index = newIndex;
        div.innerHTML = `
            <input type="hidden" name="items[${newIndex}][id]" value="${newId}">
            <input type="hidden" name="items[${newIndex}][correct_order]" value="${newIndex + 1}">
            <div class="ordering-item__col ordering-item__col--num">
                <span class="ordering-item__order-badge" data-order="${newIndex + 1}">${newIndex + 1}</span>
            </div>
            <div class="ordering-item__col ordering-item__col--text">
                <input type="text" name="items[${newIndex}][text]" class="form-control ordering-item__text" placeholder="Item ${newIndex + 1} (urutan benar)" required aria-label="Item urutan ${newIndex + 1}">
            </div>
            <div class="ordering-item__col ordering-item__col--actions">
                <div class="ordering-item__actions">
                    <button type="button" class="btn btn--ghost btn--xs btn-icon ordering-item__move-up" title="Naikkan" aria-label="Naikkan item ${newIndex + 1}" ${newIndex === 0 ? 'disabled' : ''}>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                    </button>
                    <button type="button" class="btn btn--ghost btn--xs btn-icon ordering-item__move-down" title="Turunkan" aria-label="Turunkan item ${newIndex + 1}" ${newIndex === 0 ? 'disabled' : ''}>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <button type="button" class="btn btn--ghost btn--xs btn-icon ordering-item__duplicate" title="Duplikasi" aria-label="Duplikasi item ${newIndex + 1}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    </button>
                    <button type="button" class="btn btn--ghost btn--xs btn-icon ordering-item__remove" title="Hapus" aria-label="Hapus item ${newIndex + 1}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                </div>
            </div>
        `;
        itemsContainer.appendChild(div);
        itemCounter++;
        updateMoveButtons();
        updateRemoveButtons();
    });

    // Event delegation
    itemsContainer?.addEventListener('click', (e) => {
        const item = e.target.closest('.ordering-item');
        if (!item) return;

        const index = parseInt(item.dataset.index, 10);
        const items = itemsContainer.querySelectorAll('.ordering-item');

        if (e.target.closest('.ordering-item__move-up')) {
            if (index === 0) return;
            const prev = items[index - 1];
            itemsContainer.insertBefore(item, prev);
            reindexItems();
        }

        if (e.target.closest('.ordering-item__move-down')) {
            if (index === items.length - 1) return;
            const next = items[index + 1];
            itemsContainer.insertBefore(next, item);
            reindexItems();
        }

        if (e.target.closest('.ordering-item__remove')) {
            if (itemCounter <= 3) return;
            item.remove();
            itemCounter--;
            reindexItems();
            updateRemoveButtons();
        }

        if (e.target.closest('.ordering-item__duplicate')) {
            const max = parseInt(addBtn?.dataset.max, 10) || 12;
            if (itemCounter >= max) return;

            const clone = item.cloneNode(true);
            const newIndex = itemCounter;
            const newId = 'item_' + (newIndex + 1);
            clone.dataset.index = newIndex;
            clone.querySelector('input[name$="[id]"]').value = newId;
            clone.querySelector('input[name$="[id]"]').name = `items[${newIndex}][id]`;
            clone.querySelector('input[name$="[correct_order]"]').name = `items[${newIndex}][correct_order]`;
            clone.querySelector('input[name$="[correct_order]"]').value = newIndex + 1;
            clone.querySelector('input[name$="[text]"]').name = `items[${newIndex}][text]`;
            clone.querySelector('input[name$="[text]"]').value = '';
            item.after(clone);
            itemCounter++;
            reindexItems();
            updateRemoveButtons();
        }
    });

    function reindexItems() {
        const items = itemsContainer.querySelectorAll('.ordering-item');
        items.forEach((it, i) => {
            it.dataset.index = i;
            it.querySelector('input[name$="[id]"]').name = `items[${i}][id]`;
            it.querySelector('input[name$="[correct_order]"]').name = `items[${i}][correct_order]`;
            it.querySelector('input[name$="[correct_order]"]').value = i + 1;
            it.querySelector('input[name$="[text]"]').name = `items[${i}][text]`;
            it.querySelector('input[name$="[text]"]').placeholder = `Item ${i + 1} (urutan benar)`;
            it.querySelector('input[name$="[text]"]').setAttribute('aria-label', `Item urutan ${i + 1}`);
            it.querySelector('.ordering-item__order-badge').textContent = i + 1;
            it.querySelector('.ordering-item__order-badge').dataset.order = i + 1;
            it.querySelector('[aria-label*="Naikkan"]').setAttribute('aria-label', `Naikkan item ${i + 1}`);
            it.querySelector('[aria-label*="Turunkan"]').setAttribute('aria-label', `Turunkan item ${i + 1}`);
            it.querySelector('[aria-label*="Duplikasi"]').setAttribute('aria-label', `Duplikasi item ${i + 1}`);
            it.querySelector('[aria-label*="Hapus"]').setAttribute('aria-label', `Hapus item ${i + 1}`);
        });
        updateMoveButtons();
    }

    function updateMoveButtons() {
        const items = itemsContainer.querySelectorAll('.ordering-item');
        items.forEach((it, i) => {
            it.querySelector('.ordering-item__move-up').disabled = i === 0;
            it.querySelector('.ordering-item__move-down').disabled = i === items.length - 1;
        });
    }

    function updateRemoveButtons() {
        const removeBtns = itemsContainer.querySelectorAll('.ordering-item__remove');
        const disabled = itemCounter <= 3;
        removeBtns.forEach(btn => btn.disabled = disabled);
    }

    // Preview logic
    const previewDetails = container.querySelector('.question-preview');
    const previewContent = container.querySelector('#ordering-preview');
    const questionText = container.querySelector('#question_text');

    function updatePreview() {
        const qText = questionText?.value || '(kosong)';
        const items = itemsContainer.querySelectorAll('.ordering-item');
        let html = `<div class="question-card question-card--ordering" data-question-type="ordering">`;
        html += `<div class="question-card__text">${escapeHtml(qText)}</div>`;
        html += `<div class="ordering-render">`;

        // Show correct order (answer key)
        html += `<div class="ordering-render__cols">`;
        html += `<div class="ordering-render__col ordering-render__col--left">`;
        html += `<h5>Urutan Benar (Kunci)</h5>`;
        items.forEach((it, i) => {
            const text = it.querySelector('input[name$="[text]"]').value || `(kosong ${i + 1})`;
            html += `<div class="ordering-render__item ordering-render__item--correct" data-correct-order="${i + 1}"><span class="ordering-render__badge">${i + 1}</span>${escapeHtml(text)}</div>`;
        });
        html += `</div>`;

        // Show shuffled preview
        const shuffled = Array.from(items).map((it, i) => ({
            idx: i,
            text: it.querySelector('input[name$="[text]"]').value || `(kosong ${i + 1})`,
            correctOrder: parseInt(it.querySelector('input[name$="[correct_order]"]').value, 10)
        })).sort(() => Math.random() - 0.5);

        html += `<div class="ordering-render__col ordering-render__col--right">`;
        html += `<h5>Tampilan Ujian (Diacak)</h5>`;
        shuffled.forEach((it, i) => {
            html += `<div class="ordering-render__item ordering-render__item--shuffled" data-correct-order="${it.correctOrder}"><span class="ordering-render__badge">${i + 1}</span>${escapeHtml(it.text)}</div>`;
        });
        html += `</div>`;
        html += `</div>`;
        html += `<p class="form-hint mt-2"><em>Siswa drag-and-drop untuk mengurutkan. Setiap posisi benar mendapat poin.</em></p>`;
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
    itemsContainer?.addEventListener('input', updatePreview);
    previewDetails?.addEventListener('toggle', () => { if (previewDetails.open) updatePreview(); });

    // Initial
    updateMoveButtons();
    updateRemoveButtons();
    updatePreview();
})();
</script>