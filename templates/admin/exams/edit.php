<?php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => url('admin/dashboard')],
    ['label' => 'Ujian', 'url' => url('admin/exams')],
    ['label' => 'Edit', 'url' => null]
];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Edit Ujian</h1>
        <p class="page-subtitle">Perbarui informasi ujian: <?= h($exam['title']) ?></p>
    </div>
    <div class="page-actions">
        <a href="<?= url('admin/exams/' . $exam['id']) ?>" class="btn btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Kembali
        </a>
    </div>
</div>

<div class="grid grid-2" style="gap: 1.5rem;">
    <!-- Main Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Informasi Ujian</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= url('admin/exams/' . $exam['id']) ?>" id="examForm">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PUT">

                <div class="form-group">
                    <label class="form-label" for="title">Judul Ujian <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="title" class="form-input" required
                        value="<?= h(old('title', $exam['title'])) ?>" placeholder="Contoh: Ujian Tengah Semester Matematika">
                </div>

                <div class="form-group">
                    <label class="form-label" for="slug">Slug <span class="text-danger">*</span></label>
                    <input type="text" name="slug" id="slug" class="form-input" required
                        value="<?= h(old('slug', $exam['slug'])) ?>" placeholder="ujian-tengah-semester-matematika"
                        pattern="[a-z0-9-]+" title="Hanya huruf kecil, angka, dan tanda hubung">
                    <div class="form-hint">URL-friendly identifier</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Deskripsi</label>
                    <textarea name="description" id="description" class="form-input" rows="3"
                        placeholder="Deskripsi singkat ujian..."><?= h(old('description', $exam['description'])) ?></textarea>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="subject_id">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="subject_id" id="subject_id" class="form-input form-select" required>
                            <option value="">Pilih Mata Pelajaran</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?= $subject['id'] ?>" <?= old('subject_id', $exam['subject_id']) == $subject['id'] ? 'selected' : '' ?>>
                                    <?= h($subject['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="class_id">Kelas (Opsional)</label>
                        <select name="class_id" id="class_id" class="form-input form-select">
                            <option value="">Semua Kelas</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= $class['id'] ?>" <?= old('class_id', $exam['class_id']) == $class['id'] ? 'selected' : '' ?>>
                                    <?= h($class['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="teacher_id">Guru Pengampu <span class="text-danger">*</span></label>
                        <select name="teacher_id" id="teacher_id" class="form-input form-select" required>
                            <option value="">Pilih Guru</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?= $teacher['id'] ?>" <?= old('teacher_id', $exam['teacher_id']) == $teacher['id'] ? 'selected' : '' ?>>
                                    <?= h($teacher['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="academic_year_id">Tahun Ajaran <span class="text-danger">*</span></label>
                        <select name="academic_year_id" id="academic_year_id" class="form-input form-select" required>
                            <option value="">Pilih Tahun Ajaran</option>
                            <?php foreach ($academicYears as $ay): ?>
                                <option value="<?= $ay['id'] ?>" <?= old('academic_year_id', $exam['academic_year_id']) == $ay['id'] ? 'selected' : '' ?>>
                                    <?= h($ay['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="semester_id">Semester <span class="text-danger">*</span></label>
                        <select name="semester_id" id="semester_id" class="form-input form-select" required>
                            <option value="">Pilih Semester</option>
                            <?php foreach ($semesters as $semester): ?>
                                <option value="<?= $semester['id'] ?>" <?= old('semester_id', $exam['semester_id']) == $semester['id'] ? 'selected' : '' ?>>
                                    <?= h($semester['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Settings Panel -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pengaturan Ujian</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= url('admin/exams/' . $exam['id']) ?>" form="examForm">
                <div class="form-group">
                    <label class="form-label" for="duration_minutes">Durasi (menit) <span class="text-danger">*</span></label>
                    <input type="number" name="duration_minutes" id="duration_minutes" class="form-input" required
                        value="<?= h(old('duration_minutes', $exam['duration_minutes'])) ?>" min="1" max="1440">
                </div>

                <div class="form-group">
                    <label class="form-label" for="passing_score">Nilai Kelulusan (%) <span class="text-danger">*</span></label>
                    <input type="number" name="passing_score" id="passing_score" class="form-input" required
                        value="<?= h(old('passing_score', $exam['passing_score'])) ?>" min="0" max="100" step="0.01">
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="start_at">Waktu Mulai <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="start_at" id="start_at" class="form-input" required
                            value="<?= h(old('start_at', date('Y-m-d\TH:i', strtotime($exam['start_at'])))) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="end_at">Waktu Selesai <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="end_at" id="end_at" class="form-input" required
                            value="<?= h(old('end_at', date('Y-m-d\TH:i', strtotime($exam['end_at'])))) ?>">
                    </div>
                </div>

                <hr style="margin: 1.5rem 0;">

                <h4 style="margin-bottom: 1rem; font-size: 0.875rem; text-transform: uppercase; color: var(--text-secondary);">Opsi Ujian</h4>

                <div class="form-check" style="margin-bottom: 0.75rem;">
                    <input type="checkbox" name="shuffle_questions" id="shuffle_questions" value="1"
                        class="form-check-input" <?= old('shuffle_questions', $exam['shuffle_questions'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="shuffle_questions">Acak urutan soal</label>
                </div>

                <div class="form-check" style="margin-bottom: 0.75rem;">
                    <input type="checkbox" name="shuffle_options" id="shuffle_options" value="1"
                        class="form-check-input" <?= old('shuffle_options', $exam['shuffle_options'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="shuffle_options">Acak urutan opsi jawaban (MCQ)</label>
                </div>

                <div class="form-check" style="margin-bottom: 0.75rem;">
                    <input type="checkbox" name="show_results" id="show_results" value="1"
                        class="form-check-input" <?= old('show_results', $exam['show_results'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="show_results">Tampilkan hasil setelah selesai</label>
                </div>

                <div class="form-check" style="margin-bottom: 0.75rem;">
                    <input type="checkbox" name="show_correct_answers" id="show_correct_answers" value="1"
                        class="form-check-input" <?= old('show_correct_answers', $exam['show_correct_answers'] ?? 0) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="show_correct_answers">Tampilkan jawaban benar (di mode review)</label>
                </div>

                <div class="form-check" style="margin-bottom: 0.75rem;">
                    <input type="checkbox" name="browser_lockdown" id="browser_lockdown" value="1"
                        class="form-check-input" <?= old('browser_lockdown', $exam['browser_lockdown'] ?? 0) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="browser_lockdown">Fullscreen enforcement (anti-cheat)</label>
                </div>

                <hr style="margin: 1.5rem 0;">

                <div class="form-group">
                    <label class="form-label" for="retry_attempts">Maksimal Percobaan Ulang</label>
                    <input type="number" name="retry_attempts" id="retry_attempts" class="form-input"
                        value="<?= h(old('retry_attempts', $exam['retry_attempts'] ?? 0)) ?>" min="0" max="10">
                    <div class="form-hint">0 = tidak boleh ulang</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="ip_restriction">Batasan IP (CIDR, pisahkan koma)</label>
                    <input type="text" name="ip_restriction" id="ip_restriction" class="form-input"
                        value="<?= h(old('ip_restriction', $exam['ip_restriction'])) ?>" placeholder="192.168.1.0/24, 10.0.0.0/8">
                    <div class="form-hint">Kosongkan = tanpa batasan IP</div>
                </div>

                <hr style="margin: 1.5rem 0;">

                <div class="form-group">
                    <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-input form-select" required>
                        <option value="draft" <?= old('status', $exam['status']) === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= old('status', $exam['status']) === 'published' ? 'selected' : '' ?>>Dipublikasikan</option>
                        <option value="ongoing" <?= old('status', $exam['status']) === 'ongoing' ? 'selected' : '' ?>>Berlangsung</option>
                        <option value="completed" <?= old('status', $exam['status']) === 'completed' ? 'selected' : '' ?>>Selesai</option>
                        <option value="archived" <?= old('status', $exam['status']) === 'archived' ? 'selected' : '' ?>>Diarsipkan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="settings">Pengaturan Lanjutan (JSON)</label>
                    <textarea name="settings" id="settings" class="form-input font-mono" rows="4"
                        placeholder='{"allow_calculator": true, "allow_reference": false}'><?= h(old('settings', $exam['settings'])) ?></textarea>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                    <a href="<?= url('admin/exams') ?>" class="btn btn-secondary">Batal</a>
                    <button type="submit" form="examForm" class="btn btn-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate slug from title
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');

    titleInput.addEventListener('input', function() {
        if (!slugInput.dataset.manual) {
            const slug = this.value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            slugInput.value = slug;
        }
    });

    slugInput.addEventListener('input', function() {
        this.dataset.manual = true;
    });

    // Validate end_at > start_at
    const startAt = document.getElementById('start_at');
    const endAt = document.getElementById('end_at');

    startAt.addEventListener('change', function() {
        if (endAt.value && endAt.value <= this.value) {
            endAt.value = '';
        }
        endAt.min = this.value;
    });

    // Initialize endAt min
    endAt.min = startAt.value;
});
</script>