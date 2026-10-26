<?php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => null]
];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Selamat Datang, <?= h(auth()->name) ?></h1>
        <p class="page-subtitle">Kelola ujian dan lihat hasil Anda</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-4" style="margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-icon primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            </div>
        </div>
        <div class="stat-card-value"><?= count($upcomingExams) ?></div>
        <div class="stat-card-label">Ujian Akan Datang</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-icon warning">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22v-8"/><path d="M6 14l6-6 6 6"/></svg>
            </div>
        </div>
        <div class="stat-card-value"><?= count($inProgress) ?></div>
        <div class="stat-card-label">Sedang Berlangsung</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-icon success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <div class="stat-card-value"><?= count($completed) ?></div>
        <div class="stat-card-label">Ujian Selesai</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-card-icon danger">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
        </div>
        <div class="stat-card-value">
            <?php
            $avg = 0;
            if (!empty($completed)) {
                $avg = array_sum(array_column($completed, 'percentage')) / count($completed);
            }
            echo number_format($avg, 1);
            ?>
        </div>
        <div class="stat-card-label">Rata-rata Nilai</div>
    </div>
</div>

<!-- Upcoming Exams -->
<?php if (!empty($upcomingExams)): ?>
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Ujian Akan Datang</h3>
        <a href="<?= url('student/exams') ?>" class="btn btn-sm btn-secondary">Lihat Semua</a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ujian</th>
                        <th>Mata Pelajaran</th>
                        <th>Mulai</th>
                        <th>Durasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upcomingExams as $exam): ?>
                    <tr>
                        <td><?= h($exam['title']) ?></td>
                        <td><?= h($exam['subject_name']) ?></td>
                        <td><?= format_datetime($exam['start_at']) ?></td>
                        <td><?= $exam['duration_minutes'] ?> menit</td>
                        <td>
                            <span class="badge badge-secondary">Belum Mulai</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- In Progress Exams -->
<?php if (!empty($inProgress)): ?>
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Sedang Berlangsung</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ujian</th>
                        <th>Mata Pelajaran</th>
                        <th>Mulai</th>
                        <th>Sisa Waktu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inProgress as $exam): ?>
                    <tr>
                        <td><?= h($exam['title']) ?></td>
                        <td><?= h($exam['subject_name'] ?? '') ?></td>
                        <td><?= format_datetime($exam['started_at']) ?></td>
                        <td>
                            <?php
                            $endTime = strtotime($exam['started_at']) + ($exam['duration_minutes'] * 60);
                            $remaining = $endTime - time();
                            if ($remaining > 0) {
                                $h = floor($remaining / 3600);
                                $m = floor(($remaining % 3600) / 60);
                                $s = $remaining % 60;
                                echo "<span class='badge badge-warning'>{$h}j {$m}m {$s}d</span>";
                            } else {
                                echo "<span class='badge badge-danger'>Waktu Habis</span>";
                            }
                            ?>
                        </td>
                        <td>
                            <a href="<?= url('student/exams/' . $exam['exam_id'] . '/take') ?>" class="btn btn-sm btn-primary">Lanjutkan</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Completed Exams -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Ujian Selesai</h3>
        <a href="<?= url('student/results') ?>" class="btn btn-sm btn-secondary">Lihat Semua</a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ujian</th>
                        <th>Mata Pelajaran</th>
                        <th>Tanggal</th>
                        <th>Skor</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($completed as $exam): ?>
                    <tr>
                        <td><?= h($exam['exam_title']) ?></td>
                        <td><?= h($exam['subject_name']) ?></td>
                        <td><?= format_datetime($exam['finished_at']) ?></td>
                        <td>
                            <?php if ($exam['percentage'] !== null): ?>
                                <span style="font-weight: 600; color: <?= $exam['is_passed'] ? 'var(--success)' : 'var(--danger)' ?>">
                                    <?= number_format($exam['percentage'], 1) ?>%
                                </span>
                            <?php else: ?>
                                <span class="badge badge-warning">Belum Dinilai</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $statusLabel = match($exam['status']) {
                                'graded' => 'Dinilai',
                                'submitted' => 'Menunggu Penilaian',
                                default => $exam['status']
                            };
                            $statusClass = match($exam['status']) {
                                'graded' => 'badge-success',
                                'submitted' => 'badge-warning',
                                default => 'badge-secondary'
                            };
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                        </td>
                        <td>
                            <?php if ($exam['status'] === 'graded'): ?>
                                <a href="<?= url('student/results/' . $exam['id']) ?>" class="btn btn-sm btn-secondary">Lihat Hasil</a>
                            <?php else: ?>
                                <span class="badge badge-secondary">Menunggu</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($completed)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;color:var(--text-secondary);padding:2rem">Belum ada ujian selesai</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Navigasi Cepat</h3>
    </div>
    <div class="card-body">
        <div style="display:flex;gap:1rem;flex-wrap:wrap;">
            <a href="<?= url('student/exams') ?>" class="btn btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                Semua Ujian
            </a>
            <a href="<?= url('student/results') ?>" class="btn btn-secondary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Hasil Ujian
            </a>
            <a href="<?= url('student/profile') ?>" class="btn btn-secondary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Profil Saya
            </a>
        </div>
    </div>
</div>