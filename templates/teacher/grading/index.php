<?php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => url('teacher/dashboard')],
    ['label' => 'Penilaian Ujian', 'url' => null]
];
?>

<div class="page-header">
    <h1 class="page-title">Penilaian Ujian</h1>
    <p class="page-subtitle">Kelola penilaian manual soal essay dan rilis nilai</p>
</div>

<!-- Filter Bar -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form method="GET" action="<?= url('teacher/grading') ?>" class="grid" style="grid-template-columns: 1fr 1fr 1fr auto; gap: 1rem; align-items: end;">
            <div>
                <label class="label">Ujian</label>
                <select name="exam_id" class="input" onchange="this.form.submit()">
                    <option value="">Semua Ujian</option>
                    <?php foreach ($exams as $exam): ?>
                        <option value="<?= $exam['id'] ?>" <?= ($filters['exam_id'] ?? '') == $exam['id'] ? 'selected' : '' ?>>
                            <?= h($exam['title']) ?> (<?= h($exam['subject_name']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="label">Status</label>
                <select name="status" class="input" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="submitted" <?= ($filters['status'] ?? '') === 'submitted' ? 'selected' : '' ?>>Sudah Diserahkan</option>
                    <option value="graded" <?= ($filters['status'] ?? '') === 'graded' ? 'selected' : '' ?>>Sudah Dinilai</option>
                    <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Menunggu Penilaian</option>
                </select>
            </div>
            <div>
                <label class="label">Kelas</label>
                <select name="class_id" class="input" onchange="this.form.submit()">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= $class['id'] ?>" <?= ($filters['class_id'] ?? '') == $class['id'] ? 'selected' : '' ?>>
                            <?= h($class['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <a href="<?= url('teacher/grading') ?>" class="btn btn-secondary" style="height: 40px;">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--info);"><?= $stats['pending_grading'] ?? 0 ?></div>
            <div class="text-secondary">Menunggu Penilaian</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--warning);"><?= $stats['in_progress_grading'] ?? 0 ?></div>
            <div class="text-secondary">Sedang Dinilai</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--success);"><?= $stats['completed_grading'] ?? 0 ?></div>
            <div class="text-secondary">Selesai Dinilai</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary);"><?= $stats['total_participants'] ?? 0 ?></div>
            <div class="text-secondary">Total Peserta</div>
        </div>
    </div>
</div>

<!-- Participants Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Peserta yang Perlu Dinilai</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($participants)): ?>
            <div class="empty-state" style="padding: 3rem; text-align: center;">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--text-tertiary); margin-bottom: 1rem;">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <h3 style="margin-bottom: 0.5rem;">Tidak ada peserta</h3>
                <p class="text-secondary">Tidak ada peserta ujian yang memerlukan penilaian manual.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Ujian</th>
                            <th>Peserta</th>
                            <th>Kelas</th>
                            <th>Skor Otomatis</th>
                            <th>Essay Menunggu</th>
                            <th>Status</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($participants as $index => $p): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>
                                <div style="font-weight: 500;"><?= h($p['exam_title']) ?></div>
                                <div class="text-sm text-secondary"><?= h($p['subject_name'] ?? '-') ?></div>
                            </td>
                            <td>
                                <div style="font-weight: 500;"><?= h($p['student_name']) ?></div>
                                <div class="text-sm text-secondary"><?= h($p['student_nis'] ?? '') ?></div>
                            </td>
                            <td><?= h($p['class_name'] ?? '-') ?></td>
                            <td>
                                <?php if ($p['percentage'] !== null): ?>
                                    <div class="font-mono font-medium" style="color: <?= $p['is_passed'] ? 'var(--success)' : 'var(--danger)' ?>">
                                        <?= number_format($p['percentage'], 2) ?>%
                                    </div>
                                    <div class="text-sm text-secondary"><?= $p['score'] ?> / <?= $p['max_score'] ?></div>
                                <?php else: ?>
                                    <span class="text-secondary">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= ($p['pending_essay_count'] ?? 0) > 0 ? 'badge-warning' : 'badge-success' ?>">
                                    <?= $p['pending_essay_count'] ?? 0 ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= match($p['status']) {
                                    'not_started' => 'badge-secondary',
                                    'in_progress' => 'badge-info',
                                    'submitted' => 'badge-warning',
                                    'graded' => 'badge-success',
                                    default => 'badge-secondary'
                                } ?>">
                                    <?= match($p['status']) {
                                        'not_started' => 'Belum Mulai',
                                        'in_progress' => 'Sedang Ujian',
                                        'submitted' => 'Diserahkan',
                                        'graded' => 'Dinilai',
                                        default => $p['status']
                                    } ?>
                                </span>
                            </td>
                            <td><?= $p['started_at'] ? format_datetime($p['started_at']) : '<span class="text-secondary">-</span>' ?></td>
                            <td><?= $p['completed_at'] ? format_datetime($p['completed_at']) : '<span class="text-secondary">-</span>' ?></td>
                            <td>
                                <div class="flex" style="gap: 0.5rem;">
                                    <?php if (($p['pending_essay_count'] ?? 0) > 0): ?>
                                        <a href="<?= url('teacher/grading/attempt/' . $p['id']) ?>" class="btn btn-primary btn-sm">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            Nilai Essay
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= url('teacher/grading/detail/' . $p['id']) ?>" class="btn btn-secondary btn-sm">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
                <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-top: 1px solid var(--border);">
                    <div class="text-sm text-secondary">
                        Menampilkan <?= $pagination['from'] ?>-<?= $pagination['to'] ?> dari <?= $pagination['total'] ?> data
                    </div>
                    <nav class="pagination" aria-label="Pagination">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <a href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] - 1])) ?>" class="btn btn-secondary btn-sm">Sebelumnya</a>
                        <?php endif; ?>
                        <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                            <a href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>" class="btn btn-sm <?= $i == $pagination['current_page'] ? 'btn-primary' : 'btn-secondary' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                            <a href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] + 1])) ?>" class="btn btn-secondary btn-sm">Selanjutnya</a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Bulk Actions (for graded exams) -->
<?php if (!empty($hasGradedExams)): ?>
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Rilis Nilai Massal</h3>
    </div>
    <div class="card-body">
        <p class="text-secondary" style="margin-bottom: 1rem;">Rilis nilai untuk seluruh peserta ujian yang sudah selesai dinilai (termasuk essay).</p>
        <form method="POST" action="<?= url('teacher/grading/release') ?>" onsubmit="return confirm('Yakin ingin merilis nilai untuk ujian ini?')">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <select name="exam_id" class="input" style="max-width: 400px; display: inline-block; margin-right: 1rem;" required>
                <option value="">Pilih Ujian</option>
                <?php foreach ($hasGradedExams as $exam): ?>
                    <option value="<?= $exam['id'] ?>"><?= h($exam['title']) ?> (<?= $exam['graded_count'] ?> dinilai)</option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-success">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-8 22 0"/></svg>
                Rilis Nilai
            </button>
        </form>
    </div>
</div>
<?php endif; ?>