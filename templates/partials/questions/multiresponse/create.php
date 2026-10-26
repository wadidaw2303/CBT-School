<?php
/**
 * Multi-Response Question - Create Mode (Multiple Correct Answers)
 * Usage: @include('partials.questions.multiresponse.create', ['examId' => $examId])
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
$minSelections = $oldVal('min_selections', 1);
$maxSelections = $oldVal('max_selections', 0); // 0 = no limit
$explanation = $oldVal('explanation', '');
$tags = $oldVal('tags', '');

// Options
$oldOptions = $old['options'] ?? [
    ['id' => 'opt_1', 'text' => '', 'is_correct' => false],
    ['id' => 'opt_2', 'text' => '', 'is_correct' => false],
    ['id' => 'opt_3', 'text' => '', 'is_correct' => false],
    ['id' => 'opt_4', 'text' => '', 'is_correct' => false],
];
?>

<div class="question-editor question-editor--multiresponse" data-type="multiresponse">
    <input type="hidden" name="type" value="multiresponse">
    <input type="hidden" name="exam_id" value="<?= h_attr($examId) ?>">

    <!-- Question Text -->
    <div class="form-group">
        <label class="form-label" for="question_text">Soal <span class="text-danger">*</span></label>
        <textarea
            id="question_text"
            name="question_text"
            class="form-control <?= isset($errors['question_text']) ? 'form-control--error' : '' ?>"
            rows="4"
            placeholder="Tulis pertanyaan di sini... (Pilih beberapa jawaban yang benar)"
            required
            aria-describedby="<?= isset($errors['question_text']) ? 'question_text-error' : 'question_text-hint' ?>"
        ><?= h($questionText) ?></textarea>
        <?php if (isset($errors['question_text'])): ?>
            <p id="question_text-error" class="form-error" role="alert"><?= h($errors['question_text']) ?></p>
        <?php else: ?>
            <p id="question_text-hint" class="form-hint">Gunakan format teks biasa. Matematika: gunakan LaTeX <code>$...$</code> atau <code>$$...$$</code>. Siswa bisa memilih <strong>lebih dari satu</strong> jawaban.</p>
        <?php endif; ?>
    </div>

    <!-- Selection Limits -->
    <div class="form-row">
        <div class="form-group">
            <label class="form-label" for="min_selections">Minimal Pilihan</label>
            <input
                type="number"
                id="min_selections"
                name="min_selections"
                class="form-control <?= isset($errors['min_selections']) ? 'form-control--error' : '' ?>"
                value="<?= h_attr($minSelections) ?>"
                min="1"
                max="8"
                step="1"
                required
            >
            <p class="form-hint">Minimal jumlah pilihan yang harus dipilih siswa.</p>
            <?php if (isset($errors['min_selections'])): ?>
                <p class="form-error" role="alert"><?= h($errors['min_selections']) ?></p>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label class="form-label" for="max_selections">Maksimal Pilihan (0 = tidak dibatasi)</label>
            <input
                type="number"
                id="max_selections"
                name="max_selections"
                class="form-control <?= isset($errors['max_selections']) ? 'form-control--error' : '' ?>"
                value="<?= h_attr($maxSelections) ?>"
                min="0"
                max="8"
                step="1"
            >
            <p class="form-hint">0 = siswa boleh memilih semua opsi. Harus ≥ minimal pilihan.</p>
            <?php if (isset($errors['max_selections'])): ?>
                <p class="form-error" role="alert"><?= h($errors['max_selections']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Options -->
    <fieldset class="form-group">
        <legend class="form-label">Pilihan Jawaban <span class="text-danger">*</span></legend>
        <p class="form-hint">Minimal 2 pilihan, maksimal 8. Centang <strong>semua</strong> jawaban yang benar (bisa lebih dari satu).</p>

        <div class="mr-options" data-max-options="8">
            <?php foreach ($oldOptions as $index => $opt): ?>
                <div class="mr-option" data-index="<?= $index ?>">
                    <div class="mr-option__main">
                        <label class="mr-option__checkbox">
                            <input
                                type="checkbox"
                                name="options[<?= $index ?>][is_correct]"
                                value="1"
                                <?= $opt['is_correct'] ? 'checked' : '' ?>
                                class="mr-option__input"
                                aria-label="Jawaban benar untuk pilihan <?= $index + 1 ?>"
                            >
                            <span class="mr-option__checkbox-visual" aria-hidden="true"></span>
                        </label>
                        <input
                            type="hidden"
                            name="options[<?= $index ?>][id]"
                            value="<?= h_attr($opt['id']) ?>"
                        >
                        <textarea
                            name="options[<?= $index ?>][text]"
                            class="form-control mr-option__text <?= isset($errors["options.{$index}.text"]) ? 'form-control--error' : '' ?>"
                            rows="2"
                            placeholder="Pilihan <?= $index + 1 ?>"
                            required
                            aria-describedby="<?= isset($errors["options.{$index}.text"]) ? "option_{$index}_text-error" : "option_{$index}_text-hint" ?>"
                        ><?= h($opt['text']) ?></textarea>
                        <?php if (isset($errors["options.{$index}.text"])): ?>
                            <p id="option_<?= $index ?>_text-error" class="form-error" role="alert"><?= h($errors["options.{$index}.text"]) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="mr-option__actions">
                        <button
                            type="button"
                            class="btn btn--ghost btn--xs btn-icon mr-option__duplicate"
                            title="Duplikasi"
                            aria-label="Duplikasi pilihan <?= $index + 1 ?>"
                        >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        </button>
                        <button
                            type="button"
                            class="btn btn--ghost btn--xs btn-icon mr-option__remove"
                            title="Hapus"
                            aria-label="Hapus pilihan <?= $index + 1 ?>"
                            <?= count($oldOptions) <= 2 ? 'disabled' : '' ?>
                        >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="form-group mt-4">
            <button
                type="button"
                class="btn btn--outline btn--sm mr-add-option"
                data-max="8"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Pilihan
            </button>
            <?php if (isset($errors['options'])): ?>
                <p class="form-error" role="alert"><?= h($errors['options']) ?></p>
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
            <p class="form-hint">Poin dibagi rata per jawaban benar yang dipilih (dan salah yang tidak dipilih).</p>
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
            placeholder="Penjelasan jawaban yang benar (ditampilkan saat review)..."
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
            placeholder="contoh: biologi, klasifikasi, multiresponse"
        >
        <p class="form-hint">Memudahkan pencarian di bank soal.</p>
    </div>

    <!-- Media Upload (optional) -->
    <div class="form-group">
        <label class="form-label">Media Pendukung (opsional)</label>
        <div class="file-dropzone" id="mr-media-dropzone">
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
        <input type="hidden" name="media_path" id="mr-media-path" value="">
        <?php if (isset($errors['media_file'])): ?>
            <p class="form-error" role="alert"><?= h($errors['media_file']) ?></p>
        <?php endif; ?>
    </div>

    <!-- Preview Toggle -->
    <details class="question-preview">
        <summary class="btn btn--ghost btn--sm">Pratinjau Soal</summary>
        <div class="question-preview__content" id="mr-preview">
            <!-- Rendered via JS -->
        </div>
    </details>
</div>

<script>
// MultiResponse Create - Client-side interactions
(function() {
    const container = document.querySelector('.question-editor--multiresponse');
    if (!container) return;

    const optionsContainer = container.querySelector('.mr-options');
    const addBtn = container.querySelector('.mr-add-option');
    let optionCounter = optionsContainer.querySelectorAll('.mr-option').length;

    // Add option
    addBtn?.addEventListener('click', () => {
        const max = parseInt(addBtn.dataset.max, 10) || 8;
        if (optionCounter >= max) return;

        const newIndex = optionCounter;
        const newId = 'opt_' + (newIndex + 1);
        const div = document.createElement('div');
        div.className = 'mr-option';
        div.dataset.index = newIndex;
        div.innerHTML = `
            <div class="mr-option__main">
                <label class="mr-option__checkbox">
                    <input type="checkbox" name="options[${newIndex}][is_correct]" value="1" class="mr-option__input" aria-label="Jawaban benar untuk pilihan ${newIndex + 1}">
                    <span class="mr-option__checkbox-visual" aria-hidden="true"></span>
                </label>
                <input type="hidden" name="options[${newIndex}][id]" value="${newId}">
                <textarea name="options[${newIndex}][text]" class="form-control mr-option__text" rows="2" placeholder="Pilihan ${newIndex + 1}" required></textarea>
            </div>
            <div class="mr-option__actions">
                <button type="button" class="btn btn--ghost btn--xs btn-icon mr-option__duplicate" title="Duplikasi" aria-label="Duplikasi pilihan ${newIndex + 1}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                </button>
                <button type="button" class="btn btn--ghost btn--xs btn-icon mr-option__remove" title="Hapus" aria-label="Hapus pilihan ${newIndex + 1}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
            </div>
        `;
        optionsContainer.appendChild(div);
        optionCounter++;
        updateRemoveButtons();
    });

    // Event delegation
    optionsContainer?.addEventListener('click', (e) => {
        const option = e.target.closest('.mr-option');
        if (!option) return;

        if (e.target.closest('.mr-option__remove')) {
            if (optionCounter <= 2) return;
            option.remove();
            optionCounter--;
            reindexOptions();
            updateRemoveButtons();
        }

        if (e.target.closest('.mr-option__duplicate')) {
            const max = parseInt(addBtn?.dataset.max, 10) || 8;
            if (optionCounter >= max) return;

            const clone = option.cloneNode(true);
            const newIndex = optionCounter;
            const newId = 'opt_' + (newIndex + 1);
            clone.dataset.index = newIndex;
            clone.querySelector('input[name^="options["][name$="[id]"]').value = newId;
            clone.querySelector('input[name^="options["][name$="[id]"]').name = `options[${newIndex}][id]`;
            clone.querySelector('input[name^="options["][name$="[is_correct]"]').name = `options[${newIndex}][is_correct]`;
            clone.querySelector('textarea[name^="options["]').name = `options[${newIndex}][text]`;
            clone.querySelector('textarea[name^="options["]').value = '';
            option.after(clone);
            optionCounter++;
            reindexOptions();
            updateRemoveButtons();
        }
    });

    function reindexOptions() {
        const options = optionsContainer.querySelectorAll('.mr-option');
        options.forEach((opt, i) => {
            opt.dataset.index = i;
            opt.querySelector('input[name^="options["][name$="[id]"]').name = `options[${i}][id]`;
            opt.querySelector('input[name^="options["][name$="[is_correct]"]').name = `options[${i}][is_correct]`;
            opt.querySelector('textarea[name^="options["]').name = `options[${i}][text]`;
            opt.querySelector('input[name^="options["][name$="[is_correct]"]').setAttribute('aria-label', `Jawaban benar untuk pilihan ${i + 1}`);
            opt.querySelector('textarea[name^="options["]').placeholder = `Pilihan ${i + 1}`;
            opt.querySelector('[aria-label*="Duplikasi"]').setAttribute('aria-label', `Duplikasi pilihan ${i + 1}`);
            opt.querySelector('[aria-label*="Hapus"]').setAttribute('aria-label', `Hapus pilihan ${i + 1}`);
        });
    }

    function updateRemoveButtons() {
        const removeBtns = optionsContainer.querySelectorAll('.mr-option__remove');
        const disabled = optionCounter <= 2;
        removeBtns.forEach(btn => btn.disabled = disabled);
    }

    // Preview logic
    const previewDetails = container.querySelector('.question-preview');
    const previewContent = container.querySelector('#mr-preview');
    const questionText = container.querySelector('#question_text');
    const minSel = container.querySelector('#min_selections');
    const maxSel = container.querySelector('#max_selections');

    function updatePreview() {
        const qText = questionText?.value || '(kosong)';
        const minS = minSel?.value || 1;
        const maxS = maxSel?.value || 0;
        const options = optionsContainer.querySelectorAll('.mr-option');
        let html = `<div class="question-card question-card--multiresponse" data-question-type="multiresponse">`;
        html += `<div class="question-card__text">${escapeHtml(qText)}</div>`;
        html += `<div class="question-card__options mr-render-options">`;
        options.forEach((opt, i) => {
            const isCorrect = opt.querySelector('input[name$="[is_correct]"]').checked;
            const text = opt.querySelector('textarea[name$="[text]"]').value || '(kosong)';
            html += `<label class="mr-render-option${isCorrect ? ' mr-render-option--correct' : ''}">`;
            html += `<input type="checkbox" name="preview_answer" disabled ${isCorrect ? 'checked' : ''}>`;
            html += `<span class="mr-render-option__text">${escapeHtml(text)}</span>`;
            if (isCorrect) html += `<span class="badge badge--success badge--xs">Benar</span>`;
            html += `</label>`;
        });
        html += `</div>`;
        html += `<p class="form-hint mt-2">Min pilih: ${minS}${maxS > 0 ? ', Max pilih: ' + maxS : ''} (${options.length} opsi)</p>`;
        html += `</div>`;
        previewContent.innerHTML = html;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Live preview
    [questionText, minSel, maxSel].forEach(el => el?.addEventListener('input', updatePreview));
    optionsContainer?.addEventListener('input', updatePreview);
    optionsContainer?.addEventListener('change', e => {
        if (e.target.type === 'checkbox') updatePreview();
    });
    previewDetails?.addEventListener('toggle', () => { if (previewDetails.open) updatePreview(); });

    // Initial
    updateRemoveButtons();
    updatePreview();
})();
</script>