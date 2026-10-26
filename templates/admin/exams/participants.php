<?php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => url('admin/dashboard')],
    ['label' => 'Ujian', 'url' => url('admin/exams')],
    ['label' => $exam['title'], 'url' => url('admin/exams/' . $exam['id'])],
    ['label' => 'Peserta', 'url' => null]
];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Peserta Ujian</h1>
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
    <!-- Current Participants -->
    <div class="card" style="flex-direction: column;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">Peserta Terdaftar (<?= $participants['total'] ?? 0 ?>)</h3>
        </div>
        <div class="card-body" style="flex: 1; overflow-y: auto; max-height: 600px;">
            <!-- Filters -->
            <form method="GET" action="<?= url('admin/exams/' . $exam['id'] . '/participants') ?>" style="margin-bottom: 1rem; display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <input type="text" name="search" class="form-input" placeholder="Cari nama, email, NIS..." value="<?= h($filters['search'] ?? '') ?>" style="flex: 1; min-width: 180px;">
                <select name="status" class="form-input form-select" style="min-width: 150px;">
                    <option value="">Semua Status</option>
                    <option value="not_started" <?= ($filters['status'] ?? '') === 'not_started' ? 'selected' : '' ?>>Belum Mulai</option>
                    <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>Sedang Mengerjakan</option>
                    <option value="submitted" <?= ($filters['status'] ?? '') === 'submitted' ? 'selected' : '' ?>>Dikumpulkan</option>
                    <option value="graded" <?= ($filters['status'] ?? '') === 'graded' ? 'selected' : '' ?>>Dinilai</option>
                    <option value="absent" <?= ($filters['status'] ?? '') === 'absent' ? 'selected' : '' ?>>Tidak Hadir</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="<?= url('admin/exams/' . $exam['id'] . '/participants') ?>" class="btn btn-secondary">Reset</a>
            </form>

            <?php if (empty($participants['data'])): ?>
                <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 1rem; opacity: 0.5;">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <p>Belum ada peserta ditambahkan</p>
                    <p class="text-sm">Tambah peserta dari panel kanan atau gunakan "Tambah Massal dari Kelas"</p>
                </div>
            <?php else: ?>
                <div class="table-container" style="max-height: 450px;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>NIS</th>
                                <th>Kelas</th>
                                <th>Status</th>
                                <th>Mulai</th>
                                <th>Selesai</th>
                                <th>Nilai</th>
                                <th style="width: 60px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($participants['data'] as $index => $p): ?>
                            <tr>
                                <td><?= ($participants['current_page'] - 1) * $participants['per_page'] + $index + 1 ?></td>
                                <td><?= h($p['name']) ?></td>
                                <td class="text-sm"><?= h($p['email']) ?></td>
                                <td><?= h($p['student_id'] ?? '-') ?></td>
                                <td><?= h($p['class_name'] ?? '-') ?></td>
                                <td>
                                    <?php
                                    $pStatusClass = match($p['status']) {
                                        'not_started' => 'badge-secondary',
                                        'in_progress' => 'badge-primary',
                                        'submitted' => 'badge-info',
                                        'graded' => 'badge-success',
                                        'absent' => 'badge-warning',
                                        default => 'badge-secondary'
                                    };
                                    $pStatusLabel = match($p['status']) {
                                        'not_started' => 'Belum Mulai',
                                        'in_progress' => 'Sedang Mengerjakan',
                                        'submitted' => 'Dikumpulkan',
                                        'graded' => 'Dinilai',
                                        'absent' => 'Tidak Hadir',
                                        default => $p['status']
                                    };
                                    ?>
                                    <span class="badge <?= $pStatusClass ?>"><?= $pStatusLabel ?></span>
                                </td>
                                <td class="text-sm"><?= $p['started_at'] ? format_datetime($p['started_at']) : '-' ?></td>
                                <td class="text-sm"><?= $p['completed_at'] ? format_datetime($p['completed_at']) : '-' ?></td>
                                <td>
                                    <?php if ($p['score'] !== null): ?>
                                        <span class="<?= $p['score'] >= $exam['passing_score'] ? 'text-success' : 'text-danger' ?>" style="font-weight: 600;">
                                            <?= number_format($p['score'], 2) ?>%
                                        </span>
                                    <?php else: ?>
                                        <span class="text-secondary">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.25rem;">
                                        <a href="<?= url('admin/attempts/' . $p['id'] ?? 0) ?>" class="btn btn-sm btn-secondary" title="Lihat Percobaan">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <?php if ($p['status'] === 'not_started'): ?>
                                            <button type="button" class="btn btn-sm btn-danger btn-remove-participant" data-user-id="<?= $p['user_id'] ?>" title="Hapus Peserta">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($participants['last_page'] > 1): ?>
                <div class="card-footer" style="justify-content: center; margin-top: 1rem;">
                    <nav class="pagination" aria-label="Pagination">
                        <?php if ($participants['current_page'] > 1): ?>
                            <a href="<?= url('admin/exams/' . $exam['id'] . '/participants') ?>?page=<?= $participants['current_page'] - 1 ?>&<?= http_build_query(array_filter($filters)) ?>">&laquo; Sebelumnya</a>
                        <?php else: ?>
                            <span class="disabled">&laquo; Sebelumnya</span>
                        <?php endif; ?>

                        <?php
                        $start = max(1, $participants['current_page'] - 2);
                        $end = min($participants['last_page'], $participants['current_page'] + 2);

                        if ($start > 1) {
                            echo '<a href="' . url('admin/exams/' . $exam['id'] . '/participants') . '?page=1&' . http_build_query(array_filter($filters)) . '">1</a>';
                            if ($start > 2) echo '<span class="disabled">...</span>';
                        }

                        for ($i = $start; $i <= $end; $i++): ?>
                            <?php if ($i == $participants['current_page']): ?>
                                <span class="current"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= url('admin/exams/' . $exam['id'] . '/participants') ?>?page=<?= $i ?>&<?= http_build_query(array_filter($filters)) ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($end < $participants['last_page']): ?>
                            <?php if ($end < $participants['last_page'] - 1) echo '<span class="disabled">...</span>'; ?>
                            <a href="<?= url('admin/exams/' . $exam['id'] . '/participants') ?>?page=<?= $participants['last_page'] ?>&<?= http_build_query(array_filter($filters)) ?>"><?= $participants['last_page'] ?></a>
                        <?php endif; ?>

                        <?php if ($participants['current_page'] < $participants['last_page']): ?>
                            <a href="<?= url('admin/exams/' . $exam['id'] . '/participants') ?>?page=<?= $participants['current_page'] + 1 ?>&<?= http_build_query(array_filter($filters)) ?>">Selanjutnya &raquo;</a>
                        <?php else: ?>
                            <span class="disabled">Selanjutnya &raquo;</span>
                        <?php endif; ?>
                    </nav>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Available Students / Bulk Add -->
    <div class="card" style="flex-direction: column;">
        <div class="card-header">
            <h3 class="card-title">Siswa Tersedia (<?= count($availableStudents) ?>)</h3>
        </div>
        <div class="card-body" style="flex: 1; overflow-y: auto; max-height: 600px;">
            <!-- Bulk Add from Class -->
            <div style="margin-bottom: 1.5rem; padding: 1rem; background: var(--bg-secondary); border-radius: 8px;">
                <h4 style="margin-bottom: 0.75rem;">Tambah Massal dari Kelas</h4>
                <form id="bulkAddForm">
                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: end;">
                        <div style="flex: 1; min-width: 200px;">
                            <label class="form-label" for="bulkClassIds">Pilih Kelas</label>
                            <select name="class_ids[]" id="bulkClassIds" class="form-input form-select" multiple required style="min-height: 100px;">
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['id'] ?>"><?= h($class['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-hint">Tahan Ctrl/Cmd untuk memilih multiple</div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Tambah dari Kelas
                        </button>
                    </div>
                </form>
            </div>

            <!-- Import from CSV/Excel -->
            <div style="margin-bottom: 1.5rem; padding: 1rem; background: var(--bg-secondary); border-radius: 8px;">
                <h4 style="margin-bottom: 0.75rem;">Import dari CSV/Excel</h4>
                <form id="importForm" enctype="multipart/form-data">
                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: end;">
                        <div style="flex: 1; min-width: 200px;">
                            <label class="form-label" for="importFile">Pilih File CSV</label>
                            <input type="file" name="participants_file" id="importFile" class="form-input" accept=".csv" required>
                            <div class="form-hint">File harus berformat CSV dengan kolom email atau student_id (NIS)</div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Import
                        </button>
                    </div>
                </form>
            </div>

            <!-- Individual Students -->
            <div style="margin-bottom: 1rem; display: flex; gap: 0.75rem;">
                <input type="text" id="studentSearch" class="form-input" placeholder="Cari siswa..." style="flex: 1;">
            </div>

            <?php if (empty($availableStudents)): ?>
                <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 1rem; opacity: 0.5;">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <p>Semua siswa sudah terdaftar</p>
                </div>
            <?php else: ?>
                <div id="availableStudentsList">
                    <?php foreach ($availableStudents as $student): ?>
                        <div class="student-item" data-id="<?= $student['id'] ?>" style="border: 1px solid var(--border); border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 0.5rem; background: var(--bg-secondary); display: flex; justify-content: space-between; align-items: center; transition: all 0.15s;">
                            <div>
                                <div style="font-weight: 500;"><?= h($student['name']) ?></div>
                                <div class="text-sm text-secondary"><?= h($student['email']) ?> · NIS: <?= h($student['student_id'] ?? '-') ?> · <?= h($student['class_name'] ?? '-') ?></div>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary btn-add-student" data-id="<?= $student['id'] ?>">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Tambah
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const examId = <?= $exam['id'] ?>;

    // Add individual student
    document.querySelectorAll('.btn-add-student').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.dataset.id;
            addParticipants([userId]);
        });
    });

    // Bulk add from class
    document.getElementById('bulkAddForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const classIds = Array.from(this.querySelector('select').selectedOptions).map(o => o.value);
        if (classIds.length === 0) {
            toast('Pilih minimal satu kelas', 'warning');
            return;
        }
        addBulkFromClasses(classIds);
    });

    // Remove participant
    document.querySelectorAll('.btn-remove-participant').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Hapus peserta ini dari ujian?')) return;
            const userId = this.dataset.userId;
            removeParticipant(userId);
        });
    });

    // Student search
    document.getElementById('studentSearch').addEventListener('input', function() {
        const search = this.value.toLowerCase();
        document.querySelectorAll('.student-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(search) ? 'flex' : 'none';
        });
    });

    function addParticipants(userIds) {
        fetch(url('admin/exams/' + examId + '/participants'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken() },
            body: JSON.stringify({ user_ids: userIds })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                toast(data.message, 'success');
                setTimeout(() => location.reload(), 500);
            } else {
                toast(data.message || 'Gagal menambahkan peserta', 'error');
            }
        });
    }

    function addBulkFromClasses(classIds) {
        fetch(url('admin/exams/' + examId + '/participants/bulk'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken() },
            body: JSON.stringify({ class_ids: classIds })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                toast(data.message, 'success');
                setTimeout(() => location.reload(), 500);
            } else {
                toast(data.message || 'Gagal menambahkan massal', 'error');
            }
        });
    }

    function removeParticipant(userId) {
        fetch(url('admin/exams/' + examId + '/participants/' + userId), {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken() }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                toast('Peserta dihapus', 'success');
                setTimeout(() => location.reload(), 500);
            } else {
                toast(data.message || 'Gagal menghapus', 'error');
            }
        });
    }
    
    function importParticipantsFromFile() {
        const form = document.getElementById('importForm');
        const formData = new FormData(form);
        
        fetch(url('admin/exams/' + examId + '/participants/import'), {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-Token': csrfToken() }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                toast(data.message, 'success');
                setTimeout(() => location.reload(), 500);
            } else {
                toast(data.message || 'Gagal mengimpor peserta', 'error');
            }
        });
    }
    
    document.getElementById('importForm').addEventListener('submit', function(e) {
        e.preventDefault();
        importParticipantsFromFile();
    });
});
</script>