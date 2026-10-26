<?php
/**
 * Essay Question - Edit Mode
 * Usage: @include('partials.questions.essay.edit', ['question' => $question, 'examId' => $examId])
 * Expects: $question (array), $examId (int|string), $errors (array|null), $old (array|null)
 */

$question = $question ?? [];
$examId = $examId ?? ($question['exam_id'] ?? '');
$errors = $errors ?? [];
$old = $old ?? [];

// Merge question data with old input
$questionText = $old['question_text'] ?? $question['question_text'] ?? '';
$points = $old['points'] ?? $question['points'] ?? 15;
$difficulty = $old['difficulty'] ?? $question['difficulty'] ?? 'medium';
$wordLimit = $old['word_limit'] ?? $question['word_limit'] ?? 500;
$charLimit = $old['char_limit'] ?? $question['char_limit'] ?? 3000;
$explanation = $old['explanation'] ?? $question['explanation'] ?? '';
$tags = $old['tags'] ?? $question['tags'] ?? '';

// Rubric: merge old input with question data
$questionRubric = $question['rubric'] ?? [];
$oldRubric = $old['rubric'] ?? [];

$mergedRubric = [];
$maxIndex = max(count($questionRubric), count($oldRubric));
for ($i = 0; $i < $maxIndex; $i++) {
    $qCrit = $questionRubric[$i] ?? [];
    $oCrit = $oldRubric[$i] ?? [];
    $mergedRubric[] = [
        'criterion' => $oCrit['criterion'] ?? $qCrit['criterion'] ?? '',
        'max_points' => $oCrit['max_points'] ?? $qCrit['max_points'] ?? 0,
        'description' => $oCrit['description'] ?? $qCrit['description'] ?? '',
    ];
}

// Ensure at least 1 criterion
if (count($mergedRubric) < 1) {
    $mergedRubric = [['criterion' => '', 'max_points' => 0, 'description' => '']];
}

$questionId = $question['id'] ?? '';
$hasErrors = !empty($errors);
?>

