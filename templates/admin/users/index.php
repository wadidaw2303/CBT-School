<?php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => url('admin/dashboard')],
    ['label' => 'Pengguna', 'url' => null]
];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Kelola Pengguna</h1>
        <p class="page-subtitle">Kelola akun administrator, guru, dan siswa</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('admin/users/create') ?>" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Pengguna
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form method="GET" action="<?= url('admin/users') ?>" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: end;">
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label" for="search">Cari</label>
                <input type="text" name="search" id="search" class="form-input" placeholder="Nama atau email..." value="<?= h($filters['search'] ?? '') ?>">
            </div>
            <div style="min-width: 150px;">
                <label class="form-label" for="role">Peran</label>
                <select name="role" id="role" class="form-input form-select">
                    <option value="">Semua Peran</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= h($role->slug) ?>" <?= ($filters['role'] ?? '') === $role->slug ? 'selected' : '' ?>><?= h($role->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="min-width: 150px;">
                <label class="form-label" for="status">Status</label>
                <select name="status" id="status" class="form-input form-select">
                    <option value="">Semua Status</option>
                    <option value="1" <?= ($filters['status'] ?? '') === '1' ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= ($filters['status'] ?? '') === '0' ? 'selected' : '' ?>>Tidak Aktif</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="<?= url('admin/users') ?>" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Peran</th>
                        <th>Status</th>
                        <th>Login Terakhir</th>
                        <th>Dibuat</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users['data'] as $index => $user): ?>
                    <tr>
                        <td><?= ($users['current_page'] - 1) * $users['per_page'] + $index + 1 ?></td>
                        <td>
                            <div style="font-weight: 500;"><?= h($user['name']) ?></div>
                            <?php if ($user['phone']): ?>
                                <div class="text-sm text-secondary"><?= h($user['phone']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= h($user['email']) ?></td>
                        <td>
                            <span class="badge badge-primary"><?= h($user['role_name']) ?></span>
                        </td>
                        <td>
                            <span class="badge <?= $user['is_active'] ? 'badge-success' : 'badge-secondary' ?>">
                                <?= $user['is_active'] ? 'Aktif' : 'Tidak Aktif' ?>
                            </span>
                        </td>
                        <td><?= $user['last_login_at'] ? format_datetime($user['last_login_at']) : '<span class="text-secondary">Belum pernah</span>' ?></td>
                        <td><?= format_datetime($user['created_at']) ?></td>
                        <td>
                            <div style="display: flex; gap: 0.375rem;">
                                <a href="<?= url('admin/users/' . $user['id'] . '/edit') ?>" class="btn btn-sm btn-secondary" title="Edit">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <?php if ($user['id'] !== auth()->id): ?>
                                    <form action="<?= url('admin/users/' . $user['id']) ?>" method="POST" style="display: inline;" onsubmit="return confirm('Yakin hapus pengguna ini?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users['data'])): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                            Tidak ada data pengguna
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($users['last_page'] > 1): ?>
        <div class="card-footer" style="justify-content: center;">
            <nav class="pagination" aria-label="Pagination">
                <?php if ($users['current_page'] > 1): ?>
                    <a href="<?= url('admin/users') ?>?page=<?= $users['current_page'] - 1 ?>&<?= http_build_query(array_filter($filters)) ?>">&laquo; Sebelumnya</a>
                <?php else: ?>
                    <span class="disabled">&laquo; Sebelumnya</span>
                <?php endif; ?>

                <?php
                $start = max(1, $users['current_page'] - 2);
                $end = min($users['last_page'], $users['current_page'] + 2);

                if ($start > 1) {
                    echo '<a href="' . url('admin/users') . '?page=1&' . http_build_query(array_filter($filters)) . '">1</a>';
                    if ($start > 2) echo '<span class="disabled">...</span>';
                }

                for ($i = $start; $i <= $end; $i++): ?>
                    <?php if ($i == $users['current_page']): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="<?= url('admin/users') ?>?page=<?= $i ?>&<?= http_build_query(array_filter($filters)) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($end < $users['last_page']): ?>
                    <?php if ($end < $users['last_page'] - 1) echo '<span class="disabled">...</span>'; ?>
                    <a href="<?= url('admin/users') ?>?page=<?= $users['last_page'] ?>&<?= http_build_query(array_filter($filters)) ?>"><?= $users['last_page'] ?></a>
                <?php endif; ?>

                <?php if ($users['current_page'] < $users['last_page']): ?>
                    <a href="<?= url('admin/users') ?>?page=<?= $users['current_page'] + 1 ?>&<?= http_build_query(array_filter($filters)) ?>">Selanjutnya &raquo;</a>
                <?php else: ?>
                    <span class="disabled">Selanjutnya &raquo;</span>
                <?php endif; ?>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>