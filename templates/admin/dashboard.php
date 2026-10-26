<?php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => null]
];
?>

<div class="grid grid-4" style="margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-icon primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
        </div>
        <div class="stat-card-value"><?= number_format($stats['users']) ?></div>
        <div class="stat-card-label">Total Pengguna</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-icon success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
        </div>
        <div class="stat-card-value"><?= number_format($stats['teachers']) ?></div>
        <div class="stat-card-label">Guru</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-icon warning">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
        </div>
        <div class="stat-card-value"><?= number_format($stats['students']) ?></div>
        <div class="stat-card-label">Siswa</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-icon danger">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            </div>
        </div>
        <div class="stat-card-value"><?= number_format($stats['exams']) ?></div>
        <div class="stat-card-label">Total Ujian</div>
    </div>
</div>

<div class="grid grid-2" style="margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-icon primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
        </div>
        <div class="stat-card-value"><?= number_format($stats['questions']) ?></div>
        <div class="stat-card-label">Bank Soal</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-icon success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <div class="stat-card-value"><?= number_format($stats['active_exams']) ?></div>
        <div class="stat-card-label">Ujian Aktif</div>
    </div>
</div>

<div class="grid grid-2">
    <!-- Recent Users -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pengguna Terbaru</h3>
            <a href="<?= url('admin/users') ?>" class="btn btn-sm btn-secondary">Lihat Semua</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Peran</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                        <tr>
                            <td><?= h($user['name']) ?></td>
                            <td><?= h($user['email']) ?></td>
                            <td><span class="badge badge-primary"><?= h($user['role_name']) ?></span></td>
                            <td>
                                <span class="badge <?= $user['is_active'] ? 'badge-success' : 'badge-secondary' ?>">
                                    <?= $user['is_active'] ? 'Aktif' : 'Tidak Aktif' ?>
                                </span>
                            </td>
                            <td><?= format_datetime($user['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentUsers)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;color:var(--text-secondary);padding:2rem">Belum ada pengguna</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Exams -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Ujian Terbaru</h3>
            <a href="<?= url('admin/exams') ?>" class="btn btn-sm btn-secondary">Lihat Semua</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Mapel</th>
                            <th>Guru</th>
                            <th>Status</th>
                            <th>Mulai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentExams as $exam): ?>
                        <tr>
                            <td><?= h($exam['title']) ?></td>
                            <td><?= h($exam['subject_name']) ?></td>
                            <td><?= h($exam['teacher_name']) ?></td>
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
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentExams)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;color:var(--text-secondary);padding:2rem">Belum ada ujian</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card" style="margin-top: 2rem;">
    <div class="card-header">
        <h3 class="card-title">Aksi Cepat</h3>
    </div>
    <div class="card-body">
        <div style="display:flex;gap:1rem;flex-wrap:wrap;">
            <a href="<?= url('admin/users/create') ?>" class="btn btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Pengguna
            </a>
            <a href="<?= url('admin/exams/create') ?>" class="btn btn-success">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Buat Ujian
            </a>
            <a href="<?= url('admin/subjects/create') ?>" class="btn btn-secondary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Mata Pelajaran
            </a>
            <a href="<?= url('admin/classes/create') ?>" class="btn btn-secondary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Kelas
            </a>
        </div>
    </div>
</div>