<div class="question-editor question-editor--essay" data-type="essay" data-question-id="<?= h_attr($questionId) ?>">
    <input type="hidden" name="type" value="essay">
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
            placeholder="Tulis pertanyaan essay di sini..."
            required
            aria-describedby="<?= isset($errors['question_text']) ? 'question_text-error' : 'question_text-hint' ?>"
        ><?= h($questionText) ?></textarea>
        <?php if (isset($errors['question_text'])): ?>
            <p id="question_text-error" class="form-error" role="alert"><?= h($errors['question_text']) ?></p>
        <?php else: ?>
            <p id="question_text-hint" class="form-hint">Gunakan format teks biasa. Matematika: gunakan LaTeX <code>$...$</code> atau <code>$$...$$</code></p>
        <?php endif; ?>
    </div>

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

    <!-- Limits Row -->
    <div class="form-row">
        <div class="form-group">
            <label class="form-label" for="word_limit">Batas Kata (opsional)</label>
            <input
                type="number"
                id="word_limit"
                name="word_limit"
                class="form-control <?= isset($errors['word_limit']) ? 'form-control--error' : '' ?>"
                value="<?= h_attr($wordLimit) ?>"
                min="0"
                max="5000"
                step="10"
                placeholder="Kosongkan = tidak dibatasi"
            >
            <p class="form-hint">0 = tidak dibatasi. Disarankan: 200-1000 kata.</p>
            <?php if (isset($errors['word_limit'])): ?>
                <p class="form-error" role="alert"><?= h($errors['word_limit']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="char_limit">Batas Karakter (opsional)</label>
            <input
                type="number"
                id="char_limit"
                name="char_limit"
                class="form-control <?= isset($errors['char_limit']) ? 'form-control--error' : '' ?>"
                value="<?= h_attr($charLimit) ?>"
                min="0"
                max="20000"
                step="100"
                placeholder="Kosongkan = tidak dibatasi"
            >
            <p class="form-hint">0 = tidak dibatasi. Termasuk spasi.</p>
            <?php if (isset($errors['char_limit'])): ?>
                <p class="form-error" role="alert"><?= h($errors['char_limit']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Rubric / Kriteria Penilaian -->
    <fieldset class="form-group">
        <legend class="form-label">Rubrik Penilaian <span class="text-danger">*</span></legend>
        <p class="form-hint">Minimal 1 kriteria. Total poin rubrik harus sama dengan bobot soal.</p>

        <div class="rubric-criteria" data-max-criteria="10">
            <?php foreach ($mergedRubric as $index => $crit): ?>
                <div class="rubric-criterion" data-index="<?= $index ?>">
                    <div class="form-row rubric-criterion__row">
                        <div class="form-group flex-1">
                            <label class="form-label" for="rubric_<?= $index ?>_criterion">Kriteria</label>
                            <input
                                type="text"
                                id="rubric_<?= $index ?>_criterion"
                                name="rubric[<?= $index ?>][criterion]"
                                class="form-control <?= isset($errors["rubric.{$index}.criterion"]) ? 'form-control--error' : '' ?>"
                                value="<?= h_attr($crit['criterion']) ?>"
                                placeholder="contoh: Ketepatan Konsep"
                                required
                                aria-describedby="<?= isset($errors["rubric.{$index}.criterion"]) ? "rubric_{$index}_criterion-error" : "rubric_{$index}_criterion-hint" ?>"
                            >
                            <?php if (isset($errors["rubric.{$index}.criterion"])): ?>
                                <p id="rubric_<?= $index ?>_criterion-error" class="form-error" role="alert"><?= h($errors["rubric.{$index}.criterion"]) ?></p>
                            <?php else: ?>
                                <p id="rubric_<?= $index ?>_criterion-hint" class="form-hint">Nama kriteria penilaian</p>
                            <?php endif; ?>
                        </div>

                        <div class="form-group" style="min-width: 120px;">
                            <label class="form-label" for="rubric_<?= $index ?>_max_points">Poin</label>
                            <input
                                type="number"
                                id="rubric_<?= $index ?>_max_points"
                                name="rubric[<?= $index ?>][max_points]"
                                class="form-control <?= isset($errors["rubric.{$index}.max_points"]) ? 'form-control--error' : '' ?> rubric-max-points"
                                value="<?= h_attr($crit['max_points']) ?>"
                                min="1"
                                max="100"
                                step="1"
                                required
                            >
                            <?php if (isset($errors["rubric.{$index}.max_points"])): ?>
                                <p class="form-error" role="alert"><?= h($errors["rubric.{$index}.max_points"]) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="rubric_<?= $index ?>_description">Deskripsi / Panduan Skor</label>
                        <textarea
                            id="rubric_<?= $index ?>_description"
                            name="rubric[<?= $index ?>][description]"
                            class="form-control <?= isset($errors["rubric.{$index}.description"]) ? 'form-control--error' : '' ?>"
                            rows="2"
                            placeholder="Deskripsi tingkat pencapaian untuk kriteria ini..."
                            aria-describedby="<?= isset($errors["rubric.{$index}.description"]) ? "rubric_{$index}_description-error" : "rubric_{$index}_description-hint" ?>"
                        ><?= h($crit['description']) ?></textarea>
                        <?php if (isset($errors["rubric.{$index}.description"])): ?>
                            <p id="rubric_<?= $index ?>_description-error" class="form-error" role="alert"><?= h($errors["rubric.{$index}.description"]) ?></p>
                        <?php else: ?>
                            <p id="rubric_<?= $index ?>_description-hint" class="form-hint">Opsional: panduan detail untuk pengkoreksi</p>
                        <?php endif; ?>
                    </div>

                    <div class="rubric-criterion__actions">
                        <button
                            type="button"
                            class="btn btn--ghost btn--xs btn-icon rubric-criterion__duplicate"
                            title="Duplikasi"
                            aria-label="Duplikasi kriteria <?= $index + 1 ?>"
                        >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        </button>
                        <button
                            type="button"
                            class="btn btn--ghost btn--xs btn-icon rubric-criterion__remove"
                            title="Hapus"
                            aria-label="Hapus kriteria <?= $index + 1 ?>"
                            <?= count($mergedRubric) <= 1 ? 'disabled' : '' ?>
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
                class="btn btn--outline btn--sm rubric-add-criterion"
                data-max="10"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Kriteria
            </button>

            <!-- Rubric Total Display -->
            <div class="rubric-total mt-3 p-3 bg-bg-tertiary rounded-lg">
                <div class="flex items-center justify-between">
                    <span class="font-medium">Total Poin Rubrik:</span>
                    <span id="rubric-total-display" class="text-xl font-bold text-primary" data-target-points="<?= (int)$points ?>">0</span>
                </div>
                <div class="progress mt-2" style="height: 6px;">
                    <div id="rubric-total-bar" class="progress__bar" style="width: 0%;"></div>
                </div>
                <p id="rubric-total-hint" class="form-hint mt-1">Target: <span id="rubric-target"><?= (int)$points ?></span> poin</p>
            </div>

            <?php if (isset($errors['rubric'])): ?>
                <p class="form-error" role="alert"><?= h($errors['rubric']) ?></p>
            <?php endif; ?>
        </div>
    </fieldset>

    <!-- Explanation -->
    <div class="form-group">
        <label class="form-label" for="explanation">Penjelasan / Jawaban Contoh (opsional)</label>
        <textarea
            id="explanation"
            name="explanation"
            class="form-control"
            rows="4"
            placeholder="Jawaban model atau panduan penilaian umum (ditampilkan saat review)..."
        ><?= h($explanation) ?></textarea>
        <p class="form-hint">Ditampilkan kepada siswa setelah ujian selesai (mode review). Bisa berisi jawaban model, poin-poin kunci, dll.</p>
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
            placeholder="contoh: biologi, fotosintesis, kelas-11, essay"
        >
        <p class="form-hint">Memudahkan pencarian di bank soal.</p>
    </div>

    <!-- Media Upload (optional) -->
    <div class="form-group">
        <label class="form-label">Media Pendukung (opsional)</label>
        <div class="file-dropzone" id="essay-media-dropzone">
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
        <input type="hidden" name="media_path" id="essay-media-path" value="<?= h_attr($question['media_path'] ?? '') ?>">
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
        <div class="question-preview__content" id="essay-preview">
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
// Essay Edit - Client-side interactions (same as create + delete)
(function() {
    const container = document.querySelector('.question-editor--essay');
    if (!container) return;

    // Rubric criteria management
    const criteriaContainer = container.querySelector('.rubric-criteria');
    const addCriterionBtn = container.querySelector('.rubric-add-criterion');
    let criterionCounter = criteriaContainer.querySelectorAll('.rubric-criterion').length;
    const pointsInput = container.querySelector('#points');
    const totalDisplay = container.querySelector('#rubric-total-display');
    const totalBar = container.querySelector('#rubric-total-bar');

    function updateRubricTotal() {
        let total = 0;
        criteriaContainer.querySelectorAll('.rubric-max-points').forEach(input => {
            total += parseInt(input.value, 10) || 0;
        });
        const target = parseInt(pointsInput?.value, 10) || 0;
        totalDisplay.textContent = total;
        const pct = target > 0 ? Math.min(100, (total / target) * 100) : 0;
        totalBar.style.width = pct + '%';
        totalBar.style.backgroundColor = total === target ? 'var(--color-success)' : (total > target ? 'var(--color-danger)' : 'var(--color-warning)');
        totalDisplay.style.color = total === target ? 'var(--color-success)' : (total > target ? 'var(--color-danger)' : 'var(--color-primary)');
    }

    // Add criterion
    addCriterionBtn?.addEventListener('click', () => {
        const max = parseInt(addCriterionBtn.dataset.max, 10) || 10;
        if (criterionCounter >= max) return;

        const newIndex = criterionCounter;
        const div = document.createElement('div');
        div.className = 'rubric-criterion';
        div.dataset.index = newIndex;
        div.innerHTML = `
            <div class="form-row rubric-criterion__row">
                <div class="form-group flex-1">
                    <label class="form-label" for="rubric_${newIndex}_criterion">Kriteria</label>
                    <input type="text" id="rubric_${newIndex}_criterion" name="rubric[${newIndex}][criterion]" class="form-control" placeholder="contoh: Ketepatan Konsep" required>
                </div>
                <div class="form-group" style="min-width: 120px;">
                    <label class="form-label" for="rubric_${newIndex}_max_points">Poin</label>
                    <input type="number" id="rubric_${newIndex}_max_points" name="rubric[${newIndex}][max_points]" class="form-control rubric-max-points" value="0" min="1" max="100" step="1" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="rubric_${newIndex}_description">Deskripsi / Panduan Skor</label>
                <textarea id="rubric_${newIndex}_description" name="rubric[${newIndex}][description]" class="form-control" rows="2" placeholder="Deskripsi tingkat pencapaian untuk kriteria ini..."></textarea>
            </div>
            <div class="rubric-criterion__actions">
                <button type="button" class="btn btn--ghost btn--xs btn-icon rubric-criterion__duplicate" title="Duplikasi" aria-label="Duplikasi kriteria ${newIndex + 1}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                </button>
                <button type="button" class="btn btn--ghost btn--xs btn-icon rubric-criterion__remove" title="Hapus" aria-label="Hapus kriteria ${newIndex + 1}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
            </div>
        `;
        criteriaContainer.appendChild(div);
        criterionCounter++;
        updateRemoveButtons();
        updateRubricTotal();
    });

    // Event delegation
    criteriaContainer?.addEventListener('click', (e) => {
        const criterion = e.target.closest('.rubric-criterion');
        if (!criterion) return;

        if (e.target.closest('.rubric-criterion__remove')) {
            if (criterionCounter <= 1) return;
            criterion.remove();
            criterionCounter--;
            reindexCriteria();
            updateRemoveButtons();
            updateRubricTotal();
        }

        if (e.target.closest('.rubric-criterion__duplicate')) {
            const max = parseInt(addCriterionBtn?.dataset.max, 10) || 10;
            if (criterionCounter >= max) return;

            const clone = criterion.cloneNode(true);
            const newIndex = criterionCounter;
            clone.dataset.index = newIndex;
            clone.querySelectorAll('[id]').forEach(el => {
                el.id = el.id.replace(/\d+$/, newIndex);
            });
            clone.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(/\[\d+\]/, `[${newIndex}]`);
            });
            clone.querySelectorAll('[for]').forEach(el => {
                el.setAttribute('for', el.getAttribute('for').replace(/\d+$/, newIndex));
            });
            clone.querySelectorAll('[aria-label]').forEach(el => {
                el.setAttribute('aria-label', el.getAttribute('aria-label').replace(/\d+$/, newIndex + 1));
            });
            clone.querySelectorAll('[title]').forEach(el => {
                if (el.title.includes('kriteria')) {
                    el.title = el.title.replace(/\d+$/, newIndex + 1);
                }
            });
            criterion.after(clone);
            criterionCounter++;
            reindexCriteria();
            updateRemoveButtons();
            updateRubricTotal();
        }
    });

    // Reindex criteria
    function reindexCriteria() {
        const criteria = criteriaContainer.querySelectorAll('.rubric-criterion');
        criteria.forEach((crit, i) => {
            crit.dataset.index = i;
            const inputs = crit.querySelectorAll('input, textarea, label');
            inputs.forEach(el => {
                if (el.id) el.id = el.id.replace(/\d+$/, i);
                if (el.name) el.name = el.name.replace(/\[\d+\]/, `[${i}]`);
                if (el.getAttribute('for')) el.setAttribute('for', el.getAttribute('for').replace(/\d+$/, i));
            });
            crit.querySelector('[aria-label*="Duplikasi"]')?.setAttribute('aria-label', `Duplikasi kriteria ${i + 1}`);
            crit.querySelector('[aria-label*="Hapus"]')?.setAttribute('aria-label', `Hapus kriteria ${i + 1}`);
        });
    }

    function updateRemoveButtons() {
        const removeBtns = criteriaContainer.querySelectorAll('.rubric-criterion__remove');
        const disabled = criterionCounter <= 1;
        removeBtns.forEach(btn => btn.disabled = disabled);
    }

    // Live rubric total update
    criteriaContainer?.addEventListener('input', e => {
        if (e.target.classList.contains('rubric-max-points')) {
            updateRubricTotal();
        }
    });
    pointsInput?.addEventListener('input', updateRubricTotal);

    // Preview logic
    const previewDetails = container.querySelector('.question-preview');
    const previewContent = container.querySelector('#essay-preview');
    const questionText = container.querySelector('#question_text');
    const wordLimit = container.querySelector('#word_limit');
    const charLimit = container.querySelector('#char_limit');

    function updatePreview() {
        const qText = questionText?.value || '(kosong)';
        const wLimit = wordLimit?.value || 0;
        const cLimit = charLimit?.value || 0;
        let html = `<div class="question-card question-card--essay" data-question-type="essay">`;
        html += `<div class="question-card__text">${escapeHtml(qText)}</div>`;
        html += `<div class="essay-render-area">`;
        html += `<textarea class="essay-render__textarea" disabled placeholder="Jawaban siswa akan muncul di sini..."></textarea>`;
        if (wLimit > 0 || cLimit > 0) {
            html += `<div class="essay-render__counters">`;
            if (wLimit > 0) html += `<span class="badge badge--outline">Batas: ${wLimit} kata</span>`;
            if (cLimit > 0) html += `<span class="badge badge--outline">Batas: ${cLimit} karakter</span>`;
            html += `</div>`;
        }
        html += `</div>`;
        // Rubric preview
        const criteria = criteriaContainer.querySelectorAll('.rubric-criterion');
        if (criteria.length > 0) {
            html += `<details class="question-card__rubric-preview"><summary>Rubrik Penilaian</summary><table class="table table--sm"><thead><tr><th>Kriteria</th><th>Poin</th><th>Deskripsi</th></tr></thead><tbody>`;
            criteria.forEach((crit, i) => {
                const criterion = crit.querySelector('[name^="rubric["][name$="[criterion]"]')?.value || '(kosong)';
                const maxPoints = crit.querySelector('[name^="rubric["][name$="[max_points]"]')?.value || 0;
                const desc = crit.querySelector('[name^="rubric["][name$="[description]"]')?.value || '-';
                html += `<tr><td>${escapeHtml(criterion)}</td><td>${maxPoints}</td><td>${escapeHtml(desc)}</td></tr>`;
            });
            html += `</tbody></table></details>`;
        }
        html += `</div>`;
        previewContent.innerHTML = html;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Live preview
    [questionText, wordLimit, charLimit].forEach(el => el?.addEventListener('input', updatePreview));
    criteriaContainer?.addEventListener('input', updatePreview);
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
    updateRubricTotal();
})();
</script>