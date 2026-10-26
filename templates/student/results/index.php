<?php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => url('student/dashboard')],
    ['label' => 'Hasil Ujian', 'url' => null]
];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Hasil Ujian</h1>
        <p class="page-subtitle">Riwayat nilai ujian Anda</p>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Ujian</th>
                        <th>Mata Pelajaran</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Skor</th>
                        <th>Grade</th>
                        <th>Status</th>
                        <th style="width: 100px;">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results['data'] as $index => $result): ?>
                    <tr>
                        <td><?= ($results['current_page'] - 1) * $results['per_page'] + $index + 1 ?></td>
                        <td>
                            <div style="font-weight: 500;"><?= h($result['exam_title'] ?? '-') ?></div>
                            <div class="text-sm text-secondary">#<?= $result['exam_id'] ?></div>
                        </td>
                        <td><?= h($result['subject_name'] ?? '-') ?></td>
                        <td><?= format_date($result['started_at']) ?></td>
                        <td class="text-sm">
                            <?php if ($result['started_at'] && $result['completed_at']): ?>
                                <?= round(($result['duration_seconds'] ?? 0) / 60) ?> menit
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($result['score'] !== null): ?>
                                <span style="font-weight: 600; font-size: 1.125rem; color: <?= $result['score'] >= ($result['passing_score'] ?? 70) ? 'var(--success)' : 'var(--danger)' ?>">
                                    <?= number_format($result['score'], 2) ?>%
                                </span>
                            <?php else: ?>
                                <span class="text-secondary">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($result['score'] !== null): ?>
                                <span class="badge badge-primary"><?= $result['grade'] ?? '-' ?></span>
                            <?php else: ?>
                                <span class="text-secondary">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $statusClass = match($result['status']) {
                                'submitted' => 'badge-info',
                                'graded' => 'badge-success',
                                default => 'badge-secondary'
                            };
                            $statusLabel = match($result['status']) {
                                'submitted' => 'Dikumpulkan',
                                'graded' => 'Dinilai',
                                default => $result['status']
                            };
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                        </td>
                        <td>
                            <a href="<?= url('student/results/' . $result['id']) ?>" class="btn btn-sm btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($results['data'])): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 1rem; opacity: 0.5;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10 9 9 9 8 9"/>
                            </svg>
                            <p>Belum ada hasil ujian</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($results['last_page'] > 1): ?>
        <div class="card-footer" style="justify-content: center;">
            <nav class="pagination" aria-label="Pagination">
                <?php if ($results['current_page'] > 1): ?>
                    <a href="<?= url('student/results') ?>?page=<?= $results['current_page'] - 1 ?>">&laquo; Sebelumnya</a>
                <?php else: ?>
                    <span class="disabled">&laquo; Sebelumnya</span>
                <?php endif; ?>

                <?php
                $start = max(1, $results['current_page'] - 2);
                $end = min($results['last_page'], $results['current_page'] + 2);

                if ($start > 1) {
                    echo '<a href="' . url('student/results') . '?page=1">1</a>';
                    if ($start > 2) echo '<span class="disabled">...</span>';
                }

                for ($i = $start; $i <= $end; $i++): ?>
                    <?php if ($i == $results['current_page']): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="<?= url('student/results') ?>?page=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($end < $results['last_page']): ?>
                    <?php if ($end < $results['last_page'] - 1) echo '<span class="disabled">...</span>'; ?>
                    <a href="<?= url('student/results') ?>?page=<?= $results['last_page'] ?>"><?= $results['last_page'] ?></a>
                <?php endif; ?>

                <?php if ($results['current_page'] < $results['last_page']): ?>
                    <a href="<?= url('student/results') ?>?page=<?= $results['current_page'] + 1 ?>">Selanjutnya &raquo;</a>
                <?php else: ?>
                    <span class="disabled">Selanjutnya &raquo;</span>
                <?php endif; ?>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>