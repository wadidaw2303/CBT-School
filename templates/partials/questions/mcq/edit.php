<?php
/**
 * MCQ Question - Edit Mode
 * Usage: @include('partials.questions.mcq.edit', ['question' => $question, 'examId' => $examId])
 * Expects: $question (array), $examId (int|string), $errors (array|null), $old (array|null)
 */

$question = $question ?? [];
$examId = $examId ?? ($question['exam_id'] ?? '');
$errors = $errors ?? [];
$old = $old ?? [];

// Merge question data with old input (for validation error repopulation)
$questionText = $old['question_text'] ?? $question['question_text'] ?? '';
$points = $old['points'] ?? $question['points'] ?? 10;
$difficulty = $old['difficulty'] ?? $question['difficulty'] ?? 'medium';
$explanation = $old['explanation'] ?? $question['explanation'] ?? '';
$tags = $old['tags'] ?? $question['tags'] ?? '';

// Options: merge old input with question data
$questionOptions = $question['options'] ?? [];
$oldOptions = $old['options'] ?? [];

$mergedOptions = [];
$maxIndex = max(count($questionOptions), count($oldOptions));
for ($i = 0; $i < $maxIndex; $i++) {
    $qOpt = $questionOptions[$i] ?? [];
    $oOpt = $oldOptions[$i] ?? [];
    $mergedOptions[] = [
        'id' => $oOpt['id'] ?? $qOpt['id'] ?? 'opt_' . ($i + 1),
        'text' => $oOpt['text'] ?? $qOpt['text'] ?? '',
        'is_correct' => $oOpt['is_correct'] ?? $qOpt['is_correct'] ?? false,
    ];
}

// Ensure at least 2 options
if (count($mergedOptions) < 2) {
    $mergedOptions = array_pad($mergedOptions, 2, ['id' => '', 'text' => '', 'is_correct' => false]);
}

// Generate unique IDs for options if missing
foreach ($mergedOptions as $i => &$opt) {
    if (empty($opt['id'])) {
        $opt['id'] = 'opt_' . ($i + 1);
    }
}
unset($opt);

// Pre-selected correct option (from old or question)
$correctOptionId = $old['correct_option'] ?? $question['correct_option'] ?? '';
if (empty($correctOptionId) && !empty($mergedOptions)) {
    // Find first correct option
    foreach ($mergedOptions as $opt) {
        if ($opt['is_correct']) {
            $correctOptionId = $opt['id'];
            break;
        }
    }
}

$questionId = $question['id'] ?? '';
$hasErrors = !empty($errors);
?>

