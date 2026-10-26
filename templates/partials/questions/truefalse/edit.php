<?php
/**
 * True/False Question - Edit Mode
 * Usage: @include('partials.questions.truefalse.edit', ['question' => $question, 'examId' => $examId])
 * Expects: $question (array), $examId (int|string), $errors (array|null), $old (array|null)
 */

$question = $question ?? [];
$examId = $examId ?? ($question['exam_id'] ?? '');
$errors = $errors ?? [];
$old = $old ?? [];

// Merge question data with old input
$questionText = $old['question_text'] ?? $question['question_text'] ?? '';
$points = $old['points'] ?? $question['points'] ?? 5;
$difficulty = $old['difficulty'] ?? $question['difficulty'] ?? 'easy';
$correctAnswer = $old['correct_answer'] ?? $question['correct_answer'] ?? 'true';
$explanation = $old['explanation'] ?? $question['explanation'] ?? '';
$tags = $old['tags'] ?? $question['tags'] ?? '';

$questionId = $question['id'] ?? '';
$hasErrors = !empty($errors);
?>

<div class="question-editor question-editor--truefalse" data-type="truefalse" data-question-id="<?= h_attr($questionId) ?>">
    <input type="hidden" name="type" value="truefalse">
    <input type="hidden" name="exam_id" value="<?= h_attr($examId) ?>">
    <input type="hidden" name="question_id" value="<?= h_attr($questionId) ?>">
    <input type="hidden" name="_method" value="PUT">

    <!-- Question Text -->
    <div class="form-group">
        <label class="form-label" for="question_text">Pernyataan <span class="text-danger">*</span></label>
        <textarea
            id="question_text"
            name="question_text"
            class="form-control <?= isset($errors['question_text']) ? 'form-control--error' : '' ?>"
            rows="4"
            placeholder="Tulis pernyataan yang akan dinilai Benar/Salah..."
            required
            aria-describedby="<?= isset($errors['question_text']) ? 'question_text-error' : 'question_text-hint' ?>"
        ><?= h($questionText) ?></textarea>
        <?php if (isset($errors['question_text'])): ?>
            <p id="question_text-error" class="form-error" role="alert"><?= h($errors['question_text']) ?></p>
        <?php else: ?>
            <p id="question_text-hint" class="form-hint">Tulis pernyataan deklaratif yang jelas bisa dinilai Benar atau Salah. Hindari kata "tidak" ganda.</p>
        <?php endif; ?>
    </div>

    <!-- Correct Answer -->
    <fieldset class="form-group">
        <legend class="form-label">Jawaban Benar <span class="text-danger">*</span></legend>
        <div class="tf-answer-options">
            <label class="tf-answer-option <?= $correctAnswer === 'true' ? 'tf-answer-option--selected' : '' ?>">
                <input
                    type="radio"
                    name="correct_answer"
                    value="true"
                    <?= $correctAnswer === 'true' ? 'checked' : '' ?>
                    required
                    class="tf-answer-option__input"
                    id="tf_true"
                >
                <span class="tf-answer-option__visual">
                    <span class="tf-answer-option__icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                    </span>
                    <span class="tf-answer-option__label">BENAR</span>
                </span>
            </label>
            <label class="tf-answer-option <?= $correctAnswer === 'false' ? 'tf-answer-option--selected' : '' ?>">
                <input
                    type="radio"
                    name="correct_answer"
                    value="false"
                    <?= $correctAnswer === 'false' ? 'checked' : '' ?>
                    required
                    class="tf-answer-option__input"
                    id="tf_false"
                >
                <span class="tf-answer-option__visual">
                    <span class="tf-answer-option__icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </span>
                    <span class="tf-answer-option__label">SALAH</span>
                </span>
            </label>
        </div>
        <?php if (isset($errors['correct_answer'])): ?>
            <p class="form-error" role="alert"><?= h($errors['correct_answer']) ?></p>
        <?php endif; ?>
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
            placeholder="Jelaskan mengapa pernyataan ini Benar/Salah (ditampilkan saat review)..."
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
            placeholder="contoh: fisika, newton, hukum-gerak, truefalse"
        >
        <p class="form-hint">Memudahkan pencarian di bank soal.</p>
    </div>

    <!-- Media Upload (optional) -->
    <div class="form-group">
        <label class="form-label">Media Pendukung (opsional)</label>
        <div class="file-dropzone" id="tf-media-dropzone">
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
        <input type="hidden" name="media_path" id="tf-media-path" value="<?= h_attr($question['media_path'] ?? '') ?>">
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
        <div class="question-preview__content" id="tf-preview">
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
// TrueFalse Edit - Client-side interactions (same as create + delete)
(function() {
    const container = document.querySelector('.question-editor--truefalse');
    if (!container) return;

    const radioInputs = container.querySelectorAll('input[name="correct_answer"]');
    const optionLabels = container.querySelectorAll('.tf-answer-option');

    // Visual selection
    radioInputs.forEach(radio => {
        radio.addEventListener('change', () => {
            optionLabels.forEach(lbl => lbl.classList.remove('tf-answer-option--selected'));
            radio.closest('.tf-answer-option')?.classList.add('tf-answer-option--selected');
        });
    });

    // Preview logic
    const previewDetails = container.querySelector('.question-preview');
    const previewContent = container.querySelector('#tf-preview');
    const questionText = container.querySelector('#question_text');
    const correctRadio = () => container.querySelector('input[name="correct_answer"]:checked');

    function updatePreview() {
        const qText = questionText?.value || '(kosong)';
        const correct = correctRadio()?.value || 'true';
        let html = `<div class="question-card question-card--truefalse" data-question-type="truefalse">`;
        html += `<div class="question-card__text">${escapeHtml(qText)}</div>`;
        html += `<div class="question-card__options tf-render-options">`;
        ['true', 'false'].forEach(val => {
            const isCorrect = val === correct;
            const label = val === 'true' ? 'BENAR' : 'SALAH';
            const icon = val === 'true'
                ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>'
                : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
            html += `<label class="tf-render-option${isCorrect ? ' tf-render-option--correct' : ''}">`;
            html += `<input type="radio" name="preview_answer" disabled ${isCorrect ? 'checked' : ''}>`;
            html += `<span class="tf-render-option__visual">${icon}<span>${label}</span></span>`;
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
    [questionText, ...radioInputs].forEach(el => el?.addEventListener('input', updatePreview));
    radioInputs.forEach(r => r.addEventListener('change', updatePreview));
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
    updatePreview();
})();
</script>