<?php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => url('student/dashboard')],
    ['label' => 'Ujian', 'url' => null]
];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Ujian Saya</h1>
        <p class="page-subtitle">Daftar ujian yang diikuti</p>
    </div>
</div>

<!-- Stats -->
<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 1.5rem;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--primary);">
                <?= count(array_filter($exams['data'], fn($e) => $e['status'] === 'not_started')) ?>
            </div>
            <div class="text-secondary">Belum Mulai</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 1.5rem;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--warning);">
                <?= count(array_filter($exams['data'], fn($e) => $e['status'] === 'in_progress')) ?>
            </div>
            <div class="text-secondary">Sedang Mengerjakan</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 1.5rem;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--info);">
                <?= count(array_filter($exams['data'], fn($e) => $e['status'] === 'submitted')) ?>
            </div>
            <div class="text-secondary">Dikumpulkan</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 1.5rem;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--success);">
                <?= count(array_filter($exams['data'], fn($e) => $e['status'] === 'graded')) ?>
            </div>
            <div class="text-secondary">Dinilai</div>
        </div>
    </div>
</div>

<!-- Exams List -->
<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Judul Ujian</th>
                        <th>Mata Pelajaran</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th>Mulai - Selesai</th>
                        <th style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($exams['data'] as $index => $exam): ?>
                    <tr>
                        <td><?= ($exams['current_page'] - 1) * $exams['per_page'] + $index + 1 ?></td>
                        <td>
                            <div style="font-weight: 500;"><?= h($exam['title']) ?></div>
                            <div class="text-sm text-secondary">#<?= $exam['exam_id'] ?></div>
                        </td>
                        <td><?= h($exam['subject_name'] ?? '-') ?></td>
                        <td><?= (int)$exam['duration_minutes'] ?> menit</td>
                        <td>
                            <?php
                            $statusClass = match($exam['status']) {
                                'not_started' => 'badge-secondary',
                                'in_progress' => 'badge-primary',
                                'submitted' => 'badge-info',
                                'graded' => 'badge-success',
                                'absent' => 'badge-warning',
                                default => 'badge-secondary'
                            };
                            $statusLabel = match($exam['status']) {
                                'not_started' => 'Belum Mulai',
                                'in_progress' => 'Sedang Mengerjakan',
                                'submitted' => 'Dikumpulkan',
                                'graded' => 'Dinilai',
                                'absent' => 'Tidak Hadir',
                                default => $exam['status']
                            };
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                        </td>
                        <td class="text-sm">
                            <div><?= format_datetime($exam['start_at']) ?></div>
                            <div class="text-secondary">s/d <?= format_datetime($exam['end_at']) ?></div>
                        </td>
                        <td>
                            <?php if ($exam['status'] === 'not_started' || $exam['status'] === 'in_progress'): ?>
                                <a href="<?= url('student/exams/' . $exam['exam_id'] . '/take') ?>" class="btn btn-primary btn-sm">
                                    <?= $exam['status'] === 'in_progress' ? 'Lanjutkan' : 'Mulai Ujian' ?>
                                </a>
                            <?php elseif ($exam['status'] === 'submitted' || $exam['status'] === 'graded'): ?>
                                <a href="<?= url('student/results/' . $exam['id']) ?>" class="btn btn-secondary btn-sm">
                                    Lihat Hasil
                                </a>
                            <?php else: ?>
                                <span class="text-secondary text-sm">Tidak tersedia</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($exams['data'])): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 1rem; opacity: 0.5;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10 9 9 9 8 9"/>
                            </svg>
                            <p>Belum ada ujian yang diikuti</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($exams['last_page'] > 1): ?>
        <div class="card-footer" style="justify-content: center;">
            <nav class="pagination" aria-label="Pagination">
                <?php if ($exams['current_page'] > 1): ?>
                    <a href="<?= url('student/exams') ?>?page=<?= $exams['current_page'] - 1 ?>">&laquo; Sebelumnya</a>
                <?php else: ?>
                    <span class="disabled">&laquo; Sebelumnya</span>
                <?php endif; ?>

                <?php
                $start = max(1, $exams['current_page'] - 2);
                $end = min($exams['last_page'], $exams['current_page'] + 2);

                if ($start > 1) {
                    echo '<a href="' . url('student/exams') . '?page=1">1</a>';
                    if ($start > 2) echo '<span class="disabled">...</span>';
                }

                for ($i = $start; $i <= $end; $i++): ?>
                    <?php if ($i == $exams['current_page']): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="<?= url('student/exams') ?>?page=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($end < $exams['last_page']): ?>
                    <?php if ($end < $exams['last_page'] - 1) echo '<span class="disabled">...</span>'; ?>
                    <a href="<?= url('student/exams') ?>?page=<?= $exams['last_page'] ?>"><?= $exams['last_page'] ?></a>
                <?php endif; ?>

                <?php if ($exams['current_page'] < $exams['last_page']): ?>
                    <a href="<?= url('student/exams') ?>?page=<?= $exams['current_page'] + 1 ?>">Selanjutnya &raquo;</a>
                <?php else: ?>
                    <span class="disabled">Selanjutnya &raquo;</span>
                <?php endif; ?>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>