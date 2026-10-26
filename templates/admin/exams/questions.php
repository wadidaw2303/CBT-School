<?php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => url('admin/dashboard')],
    ['label' => 'Ujian', 'url' => url('admin/exams')],
    ['label' => $exam['title'], 'url' => url('admin/exams/' . $exam['id'])],
    ['label' => 'Soal', 'url' => null]
];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Kelola Soal Ujian</h1>
        <p class="page-subtitle"><?= h($exam['title']) ?> · <?= $exam['subject_name'] ?></p>
    </div>
    <div class="page-actions">
        <a href="<?= url('admin/exams/' . $exam['id']) ?>" class="btn btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Kembali
        </a>
    </div>
</div>

<div class="grid" style="grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <!-- Current Exam Questions -->
    <div class="card" style="flex-direction: column;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">Soal di Ujian Ini (<?= count($examQuestions) ?>)</h3>
            <span class="badge badge-primary" id="totalPoints">Total Bobot: <?= array_sum(array_column($examQuestions, 'points')) ?></span>
        </div>
        <div class="card-body" style="flex: 1; overflow-y: auto; max-height: 600px;">
            <?php if (empty($examQuestions)): ?>
                <div id="emptyExamQuestions" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 1rem; opacity: 0.5;">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                    <p>Belum ada soal ditambahkan</p>
                    <p class="text-sm">Pilih soal dari panel kanan lalu klik "Tambah ke Ujian"</p>
                </div>
            <?php else: ?>
                <div id="examQuestionsList">
                    <?php foreach ($examQuestions as $index => $eq): ?>
                        <div class="question-item" data-question-id="<?= $eq['question_id'] ?>" data-sort-order="<?= $eq['sort_order'] ?>" style="border: 1px solid var(--border); border-radius: 8px; padding: 1rem; margin-bottom: 0.75rem; background: var(--bg-secondary); display: flex; gap: 1rem; align-items: flex-start;">
                            <div style="width: 40px; text-align: center; font-weight: 600; color: var(--text-secondary); padding-top: 0.25rem;" class="sort-index"><?= $index + 1 ?></div>
                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?= h(strip_tags(json_encode($eq['content']['question_text'] ?? $eq['content']['stem'] ?? 'Soal #' . $eq['question_id']))) ?>
                                        </div>
                                        <div class="text-sm text-secondary">
                                            ID: <?= $eq['question_id'] ?> · Tipe: <?= h($eq['type'] ?? '-') ?> · Kategori: <?= h($eq['category_name'] ?? '-') ?>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                                        <input type="number" class="form-input form-input-sm points-input" data-question-id="<?= $eq['question_id'] ?>" value="<?= $eq['points'] ?>" min="1" max="100" style="width: 70px;">
                                        <button type="button" class="btn btn-sm btn-danger btn-remove-question" data-question-id="<?= $eq['question_id'] ?>" title="Hapus">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="text-sm text-secondary">
                                    <span class="badge badge-primary"><?= h($eq['type'] ?? '-') ?></span>
                                    <span class="badge badge-secondary"><?= h($eq['category_name'] ?? '-') ?></span>
                                    <span class="badge <?= match($eq['difficulty'] ?? '') { 'easy' => 'badge-success', 'medium' => 'badge-warning', 'hard' => 'badge-danger', default => 'badge-secondary' } ?>"><?= ucfirst($eq['difficulty'] ?? '-') ?></span>
                                </div>
                            </div>
                            <div style="width: 40px; display: flex; flex-direction: column; gap: 0.25rem; padding-top: 0.5rem;">
                                <button type="button" class="btn btn-sm btn-secondary btn-move-up" title="Naik" style="padding: 0.25rem;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary btn-move-down" title="Turun" style="padding: 0.25rem;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Question Bank -->
    <div class="card" style="flex-direction: column;">
        <div class="card-header">
            <h3 class="card-title">Bank Soal Tersedia (<?= count($availableQuestions) ?>)</h3>
        </div>
        <div class="card-body" style="flex: 1; overflow-y: auto; max-height: 600px;">
            <!-- Filters -->
            <div style="margin-bottom: 1rem; display: flex; flex-wrap: wrap; gap: 0.75rem;">
                <input type="text" id="bankSearch" class="form-input" placeholder="Cari soal..." style="flex: 1; min-width: 150px;">
                <select id="bankCategory" class="form-input form-select" style="min-width: 150px;">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= h($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="bankType" class="form-input form-select" style="min-width: 140px;">
                    <option value="">Semua Tipe</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?= $type ?>"><?= ucfirst(str_replace('_', ' ', $type)) ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="bankDifficulty" class="form-input form-select" style="min-width: 120px;">
                    <option value="">Semua Kesulitan</option>
                    <?php foreach ($difficulties as $diff): ?>
                        <option value="<?= $diff ?>"><?= ucfirst($diff) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (empty($availableQuestions)): ?>
                <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 1rem; opacity: 0.5;">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                    <p>Tidak ada soal tersedia untuk mata pelajaran ini</p>
                    <p class="text-sm">Buat soal baru di menu <strong>Bank Soal</strong> atau pilih mata pelajaran lain</p>
                </div>
            <?php else: ?>
                <div id="questionBankList">
                    <?php foreach ($availableQuestions as $q): ?>
                        <div class="bank-question-item" data-id="<?= $q['id'] ?>" data-category="<?= $q['category_id'] ?? '' ?>" data-type="<?= $q['type'] ?>" data-difficulty="<?= $q['difficulty'] ?>" style="border: 1px solid var(--border); border-radius: 8px; padding: 1rem; margin-bottom: 0.75rem; background: var(--bg-secondary); cursor: pointer; transition: all 0.15s;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?= h(strip_tags(json_encode($q['content']['question_text'] ?? $q['content']['stem'] ?? 'Soal #' . $q['id']))) ?>
                                    </div>
                                    <div class="text-sm text-secondary">
                                        ID: <?= $q['id'] ?> · Tipe: <?= h($q['type']) ?> · Kategori: <?= h($q['category_name'] ?? '-') ?>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <input type="number" class="form-input form-input-sm bank-points" value="1" min="1" max="100" style="width: 60px;" title="Bobot soal">
                                    <button type="button" class="btn btn-sm btn-primary btn-add-question" data-id="<?= $q['id'] ?>" title="Tambah ke Ujian">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="text-sm text-secondary" style="margin-top: 0.5rem;">
                                <span class="badge badge-primary"><?= h($q['type']) ?></span>
                                <span class="badge badge-secondary"><?= h($q['category_name'] ?? '-') ?></span>
                                <span class="badge <?= match($q['difficulty']) { 'easy' => 'badge-success', 'medium' => 'badge-warning', 'hard' => 'badge-danger', default => 'badge-secondary' } ?>"><?= ucfirst($q['difficulty']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Bulk Add Selected -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <strong id="selectedCount">0</strong> soal dipilih
        </div>
        <button type="button" class="btn btn-primary" id="btnBulkAdd" disabled>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Semua yang Dipilih ke Ujian
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const examId = <?= $exam['id'] ?>;
    let selectedBankQuestions = new Set();

    // Bank question selection
    document.querySelectorAll('.bank-question-item').forEach(item => {
        item.addEventListener('click', function(e) {
            if (e.target.closest('button') || e.target.closest('input')) return;
            const id = this.dataset.id;
            if (selectedBankQuestions.has(id)) {
                selectedBankQuestions.delete(id);
                this.style.borderColor = 'var(--border)';
                this.style.background = 'var(--bg-secondary)';
            } else {
                selectedBankQuestions.add(id);
                this.style.borderColor = 'var(--primary)';
                this.style.background = 'rgba(99, 102, 241, 0.05)';
            }
            updateBulkAddButton();
        });
    });

    document.querySelectorAll('.bank-points').forEach(input => {
        input.addEventListener('click', e => e.stopPropagation());
    });

    document.querySelectorAll('.btn-add-question').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const questionId = this.dataset.id;
            const points = this.closest('.bank-question-item').querySelector('.bank-points').value;
            addQuestionToExam([questionId], { [questionId]: parseInt(points) });
        });
    });

    function updateBulkAddButton() {
        const count = selectedBankQuestions.size;
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('btnBulkAdd').disabled = count === 0;
    }

    document.getElementById('btnBulkAdd').addEventListener('click', function() {
        const questions = Array.from(selectedBankQuestions);
        const points = {};
        questions.forEach(id => {
            const input = document.querySelector(`.bank-question-item[data-id="${id}"] .bank-points`);
            points[id] = parseInt(input?.value || 1);
        });
        addQuestionToExam(questions, points);
    });

    function addQuestionToExam(questionIds, points) {
        fetch(url('admin/exams/' + examId + '/questions'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken() },
            body: JSON.stringify({ question_ids: questionIds, points: points })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                toast(data.message, 'success');
                selectedBankQuestions.clear();
                setTimeout(() => location.reload(), 500);
            } else {
                toast(data.message || 'Gagal menambahkan soal', 'error');
            }
        });
    }

    // Bank filters
    function filterBankQuestions() {
        const search = document.getElementById('bankSearch').value.toLowerCase();
        const category = document.getElementById('bankCategory').value;
        const type = document.getElementById('bankType').value;
        const difficulty = document.getElementById('bankDifficulty').value;

        document.querySelectorAll('.bank-question-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            const matchSearch = !search || text.includes(search);
            const matchCategory = !category || item.dataset.category == category;
            const matchType = !type || item.dataset.type === type;
            const matchDifficulty = !difficulty || item.dataset.difficulty === difficulty;

            item.style.display = (matchSearch && matchCategory && matchType && matchDifficulty) ? 'block' : 'none';
        });
    }

    ['bankSearch', 'bankCategory', 'bankType', 'bankDifficulty'].forEach(id => {
        document.getElementById(id).addEventListener('input', filterBankQuestions);
    });

    // Points change in exam questions
    document.querySelectorAll('.points-input').forEach(input => {
        input.addEventListener('change', function() {
            const questionId = this.dataset.questionId;
            const points = parseInt(this.value);
            fetch(url('admin/exams/' + examId + '/questions/' + questionId), {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken() },
                body: JSON.stringify({ points: points })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    updateTotalPoints();
                } else {
                    toast(data.message || 'Gagal memperbarui', 'error');
                    this.value = this.defaultValue;
                }
            });
        });
    });

    // Remove question from exam
    document.querySelectorAll('.btn-remove-question').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Hapus soal ini dari ujian?')) return;
            const questionId = this.dataset.questionId;
            fetch(url('admin/exams/' + examId + '/questions/' + questionId), {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken() }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    toast('Soal dihapus dari ujian', 'success');
                    setTimeout(() => location.reload(), 500);
                } else {
                    toast(data.message || 'Gagal menghapus', 'error');
                }
            });
        });
    });

    // Drag and drop reorder
    const list = document.getElementById('examQuestionsList');
    if (list) {
        new Sortable(list, {
            animation: 150,
            handle: '.question-item',
            onEnd: function(evt) {
                const order = Array.from(list.querySelectorAll('.question-item')).map((item, index) => {
                    item.querySelector('.sort-index').textContent = index + 1;
                    return item.dataset.questionId;
                });
                fetch(url('admin/exams/' + examId + '/questions/reorder'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken() },
                    body: JSON.stringify({ order: order })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        toast('Urutan diperbarui', 'success');
                    } else {
                        toast(data.message || 'Gagal mengurutkan', 'error');
                        setTimeout(() => location.reload(), 500);
                    }
                });
            }
        });
    }

    function updateTotalPoints() {
        let total = 0;
        document.querySelectorAll('.points-input').forEach(input => {
            total += parseInt(input.value) || 0;
        });
        document.getElementById('totalPoints').textContent = 'Total Bobot: ' + total;
    }
});

