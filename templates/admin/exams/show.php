<?php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => url('admin/dashboard')],
    ['label' => 'Ujian', 'url' => url('admin/exams')],
    ['label' => $exam['title'], 'url' => null]
];
$statusClass = match($exam['status']) {
    'draft' => 'badge-secondary',
    'published' => 'badge-primary',
    'ongoing' => 'badge-warning',
    'completed' => 'badge-success',
    'archived' => 'badge-secondary',
    default => 'badge-secondary'
};
$statusLabel = match($exam['status']) {
    'draft' => 'Draft',
    'published' => 'Dipublikasikan',
    'ongoing' => 'Berlangsung',
    'completed' => 'Selesai',
    'archived' => 'Diarsipkan',
    default => $exam['status']
};
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= h($exam['title']) ?></h1>
        <p class="page-subtitle">
            <span class="badge <?= $statusClass ?>" style="margin-right: 0.75rem;"><?= $statusLabel ?></span>
            <?= h($exam['subject_name']) ?> · <?= h($exam['class_name'] ?? 'Semua Kelas') ?> ·
            <?= h($exam['teacher_name']) ?> · <?= h($exam['academic_year_name']) ?> / <?= h($exam['semester_name']) ?>
        </p>
    </div>
    <div class="page-actions">
        <?php if ($exam['status'] === 'draft'): ?>
            <button type="button" class="btn btn-success" id="btnPublish">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Publikasikan
            </button>
        <?php elseif ($exam['status'] === 'published'): ?>
            <button type="button" class="btn btn-warning" id="btnUnpublish">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                Kembalikan ke Draft
            </button>
        <?php endif; ?>
        <a href="<?= url('admin/exams/' . $exam['id'] . '/questions') ?>" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Kelola Soal (<?= $stats['total_questions'] ?>)
        </a>
        <a href="<?= url('admin/exams/' . $exam['id'] . '/participants') ?>" class="btn btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Peserta (<?= $stats['total_participants'] ?>)
        </a>
        <a href="<?= url('admin/exams/' . $exam['id'] . '/edit') ?>" class="btn btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 1.5rem;">
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary);"><?= $stats['total_questions'] ?></div>
            <div class="text-secondary">Total Soal</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 1.5rem;">
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary);"><?= $stats['total_participants'] ?></div>
            <div class="text-secondary">Total Peserta</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 1.5rem;">
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--warning);"><?= $stats['not_started'] ?></div>
            <div class="text-secondary">Belum Mulai</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 1.5rem;">
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary);"><?= $stats['in_progress'] ?></div>
            <div class="text-secondary">Sedang Mengerjakan</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 1.5rem;">
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--info);"><?= $stats['submitted'] ?></div>
            <div class="text-secondary">Dikumpulkan</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 1.5rem;">
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--success);"><?= $stats['graded'] ?></div>
            <div class="text-secondary">Dinilai</div>
        </div>
    </div>
</div>

