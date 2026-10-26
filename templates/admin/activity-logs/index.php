<?php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => url('admin/dashboard')],
    ['label' => 'Log Aktivitas', 'url' => null]
];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Log Aktivitas</h1>
        <p class="page-subtitle">Riwayat aktivitas sistem</p>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Model</th>
                        <th>ID Record</th>
                        <th>Detail</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs['data'] as $index => $log): ?>
                    <tr>
                        <td><?= ($logs['current_page'] - 1) * $logs['per_page'] + $index + 1 ?></td>
                        <td class="text-sm"><?= format_datetime($log['created_at']) ?></td>
                        <td><?= h($log['user_name'] ?? 'System') ?></td>
                        <td>
                            <?php
                            $actionClass = match($log['action']) {
                                'created' => 'badge-success',
                                'updated' => 'badge-primary',
                                'deleted' => 'badge-danger',
                                'published' => 'badge-info',
                                'unpublished' => 'badge-warning',
                                'login' => 'badge-primary',
                                'logout' => 'badge-secondary',
                                default => 'badge-secondary'
                            };
                            $actionLabel = match($log['action']) {
                                'created' => 'Dibuat',
                                'updated' => 'Diperbarui',
                                'deleted' => 'Dihapus',
                                'published' => 'Dipublikasikan',
                                'unpublished' => 'Dikembalikan ke Draft',
                                'login' => 'Login',
                                'logout' => 'Logout',
                                default => ucfirst($log['action'])
                            };
                            ?>
                            <span class="badge <?= $actionClass ?>"><?= $actionLabel ?></span>
                        </td>
                        <td class="text-sm"><?= h($log['model_type'] ?? '-') ?></td>
                        <td class="font-mono text-sm">#<?= $log['model_id'] ?? '-' ?></td>
                        <td>
                            <?php if ($log['old_values'] || $log['new_values']): ?>
                                <details style="cursor: pointer;">
                                    <summary class="text-sm text-secondary">Lihat perubahan</summary>
                                    <div class="font-mono text-xs" style="margin-top: 0.5rem; max-height: 200px; overflow: auto;">
                                        <?php if ($log['old_values']): ?>
                                            <div style="color: var(--danger);">Old: <?= h(json_encode($log['old_values'], JSON_PRETTY_PRINT)) ?></div>
                                        <?php endif; ?>
                                        <?php if ($log['new_values']): ?>
                                            <div style="color: var(--success);">New: <?= h(json_encode($log['new_values'], JSON_PRETTY_PRINT)) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </details>
                            <?php else: ?>
                                <span class="text-secondary text-sm">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-sm font-mono"><?= h($log['ip_address'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($logs['data'])): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                            Tidak ada log aktivitas
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($logs['last_page'] > 1): ?>
        <div class="card-footer" style="justify-content: center;">
            <nav class="pagination" aria-label="Pagination">
                <?php if ($logs['current_page'] > 1): ?>
                    <a href="<?= url('admin/activity-logs') ?>?page=<?= $logs['current_page'] - 1 ?>">&laquo; Sebelumnya</a>
                <?php else: ?>
                    <span class="disabled">&laquo; Sebelumnya</span>
                <?php endif; ?>

                <?php
                $start = max(1, $logs['current_page'] - 2);
                $end = min($logs['last_page'], $logs['current_page'] + 2);

                if ($start > 1) {
                    echo '<a href="' . url('admin/activity-logs') . '?page=1">1</a>';
                    if ($start > 2) echo '<span class="disabled">...</span>';
                }

                for ($i = $start; $i <= $end; $i++): ?>
                    <?php if ($i == $logs['current_page']): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="<?= url('admin/activity-logs') ?>?page=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($end < $logs['last_page']): ?>
                    <?php if ($end < $logs['last_page'] - 1) echo '<span class="disabled">...</span>'; ?>
                    <a href="<?= url('admin/activity-logs') ?>?page=<?= $logs['last_page'] ?>"><?= $logs['last_page'] ?></a>
                <?php endif; ?>

                <?php if ($logs['current_page'] < $logs['last_page']): ?>
                    <a href="<?= url('admin/activity-logs') ?>?page=<?= $logs['current_page'] + 1 ?>">Selanjutnya &raquo;</a>
                <?php else: ?>
                    <span class="disabled">Selanjutnya &raquo;</span>
                <?php endif; ?>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>