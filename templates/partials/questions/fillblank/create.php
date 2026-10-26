<?php
/**
 * Fill in the Blank Question - Create Mode
 * Usage: @include('partials.questions.fillblank.create', ['examId' => $examId])
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
$caseSensitive = $oldVal('case_sensitive', false);
$explanation = $oldVal('explanation', '');
$tags = $oldVal('tags', '');

// Blanks: array of blank definitions
$oldBlanks = $old['blanks'] ?? [
    ['answer' => '', 'alternatives' => '', 'points' => 1],
    ['answer' => '', 'alternatives' => '', 'points' => 1],
];
?>

<div class="question-editor question-editor--fillblank" data-type="fillblank">
    <input type="hidden" name="type" value="fillblank">
    <input type="hidden" name="exam_id" value="<?= h_attr($examId) ?>">

    <!-- Question Text with Blank Markers -->
    <div class="form-group">
        <label class="form-label" for="question_text">Soal dengan Blank <span class="text-danger">*</span></label>
        <textarea
            id="question_text"
            name="question_text"
            class="form-control <?= isset($errors['question_text']) ? 'form-control--error' : '' ?>"
            rows="5"
            placeholder="Tulis soal dan gunakan {1}, {2}, {3}... sebagai penanda blank. Contoh: Ibukota Indonesia adalah {1}."
            required
            aria-describedby="<?= isset($errors['question_text']) ? 'question_text-error' : 'question_text-hint' ?>"
        ><?= h($questionText) ?></textarea>
        <?php if (isset($errors['question_text'])): ?>
            <p id="question_text-error" class="form-error" role="alert"><?= h($errors['question_text']) ?></p>
        <?php else: ?>
            <p id="question_text-hint" class="form-hint">Gunakan <code>{1}</code>, <code>{2}</code>, <code>{3}</code>... sebagai penanda blank di dalam teks. Jumlah blank harus sama dengan jumlah jawaban di bawah.</p>
        <?php endif; ?>
    </div>

    <!-- Live Preview of Question Text -->
    <div class="form-group">
        <label class="form-label">Pratinjau Teks Soal</label>
        <div class="fillblank-preview-text" id="fb-preview-text" style="min-height: 60px; padding: var(--space-3); background: var(--color-bg-tertiary); border-radius: var(--radius-md); border: 1px solid var(--color-border); font-family: var(--font-family-mono);">
            <em class="text-text-tertiary">Ketik soal di atas untuk melihat pratinjau...</em>
        </div>
    </div>

    <!-- Blank Answers -->
    <fieldset class="form-group">
        <legend class="form-label">Jawaban Blank <span class="text-danger">*</span></legend>
        <p class="form-hint">Setiap blank (dengan {1}, {2}, ...) harus punya jawaban. Pisahkan alternatif jawaban dengan <code>|</code> (pipe). Contoh: <code>Jakarta|DKI Jakarta</code></p>

        <div class="fillblank-blanks" data-max-blanks="15">
            <?php foreach ($oldBlanks as $index => $blank): ?>
                <div class="fillblank-blank" data-index="<?= $index ?>">
                    <div class="form-row fillblank-blank__row">
                        <div class="form-group" style="width: 60px; flex-shrink: 0;">
                            <label class="form-label" for="blank_<?= $index ?>_num">Blank</label>
                            <input
                                type="text"
                                id="blank_<?= $index ?>_num"
                                class="form-control fillblank-blank__num"
                                value="{<?= $index + 1 ?>}"
                                readonly
                                aria-label="Nomor blank <?= $index + 1 ?>"
                            >
                        </div>

                        <div class="form-group flex-1">
                            <label class="form-label" for="blank_<?= $index ?>_answer">Jawaban Utama</label>
                            <input
                                type="text"
                                id="blank_<?= $index ?>_answer"
                                name="blanks[<?= $index ?>][answer]"
                                class="form-control fillblank-blank__answer <?= isset($errors["blanks.{$index}.answer"]) ? 'form-control--error' : '' ?>"
                                value="<?= h_attr($blank['answer']) ?>"
                                placeholder="Jawaban benar untuk blank {<?= $index + 1 ?>}"
                                required
                                aria-describedby="<?= isset($errors["blanks.{$index}.answer"]) ? "blank_{$index}_answer-error" : "blank_{$index}_answer-hint" ?>"
                            >
                            <?php if (isset($errors["blanks.{$index}.answer"])): ?>
                                <p id="blank_<?= $index ?>_answer-error" class="form-error" role="alert"><?= h($errors["blanks.{$index}.answer"]) ?></p>
                            <?php else: ?>
                                <p id="blank_<?= $index ?>_answer-hint" class="form-hint">Jawaban yang dianggap benar (case-sensitive jika dicentang di bawah)</p>
                            <?php endif; ?>
                        </div>

                        <div class="form-group flex-1">
                            <label class="form-label" for="blank_<?= $index ?>_alternatives">Alternatif (pisahkan dengan |)</label>
                            <input
                                type="text"
                                id="blank_<?= $index ?>_alternatives"
                                name="blanks[<?= $index ?>][alternatives]"
                                class="form-control fillblank-blank__alternatives"
                                value="<?= h_attr($blank['alternatives']) ?>"
                                placeholder="contoh: Jakarta|DKI Jakarta|Ibu Kota"
                                aria-describedby="blank_<?= $index ?>_alternatives-hint"
                            >
                            <p id="blank_<?= $index ?>_alternatives-hint" class="form-hint">Opsional: jawaban alternatif yang juga dianggap benar, pisahkan dengan <code>|</code></p>
                        </div>

                        <div class="form-group" style="width: 80px; flex-shrink: 0;">
                            <label class="form-label" for="blank_<?= $index ?>_points">Poin</label>
                            <input
                                type="number"
                                id="blank_<?= $index ?>_points"
                                name="blanks[<?= $index ?>][points]"
                                class="form-control fillblank-blank__points <?= isset($errors["blanks.{$index}.points"]) ? 'form-control--error' : '' ?>"
                                value="<?= h_attr($blank['points']) ?>"
                                min="1"
                                max="100"
                                step="1"
                                required
                            >
                            <?php if (isset($errors["blanks.{$index}.points"])): ?>
                                <p class="form-error"><?= h($errors["blanks.{$index}.points"]) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="fillblank-blank__actions">
                        <button
                            type="button"
                            class="btn btn--ghost btn--xs btn-icon fillblank-blank__duplicate"
                            title="Duplikasi"
                            aria-label="Duplikasi blank <?= $index + 1 ?>"
                        >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        </button>
                        <button
                            type="button"
                            class="btn btn--ghost btn--xs btn-icon fillblank-blank__remove"
                            title="Hapus"
                            aria-label="Hapus blank <?= $index + 1 ?>"
                            <?= count($oldBlanks) <= 1 ? 'disabled' : '' ?>
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
                class="btn btn--outline btn--sm fillblank-add-blank"
                data-max="15"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Blank
            </button>
            <?php if (isset($errors['blanks'])): ?>
                <p class="form-error" role="alert"><?= h($errors['blanks']) ?></p>
            <?php endif; ?>
        </div>
    </fieldset>

    <!-- Settings -->
    <div class="form-row">
        <div class="form-group">
            <label class="form-label" for="points">Total Bobot (Poin)</label>
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
            <p class="form-hint">Total poin soal. Poin per blank di atas akan diskalakan proporsional.</p>
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

    <!-- Case Sensitivity -->
    <div class="form-group">
        <label class="form-check">
            <input
                type="checkbox"
                id="case_sensitive"
                name="case_sensitive"
                class="form-check__input"
                <?= $caseSensitive ? 'checked' : '' ?>
                value="1"
            >
            <span class="form-check__label">Case-sensitive (perbedaan huruf besar/kecil diperhitungkan)</span>
        </label>
        <p class="form-hint">Jika dicentang, "Jakarta" ≠ "jakarta". Jika tidak, keduanya sama.</p>
    </div>

    <!-- Explanation -->
    <div class="form-group">
        <label class="form-label" for="explanation">Penjelasan (opsional)</label>
        <textarea
            id="explanation"
            name="explanation"
            class="form-control"
            rows="3"
            placeholder="Penjelasan jawaban (ditampilkan saat review)..."
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
            placeholder="contoh: bahasa, tata-bahasa, fillblank"
        >
        <p class="form-hint">Memudahkan pencarian di bank soal.</p>
    </div>

    <!-- Media Upload (optional) -->
    <div class="form-group">
        <label class="form-label">Media Pendukung (opsional)</label>
        <div class="file-dropzone" id="fb-media-dropzone">
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
        <input type="hidden" name="media_path" id="fb-media-path" value="">
        <?php if (isset($errors['media_file'])): ?>
            <p class="form-error" role="alert"><?= h($errors['media_file']) ?></p>
        <?php endif; ?>
    </div>

    <!-- Preview Toggle -->
    <details class="question-preview">
        <summary class="btn btn--ghost btn--sm">Pratinjau Soal Lengkap</summary>
        <div class="question-preview__content" id="fb-preview">
            <!-- Rendered via JS -->
        </div>
    </details>
</div>

<script>
// FillBlank Create - Client-side interactions
(function() {
    const container = document.querySelector('.question-editor--fillblank');
    if (!container) return;

    const blanksContainer = container.querySelector('.fillblank-blanks');
    const addBtn = container.querySelector('.fillblank-add-blank');
    let blankCounter = blanksContainer.querySelectorAll('.fillblank-blank').length;
    const questionText = container.querySelector('#question_text');
    const previewText = container.querySelector('#fb-preview-text');

    // Update preview text as user types
    function updatePreviewText() {
        const text = questionText?.value || '';
        if (!text.trim()) {
            previewText.innerHTML = '<em class="text-text-tertiary">Ketik soal di atas untuk melihat pratinjau...</em>';
            return;
        }
        // Replace {1}, {2}... with styled blanks
        let html = escapeHtml(text);
        html = html.replace(/\{(\d+)\}/g, '<span class="fillblank-preview__blank" data-blank="$1">{$1}</span>');
        previewText.innerHTML = html;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    questionText?.addEventListener('input', updatePreviewText);

    // Add blank
    addBtn?.addEventListener('click', () => {
        const max = parseInt(addBtn.dataset.max, 10) || 15;
        if (blankCounter >= max) return;

        const newIndex = blankCounter;
        const div = document.createElement('div');
        div.className = 'fillblank-blank';
        div.dataset.index = newIndex;
        div.innerHTML = `
            <div class="form-row fillblank-blank__row">
                <div class="form-group" style="width: 60px; flex-shrink: 0;">
                    <label class="form-label" for="blank_${newIndex}_num">Blank</label>
                    <input type="text" id="blank_${newIndex}_num" class="form-control fillblank-blank__num" value="{${newIndex + 1}}" readonly aria-label="Nomor blank ${newIndex + 1}">
                </div>
                <div class="form-group flex-1">
                    <label class="form-label" for="blank_${newIndex}_answer">Jawaban Utama</label>
                    <input type="text" id="blank_${newIndex}_answer" name="blanks[${newIndex}][answer]" class="form-control fillblank-blank__answer" placeholder="Jawaban benar untuk blank {${newIndex + 1}}" required>
                </div>
                <div class="form-group flex-1">
                    <label class="form-label" for="blank_${newIndex}_alternatives">Alternatif (pisahkan dengan |)</label>
                    <input type="text" id="blank_${newIndex}_alternatives" name="blanks[${newIndex}][alternatives]" class="form-control fillblank-blank__alternatives" placeholder="contoh: Jakarta|DKI Jakarta">
                    <p class="form-hint">Opsional: jawaban alternatif yang juga dianggap benar, pisahkan dengan <code>|</code></p>
                </div>
                <div class="form-group" style="width: 80px; flex-shrink: 0;">
                    <label class="form-label" for="blank_${newIndex}_points">Poin</label>
                    <input type="number" id="blank_${newIndex}_points" name="blanks[${newIndex}][points]" class="form-control fillblank-blank__points" value="1" min="1" max="100" step="1" required>
                </div>
            </div>
            <div class="fillblank-blank__actions">
                <button type="button" class="btn btn--ghost btn--xs btn-icon fillblank-blank__duplicate" title="Duplikasi" aria-label="Duplikasi blank ${newIndex + 1}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                </button>
                <button type="button" class="btn btn--ghost btn--xs btn-icon fillblank-blank__remove" title="Hapus" aria-label="Hapus blank ${newIndex + 1}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
            </div>
        `;
        blanksContainer.appendChild(div);
        blankCounter++;
        updateRemoveButtons();
        updateBlankNumbers();
    });

    // Event delegation
    blanksContainer?.addEventListener('click', (e) => {
        const blank = e.target.closest('.fillblank-blank');
        if (!blank) return;

        if (e.target.closest('.fillblank-blank__remove')) {
            if (blankCounter <= 1) return;
            blank.remove();
            blankCounter--;
            reindexBlanks();
            updateRemoveButtons();
            updateBlankNumbers();
        }

        if (e.target.closest('.fillblank-blank__duplicate')) {
            const max = parseInt(addBtn?.dataset.max, 10) || 15;
            if (blankCounter >= max) return;

            const clone = blank.cloneNode(true);
            const newIndex = blankCounter;
            clone.dataset.index = newIndex;
            clone.querySelectorAll('[id]').forEach(el => el.id = el.id.replace(/\d+$/, newIndex));
            clone.querySelectorAll('[name]').forEach(el => el.name = el.name.replace(/\[\d+\]/, `[${newIndex}]`));
            clone.querySelectorAll('[for]').forEach(el => el.setAttribute('for', el.getAttribute('for').replace(/\d+$/, newIndex)));
            clone.querySelectorAll('[aria-label]').forEach(el => el.setAttribute('aria-label', el.getAttribute('aria-label').replace(/\d+$/, newIndex + 1)));
            clone.querySelector('.fillblank-blank__num').value = `{${newIndex + 1}}`;
            clone.querySelector('[placeholder*="blank"]').placeholder = `Jawaban benar untuk blank {${newIndex + 1}}`;
            blank.after(clone);
            blankCounter++;
            reindexBlanks();
            updateRemoveButtons();
            updateBlankNumbers();
        }
    });

    function reindexBlanks() {
        const blanks = blanksContainer.querySelectorAll('.fillblank-blank');
        blanks.forEach((b, i) => {
            b.dataset.index = i;
            b.querySelectorAll('input, label').forEach(el => {
                if (el.id) el.id = el.id.replace(/\d+$/, i);
                if (el.name) el.name = el.name.replace(/\[\d+\]/, `[${i}]`);
                if (el.getAttribute('for')) el.setAttribute('for', el.getAttribute('for').replace(/\d+$/, i));
            });
            b.querySelector('.fillblank-blank__num').value = `{${i + 1}}`;
            b.querySelector('[aria-label*="Nomor blank"]').setAttribute('aria-label', `Nomor blank ${i + 1}`);
            b.querySelector('[placeholder*="blank"]').placeholder = `Jawaban benar untuk blank {${i + 1}}`;
            b.querySelector('[aria-label*="Duplikasi"]').setAttribute('aria-label', `Duplikasi blank ${i + 1}`);
            b.querySelector('[aria-label*="Hapus"]').setAttribute('aria-label', `Hapus blank ${i + 1}`);
        });
    }

    function updateRemoveButtons() {
        const removeBtns = blanksContainer.querySelectorAll('.fillblank-blank__remove');
        const disabled = blankCounter <= 1;
        removeBtns.forEach(btn => btn.disabled = disabled);
    }

    function updateBlankNumbers() {
        // Re-render preview text to show updated blank numbers
        updatePreviewText();
    }

    // Preview logic (full)
    const previewDetails = container.querySelector('.question-preview');
    const previewContent = container.querySelector('#fb-preview');

    function updateFullPreview() {
        const qText = questionText?.value || '(kosong)';
        const caseSensitive = container.querySelector('#case_sensitive')?.checked;
        const blanks = blanksContainer.querySelectorAll('.fillblank-blank');
        let html = `<div class="question-card question-card--fillblank" data-question-type="fillblank">`;
        html += `<div class="question-card__text fillblank-render__text">`;

        // Replace {1}, {2} with input fields
        let displayText = escapeHtml(qText);
        blanks.forEach((b, i) => {
            const blankNum = i + 1;
            const answer = b.querySelector('[name$="[answer]"]').value || '';
            const alternatives = b.querySelector('[name$="[alternatives]"]').value || '';
            const allAnswers = [answer, ...alternatives.split('|').map(s => s.trim()).filter(Boolean)];
            displayText = displayText.replace(new RegExp('\\\\{' + blankNum + '\\\\}', 'g'),
                `<span class="fillblank-render__blank" data-blank="${blankNum}" title="Jawaban: ${escapeHtml(allAnswers.join(', '))}">{${blankNum}}</span>`
            );
        });
        html += displayText;
        html += `</div>`;

        // Show answer key
        if (blanks.length > 0) {
            html += `<details class="question-card__rubric-preview"><summary>Kunci Jawaban</summary><table class="table table--sm"><thead><tr><th>Blank</th><th>Jawaban Utama</th><th>Alternatif</th><th>Poin</th></tr></thead><tbody>`;
            blanks.forEach((b, i) => {
                const answer = b.querySelector('[name$="[answer]"]').value || '(kosong)';
                const alternatives = b.querySelector('[name$="[alternatives]"]').value || '-';
                const points = b.querySelector('[name$="[points]"]').value || 0;
                html += `<tr><td>{${i + 1}}</td><td>${escapeHtml(answer)}</td><td>${escapeHtml(alternatives)}</td><td>${points}</td></tr>`;
            });
            html += `</tbody></table></details>`;
        }
        html += `<p class="form-hint mt-2"><em>Case-sensitive: ${caseSensitive ? 'Ya' : 'Tidak'}</em></p>`;
        html += `</div>`;
        previewContent.innerHTML = html;
    }

    // Live preview
    questionText?.addEventListener('input', updateFullPreview);
    blanksContainer?.addEventListener('input', updateFullPreview);
    container.querySelector('#case_sensitive')?.addEventListener('change', updateFullPreview);
    previewDetails?.addEventListener('toggle', () => { if (previewDetails.open) updateFullPreview(); });

    // Initial
    updateRemoveButtons();
    updatePreviewText();
    updateFullPreview();
})();
</script>