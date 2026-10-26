<?php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => url('admin/dashboard')],
    ['label' => 'Peran & Hak Akses', 'url' => null]
];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Peran & Hak Akses</h1>
        <p class="page-subtitle">Kelola peran pengguna dan izin akses</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" id="btnCreateRole">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Peran
        </button>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="roleModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="closeRoleModal()"></div>
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 id="modalTitle">Tambah Peran</h3>
            <button type="button" class="modal-close" onclick="closeRoleModal()">&times;</button>
        </div>
        <form id="roleForm">
            <input type="hidden" name="id" id="roleId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="roleName">Nama Peran <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="roleName" class="form-input" required placeholder="Contoh: Administrator">
                </div>

                <div class="form-group">
                    <label class="form-label" for="roleSlug">Slug <span class="text-danger">*</span></label>
                    <input type="text" name="slug" id="roleSlug" class="form-input" required placeholder="admin" pattern="[a-z0-9_-]+" title="Hanya huruf kecil, angka, underscore, dan tanda hubung">
                </div>

                <div class="form-group">
                    <label class="form-label" for="roleDescription">Deskripsi</label>
                    <textarea name="description" id="roleDescription" class="form-input" rows="2"></textarea>
                </div>

                <hr style="margin: 1rem 0;">

                <h4 style="margin-bottom: 1rem;">Izin Akses (Permissions)</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
                    <?php foreach ($allPermissions as $category => $perms): ?>
                        <fieldset style="border: 1px solid var(--border); border-radius: 8px; padding: 1rem;">
                            <legend style="font-weight: 600; margin-bottom: 0.75rem;"><?= h($category) ?></legend>
                            <?php foreach ($perms as $perm): ?>
                                <div class="form-check" style="margin-bottom: 0.5rem;">
                                    <input type="checkbox" name="permissions[]" value="<?= $perm['slug'] ?>" id="perm_<?= $perm['slug'] ?>" class="form-check-input">
                                    <label class="form-check-label" for="perm_<?= $perm['slug'] ?>"><?= h($perm['name']) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </fieldset>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeRoleModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Roles Table -->
<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Nama</th>
                        <th>Slug</th>
                        <th>Deskripsi</th>
                        <th>Izin Akses</th>
                        <th>Jumlah User</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $index => $role): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= h($role['name']) ?></td>
                        <td class="font-mono text-sm"><?= h($role['slug']) ?></td>
                        <td class="text-secondary"><?= h($role['description'] ?? '-') ?></td>
                        <td>
                            <?php if (!empty($role['permissions'])): ?>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.25rem;">
                                    <?php foreach ($role['permissions'] as $perm): ?>
                                        <span class="badge badge-secondary badge-sm"><?= h($perm) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-secondary text-sm">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-primary"><?= (int)($role['user_count'] ?? 0) ?></span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.375rem;">
                                <button type="button" class="btn btn-sm btn-secondary btn-edit-role" data-id="<?= $role['id'] ?>"
                                    data-name="<?= h($role['name']) ?>"
                                    data-slug="<?= h($role['slug']) ?>"
                                    data-description="<?= h($role['description'] ?? '') ?>"
                                    data-permissions='<?= json_encode($role['permissions'] ?? []) ?>'
                                    title="Edit">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <?php if (!in_array($role['slug'], ['admin', 'teacher', 'student'])): ?>
                                    <button type="button" class="btn btn-sm btn-danger btn-delete-role" data-id="<?= $role['id'] ?>" data-name="<?= h($role['name']) ?>" title="Hapus">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                <?php else: ?>
                                    <span class="text-secondary text-sm" title="Peran sistem tidak bisa dihapus">🔒</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($roles)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                            Tidak ada data peran
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('roleModal');
    const form = document.getElementById('roleForm');

    // Open create modal
    document.getElementById('btnCreateRole').addEventListener('click', function() {
        openRoleModal();
    });

    // Edit buttons
    document.querySelectorAll('.btn-edit-role').forEach(btn => {
        btn.addEventListener('click', function() {
            const permissions = JSON.parse(this.dataset.permissions || '[]');
            openRoleModal({
                id: this.dataset.id,
                name: this.dataset.name,
                slug: this.dataset.slug,
                description: this.dataset.description,
                permissions: permissions
            });
        });
    });

    // Delete buttons
    document.querySelectorAll('.btn-delete-role').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Hapus peran "' + this.dataset.name + '"?')) return;
            deleteRole(this.dataset.id);
        });
    });

    // Form submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        saveRole(new FormData(this));
    });
});

function openRoleModal(data = {}) {
    const modal = document.getElementById('roleModal');
    const form = document.getElementById('roleForm');
    const title = document.getElementById('modalTitle');

    form.reset();
    document.getElementById('roleId').value = data.id || '';
    document.getElementById('roleName').value = data.name || '';
    document.getElementById('roleSlug').value = data.slug || '';
    document.getElementById('roleDescription').value = data.description || '';

    // Set permissions checkboxes
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
        cb.checked = data.permissions?.includes(cb.value) || false;
    });

    // Disable slug for system roles
    const slugInput = document.getElementById('roleSlug');
    if (data.id && ['admin', 'teacher', 'student'].includes(data.slug)) {
        slugInput.disabled = true;
        slugInput.title = 'Peran sistem tidak bisa diubah slug-nya';
    } else {
        slugInput.disabled = false;
        slugInput.title = '';
    }

    title.textContent = data.id ? 'Edit Peran' : 'Tambah Peran';
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeRoleModal() {
    document.getElementById('roleModal').style.display = 'none';
    document.body.style.overflow = '';
}

function saveRole(formData) {
    const id = formData.get('id');
    const url = id ? url('admin/roles/' + id) : url('admin/roles');
    const method = id ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: { 'X-CSRF-Token': csrfToken() },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.message?.includes('berhasil') || data.success) {
            toast(data.message || 'Peran disimpan', 'success');
            closeRoleModal();
            setTimeout(() => location.reload(), 500);
        } else {
            toast(data.message || 'Gagal menyimpan', 'error');
        }
    });
}

function deleteRole(id) {
    fetch(url('admin/roles/' + id), {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken() }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success || data.message?.includes('berhasil')) {
            toast('Peran dihapus', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            toast(data.message || 'Gagal menghapus', 'error');
        }
    });
}
</script>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
}
.modal-content {
    position: relative;
    background: var(--bg);
    border-radius: 12px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border);
}
.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-secondary);
    line-height: 1;
}
.modal-body {
    padding: 1.5rem;
    overflow-y: auto;
}
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border);
}
.badge-sm {
    font-size: 0.625rem;
    padding: 0.125rem 0.375rem;
}
</style>