<div class="grid grid-2" style="gap: 1.5rem;">
    <!-- Exam Info & Questions -->
    <div class="card" style="flex-direction: column;">
        <div class="card-header">
            <h3 class="card-title">Informasi Ujian</h3>
        </div>
        <div class="card-body" style="flex: 1;">
            <dl style="display: grid; grid-template-columns: 160px 1fr; gap: 0.75rem 1.5rem; font-size: 0.875rem;">
                <dt class="text-secondary">UUID</dt>
                <dd class="font-mono text-sm"><?= h($exam['uuid']) ?></dd>

                <dt class="text-secondary">Deskripsi</dt>
                <dd><?= h($exam['description'] ?? '-') ?></dd>

                <dt class="text-secondary">Durasi</dt>
                <dd><?= (int)$exam['duration_minutes'] ?> menit</dd>

                <dt class="text-secondary">Nilai Kelulusan</dt>
                <dd><?= $exam['passing_score'] ?>%</dd>

                <dt class="text-secondary">Waktu Mulai</dt>
                <dd><?= format_datetime($exam['start_at']) ?></dd>

                <dt class="text-secondary">Waktu Selesai</dt>
                <dd><?= format_datetime($exam['end_at']) ?></dd>

                <dt class="text-secondary">Acak Soal</dt>
                <dd><?= $exam['shuffle_questions'] ? 'Ya' : 'Tidak' ?></dd>

                <dt class="text-secondary">Acak Opsi</dt>
                <dd><?= $exam['shuffle_options'] ? 'Ya' : 'Tidak' ?></dd>

                <dt class="text-secondary">Tampilkan Hasil</dt>
                <dd><?= $exam['show_results'] ? 'Ya' : 'Tidak' ?></dd>

                <dt class="text-secondary">Tampilkan Jawaban</dt>
                <dd><?= $exam['show_correct_answers'] ? 'Ya' : 'Tidak' ?></dd>

                <dt class="text-secondary">Fullscreen</dt>
                <dd><?= $exam['browser_lockdown'] ? 'Ya' : 'Tidak' ?></dd>

                <dt class="text-secondary">Maks. Ulang</dt>
                <dd><?= (int)$exam['retry_attempts'] ?></dd>

                <dt class="text-secondary">Batasan IP</dt>
                <dd><?= h($exam['ip_restriction'] ?? '-') ?></dd>

                <dt class="text-secondary">Pengaturan</dt>
                <dd class="font-mono text-sm"><?= h($exam['settings'] ?? '-') ?></dd>

                <dt class="text-secondary">Dibuat</dt>
                <dd><?= format_datetime($exam['created_at']) ?></dd>

                <dt class="text-secondary">Diperbarui</dt>
                <dd><?= format_datetime($exam['updated_at']) ?></dd>
            </dl>
        </div>
    </div>

    <!-- Questions Preview -->
    <div class="card" style="flex-direction: column;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">Soal Ujian (<?= $stats['total_questions'] ?>)</h3>
            <a href="<?= url('admin/exams/' . $exam['id'] . '/questions') ?>" class="btn btn-sm btn-primary">Kelola</a>
        </div>
        <div class="card-body" style="flex: 1; overflow-y: auto; max-height: 500px;">
            <?php if (empty($examQuestions)): ?>
                <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 1rem; opacity: 0.5;">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                    <p>Belum ada soal ditambahkan</p>
                    <a href="<?= url('admin/exams/' . $exam['id'] . '/questions') ?>" class="btn btn-primary mt-3">Tambah Soal</a>
                </div>
            <?php else: ?>
                <div class="table-container" style="max-height: 400px;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Soal</th>
                                <th>Tipe</th>
                                <th>Kategori</th>
                                <th>Kesulitan</th>
                                <th>Bobot</th>
                                <th style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($examQuestions as $index => $eq): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <div style="max-width: 300px;">
                                        <div style="font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?= h(strip_tags(json_encode($eq['content']['question_text'] ?? $eq['content']['stem'] ?? 'Soal #' . $eq['question_id']))) ?>
                                        </div>
                                        <div class="text-sm text-secondary">ID: <?= $eq['question_id'] ?> · UUID: <?= substr($eq['question_uuid'] ?? '', 0, 8) ?>...</div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-primary"><?= h($eq['type'] ?? '-') ?></span>
                                </td>
                                <td><?= h($eq['category_name'] ?? '-') ?></td>
                                <td>
                                    <?php
                                    $diffClass = match($eq['difficulty'] ?? '') {
                                        'easy' => 'badge-success',
                                        'medium' => 'badge-warning',
                                        'hard' => 'badge-danger',
                                        default => 'badge-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $diffClass ?>"><?= ucfirst($eq['difficulty'] ?? '-') ?></span>
                                </td>
                                <td><?= $eq['points'] ?></td>
                                <td>
                                    <div style="display: flex; gap: 0.25rem;">
                                        <button type="button" class="btn btn-sm btn-secondary btn-edit-question" data-id="<?= $eq['id'] ?? '' ?>" data-question-id="<?= $eq['question_id'] ?>" data-points="<?= $eq['points'] ?>" title="Edit Bobot">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger btn-remove-question" data-exam-id="<?= $exam['id'] ?>" data-question-id="<?= $eq['question_id'] ?>" title="Hapus dari Ujian">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Participants Preview -->
    <div class="card" style="flex-direction: column; grid-column: 1 / -1;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">Peserta Ujian (<?= $stats['total_participants'] ?>)</h3>
            <a href="<?= url('admin/exams/' . $exam['id'] . '/participants') ?>" class="btn btn-sm btn-primary">Kelola</a>
        </div>
        <div class="card-body" style="flex: 1; overflow-y: auto; max-height: 400px;">
            <?php if (empty($participants)): ?>
                <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 1rem; opacity: 0.5;">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <p>Belum ada peserta ditambahkan</p>
                    <a href="<?= url('admin/exams/' . $exam['id'] . '/participants') ?>" class="btn btn-primary mt-3">Tambah Peserta</a>
                </div>
            <?php else: ?>
                <div class="table-container" style="max-height: 350px;">
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
                                <th style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($participants as $index => $p): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
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
                                    <a href="<?= url('admin/attempts/' . $p['id'] ?? 0) ?>" class="btn btn-sm btn-secondary" title="Lihat Percobaan">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const examId = <?= $exam['id'] ?>;

    // Publish
    const btnPublish = document.getElementById('btnPublish');
    if (btnPublish) {
        btnPublish.addEventListener('click', function() {
            if (!confirm('Publikasikan ujian ini? Peserta akan bisa melihat dan mengerjakan ujian.')) return;
            fetch(url('admin/exams/' + examId + '/publish'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken() }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    toast(data.message, 'success');
                    setTimeout(() => location.reload(), 500);
                } else {
                    toast(data.message || 'Gagal mempublikasikan', 'error');
                }
            });
        });
    }

    // Unpublish
    const btnUnpublish = document.getElementById('btnUnpublish');
    if (btnUnpublish) {
        btnUnpublish.addEventListener('click', function() {
            if (!confirm('Kembalikan ujian ke draft? Peserta tidak akan bisa mengakses ujian.')) return;
            fetch(url('admin/exams/' + examId + '/unpublish'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken() }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    toast(data.message, 'success');
                    setTimeout(() => location.reload(), 500);
                } else {
                    toast(data.message || 'Gagal mengembalikan ke draft', 'error');
                }
            });
        });
    }

    // Edit question points
    document.querySelectorAll('.btn-edit-question').forEach(btn => {
        btn.addEventListener('click', function() {
            const questionId = this.dataset.questionId;
            const currentPoints = this.dataset.points;
            const newPoints = prompt('Masukkan bobot soal:', currentPoints);
            if (newPoints === null || newPoints === currentPoints) return;

            fetch(url('admin/exams/' + examId + '/questions/' + questionId), {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken() },
                body: JSON.stringify({ points: parseInt(newPoints) })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    toast('Bobot diperbarui', 'success');
                    setTimeout(() => location.reload(), 500);
                } else {
                    toast(data.message || 'Gagal memperbarui', 'error');
                }
            });
        });
    });

    // Remove question
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
});
</script>