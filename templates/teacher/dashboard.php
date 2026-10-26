<?php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => null]
];
?>

<div class="grid grid-4" style="margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-icon primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
        </div>
        <div class="stat-card-value"><?= count($myExams) ?></div>
        <div class="stat-card-label">Ujian Saya</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-icon success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
        </div>
        <div class="stat-card-value"><?= count($pendingGrading) ?></div>
        <div class="stat-card-label">Perlu Dinilai</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-icon warning">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
        </div>
        <div class="stat-card-value"><?= $db = \Core\Database::getInstance(); echo $db->table('questions')->where('created_by', '=', auth()->id)->count(); ?></div>
        <div class="stat-card-label">Total Soal</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-icon danger">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
        </div>
        <div class="stat-card-value"><?= $db = \Core\Database::getInstance(); echo $db->table('exam_participants')->leftJoin('exams', 'exam_participants.exam_id', '=', 'exams.id')->where('exams.teacher_id', '=', auth()->id)->where('exam_participants.status', '=', 'graded')->count(); ?></div>
        <div class="stat-card-label">Selesai Dinilai</div>
    </div>
</div>

<div class="grid grid-2">
    <!-- My Exams -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Ujian Terbaru</h3>
            <a href="<?= url('teacher/exams') ?>" class="btn btn-sm btn-secondary">Lihat Semua</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Mulai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myExams as $exam): ?>
                        <tr>
                            <td><?= h($exam['title']) ?></td>
                            <td><?= h($exam['subject_name']) ?></td>
                            <td><?= h($exam['class_name'] ?? 'Semua') ?></td>
                            <td>
                                <?php
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
                                <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                            </td>
                            <td><?= format_datetime($exam['start_at']) ?></td>
                            <td>
                                <div style="display: flex; gap: 0.375rem;">
                                    <?php if ($exam['status'] === 'draft'): ?>
                                        <a href="<?= url('teacher/exams/' . $exam['id'] . '/edit') ?>" class="btn btn-sm btn-secondary">Edit</a>
                                    <?php endif; ?>
                                    <a href="<?= url('teacher/exams/' . $exam['id']) ?>" class="btn btn-sm btn-secondary">Detail</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($myExams)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;color:var(--text-secondary);padding:2rem">Belum ada ujian. <a href="<?= url('teacher/exams/create') ?>">Buat ujian pertama</a></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pending Grading -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Perlu Dinilai (Esai)</h3>
            <a href="<?= url('teacher/grading') ?>" class="btn btn-sm btn-secondary">Lihat Semua</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Ujian</th>
                            <th>Selesai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingGrading as $item): ?>
                        <tr>
                            <td><?= h($item['student_name']) ?></td>
                            <td><?= h($item['exam_title']) ?></td>
                            <td><?= format_datetime($item['finished_at']) ?></td>
                            <td>
                                <a href="<?= url('teacher/grading/' . $item['id']) ?>" class="btn btn-sm btn-primary">Nilai</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($pendingGrading)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center;color:var(--text-secondary);padding:2rem">Tidak ada yang perlu dinilai</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Aksi Cepat</h3>
    </div>
    <div class="card-body">
        <div style="display:flex;gap:1rem;flex-wrap:wrap;">
            <a href="<?= url('teacher/exams/create') ?>" class="btn btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Buat Ujian Baru
            </a>
            <a href="<?= url('teacher/questions') ?>" class="btn btn-secondary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Kelola Bank Soal
            </a>
            <a href="<?= url('teacher/grading') ?>" class="btn btn-warning">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Penilaian Esai
            </a>
            <a href="<?= url('teacher/reports') ?>" class="btn btn-secondary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Lihat Laporan
            </a>
        </div>
    </div>
</div>