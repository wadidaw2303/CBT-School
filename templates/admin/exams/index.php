<?php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => url('admin/dashboard')],
    ['label' => 'Ujian', 'url' => null]
];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Kelola Ujian</h1>
        <p class="page-subtitle">Kelola ujian, soal, dan peserta</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('admin/exams/create') ?>" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Buat Ujian Baru
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form method="GET" action="<?= url('admin/exams') ?>" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: end;">
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label" for="search">Cari</label>
                <input type="text" name="search" id="search" class="form-input" placeholder="Judul atau slug ujian..." value="<?= h($filters['search'] ?? '') ?>">
            </div>
            <div style="min-width: 150px;">
                <label class="form-label" for="status">Status</label>
                <select name="status" id="status" class="form-input form-select">
                    <option value="">Semua Status</option>
                    <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Dipublikasikan</option>
                    <option value="ongoing" <?= ($filters['status'] ?? '') === 'ongoing' ? 'selected' : '' ?>>Berlangsung</option>
                    <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Selesai</option>
                    <option value="archived" <?= ($filters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Diarsipkan</option>
                </select>
            </div>
            <div style="min-width: 180px;">
                <label class="form-label" for="subject">Mata Pelajaran</label>
                <select name="subject" id="subject" class="form-input form-select">
                    <option value="">Semua Mata Pelajaran</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?= $subject['id'] ?>" <?= ($filters['subject'] ?? '') == $subject['id'] ? 'selected' : '' ?>><?= h($subject['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="<?= url('admin/exams') ?>" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Exams Table -->
<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Judul</th>
                        <th>Mapel</th>
                        <th>Kelas</th>
                        <th>Guru</th>
                        <th>Tahun/Semester</th>
                        <th>Durasi</th>
                        <th>Soal</th>
                        <th>Peserta</th>
                        <th>Status</th>
                        <th>Mulai - Selesai</th>
                        <th style="width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($exams['data'] as $index => $exam): ?>
                    <tr>
                        <td><?= ($exams['current_page'] - 1) * $exams['per_page'] + $index + 1 ?></td>
                        <td>
                            <div style="font-weight: 500;"><?= h($exam['title']) ?></div>
                            <div class="text-sm text-secondary">#<?= $exam['id'] ?> · <?= h($exam['slug']) ?></div>
                        </td>
                        <td><?= h($exam['subject_name'] ?? '-') ?></td>
                        <td><?= h($exam['class_name'] ?? '-') ?></td>
                        <td><?= h($exam['teacher_name'] ?? '-') ?></td>
                        <td>
                            <div><?= h($exam['academic_year_name'] ?? '-') ?></div>
                            <div class="text-sm text-secondary"><?= h($exam['semester_name'] ?? '-') ?></div>
                        </td>
                        <td><?= (int)$exam['duration_minutes'] ?> menit</td>
                        <td><?= (int)$exam['total_questions'] ?></td>
                        <td>
                            <span class="badge badge-primary"><?= (int)($exam['participant_count'] ?? 0) ?></span>
                        </td>
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
                        <td class="text-sm">
                            <div><?= format_datetime($exam['start_at']) ?></div>
                            <div class="text-secondary">s/d <?= format_datetime($exam['end_at']) ?></div>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.375rem; flex-wrap: wrap;">
                                <a href="<?= url('admin/exams/' . $exam['id']) ?>" class="btn btn-sm btn-secondary" title="Detail">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <a href="<?= url('admin/exams/' . $exam['id'] . '/edit') ?>" class="btn btn-sm btn-secondary" title="Edit">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <a href="<?= url('admin/exams/' . $exam['id'] . '/questions') ?>" class="btn btn-sm btn-primary" title="Kelola Soal">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                </a>
                                <a href="<?= url('admin/exams/' . $exam['id'] . '/participants') ?>" class="btn btn-sm btn-secondary" title="Peserta">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                </a>
                                <?php if ($exam['status'] === 'draft'): ?>
                                    <button type="button" class="btn btn-sm btn-success btn-publish" data-id="<?= $exam['id'] ?>" title="Publikasikan">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                    </button>
                                <?php elseif ($exam['status'] === 'published'): ?>
                                    <button type="button" class="btn btn-sm btn-warning btn-unpublish" data-id="<?= $exam['id'] ?>" title="Kembalikan ke Draft">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                                    </button>
                                <?php endif; ?>
                                <?php if ($exam['id'] !== auth()->id): ?>
                                    <form action="<?= url('admin/exams/' . $exam['id']) ?>" method="POST" style="display: inline;" onsubmit="return confirm('Yakin hapus ujian ini?')">
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
                    <?php if (empty($exams['data'])): ?>
                    <tr>
                        <td colspan="12" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                            Tidak ada data ujian
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
                    <a href="<?= url('admin/exams') ?>?page=<?= $exams['current_page'] - 1 ?>&<?= http_build_query(array_filter($filters)) ?>">&laquo; Sebelumnya</a>
                <?php else: ?>
                    <span class="disabled">&laquo; Sebelumnya</span>
                <?php endif; ?>

                <?php
                $start = max(1, $exams['current_page'] - 2);
                $end = min($exams['last_page'], $exams['current_page'] + 2);

                if ($start > 1) {
                    echo '<a href="' . url('admin/exams') . '?page=1&' . http_build_query(array_filter($filters)) . '">1</a>';
                    if ($start > 2) echo '<span class="disabled">...</span>';
                }

                for ($i = $start; $i <= $end; $i++): ?>
                    <?php if ($i == $exams['current_page']): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="<?= url('admin/exams') ?>?page=<?= $i ?>&<?= http_build_query(array_filter($filters)) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($end < $exams['last_page']): ?>
                    <?php if ($end < $exams['last_page'] - 1) echo '<span class="disabled">...</span>'; ?>
                    <a href="<?= url('admin/exams') ?>?page=<?= $exams['last_page'] ?>&<?= http_build_query(array_filter($filters)) ?>"><?= $exams['last_page'] ?></a>
                <?php endif; ?>

                <?php if ($exams['current_page'] < $exams['last_page']): ?>
                    <a href="<?= url('admin/exams') ?>?page=<?= $exams['current_page'] + 1 ?>&<?= http_build_query(array_filter($filters)) ?>">Selanjutnya &raquo;</a>
                <?php else: ?>
                    <span class="disabled">Selanjutnya &raquo;</span>
                <?php endif; ?>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Publish button
    document.querySelectorAll('.btn-publish').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Publikasikan ujian ini? Peserta akan bisa melihat dan mengerjakan ujian.')) return;
            const id = this.dataset.id;
            fetch(url('admin/exams/' + id + '/publish'), {
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
    });

    // Unpublish button
    document.querySelectorAll('.btn-unpublish').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Kembalikan ujian ke draft? Peserta tidak akan bisa mengakses ujian.')) return;
            const id = this.dataset.id;
            fetch(url('admin/exams/' + id + '/unpublish'), {
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
    });
});
</script>