<div class="question-editor question-editor--mcq" data-type="mcq" data-question-id="<?= h_attr($questionId) ?>">
    <input type="hidden" name="type" value="mcq">
    <input type="hidden" name="exam_id" value="<?= h_attr($examId) ?>">
    <input type="hidden" name="question_id" value="<?= h_attr($questionId) ?>">
    <input type="hidden" name="_method" value="PUT">

    <!-- Question Text -->
    <div class="form-group">
        <label class="form-label" for="question_text">Soal <span class="text-danger">*</span></label>
        <textarea
            id="question_text"
            name="question_text"
            class="form-control <?= isset($errors['question_text']) ? 'form-control--error' : '' ?>"
            rows="4"
            placeholder="Tulis pertanyaan di sini..."
            required
            aria-describedby="<?= isset($errors['question_text']) ? 'question_text-error' : 'question_text-hint' ?>"
        ><?= h($questionText) ?></textarea>
        <?php if (isset($errors['question_text'])): ?>
            <p id="question_text-error" class="form-error" role="alert"><?= h($errors['question_text']) ?></p>
        <?php else: ?>
            <p id="question_text-hint" class="form-hint">Gunakan format teks biasa. Matematika: gunakan LaTeX <code>$...$</code> atau <code>$$...$$</code></p>
        <?php endif; ?>
    </div>

    <!-- Options -->
    <fieldset class="form-group">
        <legend class="form-label">Pilihan Jawaban <span class="text-danger">*</span></legend>
        <p class="form-hint">Minimal 2 pilihan, maksimal 8. Tandai satu jawaban benar.</p>

        <div class="mcq-options" data-max-options="8">
            <?php foreach ($mergedOptions as $index => $opt): ?>
                <div class="mcq-option" data-index="<?= $index ?>">
                    <div class="mcq-option__main">
                        <label class="mcq-option__radio">
                            <input
                                type="radio"
                                name="correct_option"
                                value="<?= h_attr($opt['id']) ?>"
                                <?= $opt['id'] === $correctOptionId ? 'checked' : '' ?>
                                required
                                aria-label="Jawaban benar untuk pilihan <?= $index + 1 ?>"
                            >
                            <span class="mcq-option__radio-visual" aria-hidden="true"></span>
                        </label>
                        <input
                            type="hidden"
                            name="options[<?= $index ?>][id]"
                            value="<?= h_attr($opt['id']) ?>"
                        >
                        <textarea
                            name="options[<?= $index ?>][text]"
                            class="form-control mcq-option__text <?= isset($errors["options.{$index}.text"]) ? 'form-control--error' : '' ?>"
                            rows="2"
                            placeholder="Pilihan <?= $index + 1 ?>"
                            required
                            aria-describedby="<?= isset($errors["options.{$index}.text"]) ? "option_{$index}_text-error" : "option_{$index}_text-hint" ?>"
                        ><?= h($opt['text']) ?></textarea>
                        <?php if (isset($errors["options.{$index}.text"])): ?>
                            <p id="option_<?= $index ?>_text-error" class="form-error" role="alert"><?= h($errors["options.{$index}.text"]) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="mcq-option__actions">
                        <button
                            type="button"
                            class="btn btn--ghost btn--xs btn-icon mcq-option__duplicate"
                            title="Duplikasi"
                            aria-label="Duplikasi pilihan <?= $index + 1 ?>"
                        >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        </button>
                        <button
                            type="button"
                            class="btn btn--ghost btn--xs btn-icon mcq-option__remove"
                            title="Hapus"
                            aria-label="Hapus pilihan <?= $index + 1 ?>"
                            <?= count($mergedOptions) <= 2 ? 'disabled' : '' ?>
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
                class="btn btn--outline btn--sm mcq-add-option"
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
            placeholder="Penjelasan jawaban benar (ditampilkan saat review)..."
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
            placeholder="contoh: aljabar, persamaan-linear, kelas-10"
        >
        <p class="form-hint">Memudahkan pencarian di bank soal.</p>
    </div>

    <!-- Media Upload (optional) -->
    <div class="form-group">
        <label class="form-label">Media Pendukung (opsional)</label>
        <div class="file-dropzone" id="mcq-media-dropzone">
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
        <input type="hidden" name="media_path" id="mcq-media-path" value="<?= h_attr($question['media_path'] ?? '') ?>">
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
        <div class="question-preview__content" id="mcq-preview">
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
// MCQ Edit - Client-side interactions (same as create + delete)
(function() {
    const container = document.querySelector('.question-editor--mcq');
    if (!container) return;

    const optionsContainer = container.querySelector('.mcq-options');
    const addBtn = container.querySelector('.mcq-add-option');
    let optionCounter = optionsContainer.querySelectorAll('.mcq-option').length;

    // Add option
    addBtn?.addEventListener('click', () => {
        const max = parseInt(addBtn.dataset.max, 10) || 8;
        if (optionCounter >= max) return;

        const newIndex = optionCounter;
        const newId = 'opt_' + (newIndex + 1);
        const div = document.createElement('div');
        div.className = 'mcq-option';
        div.dataset.index = newIndex;
        div.innerHTML = `
            <div class="mcq-option__main">
                <label class="mcq-option__radio">
                    <input type="radio" name="correct_option" value="${newId}" aria-label="Jawaban benar untuk pilihan ${newIndex + 1}">
                    <span class="mcq-option__radio-visual" aria-hidden="true"></span>
                </label>
                <input type="hidden" name="options[${newIndex}][id]" value="${newId}">
                <textarea name="options[${newIndex}][text]" class="form-control mcq-option__text" rows="2" placeholder="Pilihan ${newIndex + 1}" required></textarea>
            </div>
            <div class="mcq-option__actions">
                <button type="button" class="btn btn--ghost btn--xs btn-icon mcq-option__duplicate" title="Duplikasi" aria-label="Duplikasi pilihan ${newIndex + 1}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                </button>
                <button type="button" class="btn btn--ghost btn--xs btn-icon mcq-option__remove" title="Hapus" aria-label="Hapus pilihan ${newIndex + 1}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
            </div>
        `;
        optionsContainer.appendChild(div);
        optionCounter++;
        updateRemoveButtons();
    });

    // Event delegation for dynamic buttons
    optionsContainer?.addEventListener('click', (e) => {
        const option = e.target.closest('.mcq-option');
        if (!option) return;

        if (e.target.closest('.mcq-option__remove')) {
            if (optionCounter <= 2) return;
            option.remove();
            optionCounter--;
            reindexOptions();
            updateRemoveButtons();
        }

        if (e.target.closest('.mcq-option__duplicate')) {
            const max = parseInt(addBtn?.dataset.max, 10) || 8;
            if (optionCounter >= max) return;

            const clone = option.cloneNode(true);
            const newIndex = optionCounter;
            const newId = 'opt_' + (newIndex + 1);
            clone.dataset.index = newIndex;
            clone.querySelector('input[name="correct_option"]').value = newId;
            clone.querySelector('input[name^="options["]').name = `options[${newIndex}][id]`;
            clone.querySelector('input[name^="options["]').value = newId;
            clone.querySelector('textarea[name^="options["]').name = `options[${newIndex}][text]`;
            clone.querySelector('textarea[name^="options["]').value = '';
            clone.querySelector('input[type="radio"]').checked = false;
            option.after(clone);
            optionCounter++;
            reindexOptions();
            updateRemoveButtons();
        }
    });

    function reindexOptions() {
        const options = optionsContainer.querySelectorAll('.mcq-option');
        options.forEach((opt, i) => {
            opt.dataset.index = i;
            const radio = opt.querySelector('input[type="radio"]');
            const hiddenId = opt.querySelector('input[name^="options["][name$="[id]"]');
            const textarea = opt.querySelector('textarea[name^="options["]');
            const newId = 'opt_' + (i + 1);
            radio.value = newId;
            hiddenId.name = `options[${i}][id]`;
            hiddenId.value = newId;
            textarea.name = `options[${i}][text]`;
            opt.querySelector('[aria-label]')?.setAttribute('aria-label', `Jawaban benar untuk pilihan ${i + 1}`);
            opt.querySelector('[title="Duplikasi"]')?.setAttribute('aria-label', `Duplikasi pilihan ${i + 1}`);
            opt.querySelector('[title="Hapus"]')?.setAttribute('aria-label', `Hapus pilihan ${i + 1}`);
            textarea.placeholder = `Pilihan ${i + 1}`;
        });
    }

    function updateRemoveButtons() {
        const removeBtns = optionsContainer.querySelectorAll('.mcq-option__remove');
        const disabled = optionCounter <= 2;
        removeBtns.forEach(btn => btn.disabled = disabled);
    }

    // Preview logic
    const previewDetails = container.querySelector('.question-preview');
    const previewContent = container.querySelector('#mcq-preview');
    const questionText = container.querySelector('#question_text');
    const optionTextareas = () => container.querySelectorAll('.mcq-option__text');
    const correctRadio = () => container.querySelector('input[name="correct_option"]:checked');

    function updatePreview() {
        const qText = questionText?.value || '(kosong)';
        const correctId = correctRadio()?.value;
        let html = `<div class="question-card question-card--mcq" data-question-type="mcq">`;
        html += `<div class="question-card__text">${escapeHtml(qText)}</div>`;
        html += `<div class="question-card__options">`;
        optionTextareas().forEach((ta, i) => {
            const optId = ta.closest('.mcq-option')?.querySelector('input[name^="options["][name$="[id]"]')?.value || 'opt_' + (i+1);
            const isCorrect = optId === correctId;
            html += `<label class="mcq-render-option${isCorrect ? ' mcq-render-option--correct' : ''}">`;
            html += `<input type="radio" name="preview_answer" disabled ${isCorrect ? 'checked' : ''}>`;
            html += `<span>${escapeHtml(ta.value || '(kosong)')}</span>`;
            if (isCorrect) html += `<span class="badge badge--success badge--xs">Benar</span>`;
            html += `</label>`;
        });
        html += `</div></div>`;
        previewContent.innerHTML = html;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Live preview
    [questionText, ...optionTextareas()].forEach(el => el?.addEventListener('input', updatePreview));
    container.querySelectorAll('input[name="correct_option"]').forEach(r => r.addEventListener('change', updatePreview));
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
                window.location.href = route.replace('/destroy', ''); // redirect to index
            } else {
                alert('Gagal menghapus soal');
            }
        } catch (e) {
            console.error(e);
            alert('Terjadi kesalahan');
        }
    });

    // Initial state
    updateRemoveButtons();
})();
</script>