// Sortable library (minimal implementation)
class Sortable {
    constructor(element, options) {
        this.element = element;
        this.options = options;
        this.draggedItem = null;
        this.init();
    }

    init() {
        this.element.querySelectorAll(this.options.handle || '> *').forEach(item => {
            item.draggable = true;
            item.addEventListener('dragstart', this.onDragStart.bind(this));
            item.addEventListener('dragend', this.onDragEnd.bind(this));
            item.addEventListener('dragover', this.onDragOver.bind(this));
            item.addEventListener('drop', this.onDrop.bind(this));
        });
    }

    onDragStart(e) {
        this.draggedItem = e.target.closest(this.options.handle || '> *');
        this.draggedItem.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
    }

    onDragEnd(e) {
        if (this.draggedItem) {
            this.draggedItem.classList.remove('dragging');
            this.draggedItem = null;
        }
    }

    onDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    }

    onDrop(e) {
        e.preventDefault();
        if (!this.draggedItem) return;

        const target = e.target.closest(this.options.handle || '> *');
        if (!target || target === this.draggedItem) return;

        const items = Array.from(this.element.querySelectorAll(this.options.handle || '> *'));
        const draggedIndex = items.indexOf(this.draggedItem);
        const targetIndex = items.indexOf(target);

        if (draggedIndex < targetIndex) {
            this.element.insertBefore(this.draggedItem, target.nextSibling);
        } else {
            this.element.insertBefore(this.draggedItem, target);
        }

        if (this.options.onEnd) {
            this.options.onEnd({ oldIndex: draggedIndex, newIndex: targetIndex });
        }
    }
}
</script>

<style>
.question-item.dragging {
    opacity: 0.5;
    border-color: var(--primary) !important;
}
.bank-question-item:hover {
    border-color: var(--primary) !important;
}
</style>