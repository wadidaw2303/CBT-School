<?php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => url('teacher/dashboard')],
    ['label' => 'Penilaian Ujian', 'url' => url('teacher/grading')],
    ['label' => 'Nilai Essay: ' . h($participant['student_name']), 'url' => null]
];

$pendingEssays = array_filter($attempts, fn($a) => in_array($a['question_type'] ?? '', ['essay']) && ($a['is_correct'] ?? null) === null);
$gradedEssays = array_filter($attempts, fn($a) => in_array($a['question_type'] ?? '', ['essay']) && ($a['is_correct'] ?? null) !== null);
$autoGraded = array_filter($attempts, fn($a) => !in_array($a['question_type'] ?? '', ['essay']));
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Penilaian Essay Manual</h1>
        <p class="page-subtitle">
            <?= h($participant['exam_title']) ?> ·
            <?= h($participant['student_name']) ?> (<?= h($participant['student_nis']) ?>) ·
            <?= h($participant['class_name'] ?? '-') ?>
        </p>
    </div>
    <div class="page-actions">
        <a href="<?= url('teacher/grading') ?>" class="btn btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Kembali
        </a>
    </div>
</div>

<!-- Exam Info Cards -->
<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--primary);"><?= count($autoGraded) + count($gradedEssays) ?> / <?= count($attempts) ?></div>
            <div class="text-secondary">Soal Dinilai</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--warning);"><?= count($pendingEssays) ?></div>
            <div class="text-secondary">Essay Menunggu</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--success);">
                <?php 
                $totalAutoScore = array_sum(array_column($autoGraded, 'points_earned'));
                $totalAutoMax = array_sum(array_column($autoGraded, 'points'));
                echo $totalAutoMax > 0 ? round(($totalAutoScore / $totalAutoMax) * 100, 1) : 0;
                ?>%
            </div>
            <div class="text-secondary">Skor Otomatis</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--info);"><?= round(($participant['duration_seconds'] ?? 0) / 60) ?> menit</div>
            <div class="text-secondary">Waktu Pengerjaan</div>
        </div>
    </div>
</div>

<?php if (!empty($pendingEssays)): ?>
<!-- Pending Essay Grading Forms -->
<form method="POST" action="<?= url('teacher/grading/save') ?>" id="gradingForm">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <input type="hidden" name="exam_participant_id" value="<?= $participant['id'] ?>">

    <?php foreach ($pendingEssays as $attempt): 
        $content = json_decode($attempt['question_content'] ?? '{}', true);
        $answer = json_decode($attempt['answer'] ?? '{}', true);
        $answerText = $answer['text'] ?? '';
        $wordCount = $answer['word_count'] ?? 0;
        $charCount = $answer['char_count'] ?? 0;
        $questionText = $content['question_text'] ?? $content['stem'] ?? 'Soal #' . $attempt['question_id'];
        $points = (float)$attempt['points'];
        $existingGrading = $attempt['essay_grading'] ?? null;
    ?>
    <div class="card" style="margin-bottom: 1.5rem; border-left: 4px solid var(--warning);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h3 class="card-title" style="margin: 0;">Soal #<?= $attempt['question_number'] ?> <span class="badge badge-warning">Essay</span></h3>
                <p class="text-secondary" style="margin: 0.5rem 0 0;">Bobot: <?= $points ?> poin</p>
            </div>
        </div>
        <div class="card-body">
            <!-- Question Content -->
            <div style="background: var(--bg-secondary); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <div style="font-weight: 500; margin-bottom: 0.5rem;">Pertanyaan:</div>
                <div style="white-space: pre-wrap;"><?= h($questionText) ?></div>
                <?php if (!empty($content['rubric'])): ?>
                    <div style="margin-top: 1rem; padding: 1rem; background: var(--bg); border-radius: 6px; border: 1px solid var(--border);">
                        <div style="font-weight: 500; margin-bottom: 0.5rem;">Rubrik Penilaian:</div>
                        <div style="white-space: pre-wrap; font-size: 0.875rem;"><?= h($content['rubric']) ?></div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($content['explanation'])): ?>
                    <div style="margin-top: 1rem; padding: 1rem; background: var(--bg); border-radius: 6px; border: 1px solid var(--border); border-left: 3px solid var(--info);">
                        <div style="font-weight: 500; margin-bottom: 0.5rem; color: var(--info);">Kunci Jawaban / Petunjuk:</div>
                        <div style="white-space: pre-wrap; font-size: 0.875rem;"><?= h($content['explanation']) ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Student Answer -->
            <div style="margin-bottom: 1.5rem;">
                <div style="font-weight: 500; margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <span>Jawaban Siswa:</span>
                    <div class="text-sm text-secondary">
                        <?= $wordCount ?> kata · <?= $charCount ?> karakter
                    </div>
                </div>
                <div style="background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 8px; padding: 1rem; min-height: 120px; white-space: pre-wrap; font-family: inherit;">
<?= $answerText ? h($answerText) : '<span class="text-secondary">(Siswa tidak menjawab)</span>' ?>
                </div>
            </div>

            <!-- Grading Input -->
            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div>
                    <label class="label">Skor (0 - <?= $points ?>)</label>
                    <input type="number" name="scores[<?= $attempt['id'] ?>]" class="input" 
                           min="0" max="<?= $points ?>" step="0.1" 
                           value="<?= $existingGrading['score'] ?? '' ?>" required>
                    <input type="hidden" name="max_scores[<?= $attempt['id'] ?>]" value="<?= $points ?>">
                </div>
                <div>
                    <label class="label">Status</label>
                    <select name="statuses[<?= $attempt['id'] ?>]" class="input">
                        <option value="graded" <?= ($existingGrading['status'] ?? '') === 'graded' ? 'selected' : '' ?>>Dinilai</option>
                        <option value="pending" <?= ($existingGrading['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Tunda</option>
                    </select>
                </div>
            </div>

            <div style="margin-top: 1rem;">
                <label class="label">Umpan Balik (Opsional)</label>
                <textarea name="feedbacks[<?= $attempt['id'] ?>]" class="input" rows="3" 
                          placeholder="Tulis umpan balik untuk siswa..."><?= $existingGrading['feedback'] ?? '' ?></textarea>
            </div>

            <!-- Rubric scoring (if defined) -->
            <?php if (!empty($content['rubric_items']) && is_array($content['rubric_items'])): ?>
            <div style="margin-top: 1rem; padding: 1rem; background: var(--bg-secondary); border-radius: 8px;">
                <div style="font-weight: 500; margin-bottom: 0.75rem;">Penilaian Berbasis Rubrik:</div>
                <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
                    <?php foreach ($content['rubric_items'] as $idx => $rubric): 
                        $rubricMax = (float)($rubric['points'] ?? 1);
                        $rubricScore = $existingGrading['rubric_scores'][$idx] ?? '';
                    ?>
                    <div>
                        <label class="label" style="font-size: 0.875rem;"><?= h($rubric['criteria'] ?? 'Kriteria ' . ($idx + 1)) ?> (max <?= $rubricMax ?>)</label>
                        <input type="number" name="rubric_scores[<?= $attempt['id'] ?>][<?= $idx ?>]" class="input" 
                               min="0" max="<?= $rubricMax ?>" step="0.1" value="<?= $rubricScore ?>">
                        <input type="hidden" name="rubric_max[<?= $attempt['id'] ?>][<?= $idx ?>]" value="<?= $rubricMax ?>">
                        <input type="hidden" name="rubric_criteria[<?= $attempt['id'] ?>][<?= $idx ?>]" value="<?= h($rubric['criteria'] ?? '') ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-sm text-secondary" style="margin-top: 0.5rem;">Total rubrik akan divalidasi agar tidak melebihi bobot soal (<?= $points ?> poin).</div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Action Bar -->
    <div class="card">
        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <button type="submit" name="action" value="save_draft" class="btn btn-secondary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Simpan Draf
                </button>
                <button type="submit" name="action" value="save_and_release" class="btn btn-primary" style="margin-left: 0.5rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-8 22 0"/></svg>
                    Simpan & Rilis Nilai
                </button>
            </div>
            <a href="<?= url('teacher/grading') ?>" class="btn btn-ghost">Batal</a>
        </div>
    </div>
</form>
<?php else: ?>
<!-- No pending essays -->
<div class="card">
    <div class="card-body" style="text-align: center; padding: 3rem;">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--success); margin-bottom: 1rem;">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        <h3 style="margin-bottom: 0.5rem;">Semua Essay Sudah Dinilai</h3>
        <p class="text-secondary" style="margin-bottom: 1.5rem;">Tidak ada soal essay yang menunggu penilaian untuk peserta ini.</p>
        <a href="<?= url('teacher/grading/detail/' . $participant['id']) ?>" class="btn btn-primary">Lihat Detail Lengkap</a>
    </div>
</div>
<?php endif; ?>

<!-- Already Graded Essays Summary -->
<?php if (!empty($gradedEssays)): ?>
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Essay Sudah Dinilai</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Soal</th>
                        <th>Skor</th>
                        <th>Status</th>
                        <th>Dinilai Oleh</th>
                        <th>Waktu</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gradedEssays as $attempt): 
                        $grading = $attempt['essay_grading'] ?? [];
                    ?>
                    <tr>
                        <td><?= $attempt['question_number'] ?></td>
                        <td style="max-width: 300px;">
                            <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?= h(strip_tags(json_encode($attempt['question_content']['question_text'] ?? $attempt['question_content']['stem'] ?? 'Soal #' . $attempt['question_id']))) ?>
                            </div>
                        </td>
                        <td class="font-mono font-medium"><?= number_format($grading['score'] ?? 0, 1) ?> / <?= $attempt['points'] ?></td>
                        <td>
                            <span class="badge <?= ($grading['status'] ?? '') === 'graded' ? 'badge-success' : 'badge-warning' ?>">
                                <?= ($grading['status'] ?? '') === 'graded' ? 'Dinilai' : 'Draf' ?>
                            </span>
                        </td>
                        <td><?= h($grading['grader_name'] ?? '-') ?></td>
                        <td><?= $grading['graded_at'] ? format_datetime($grading['graded_at']) : '-' ?></td>
                        <td>
                            <a href="<?= url('teacher/grading/attempt/' . $participant['id'] . '?edit=' . $attempt['id']) ?>" class="btn btn-secondary btn-sm">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Auto-graded Questions Summary -->
<?php if (!empty($autoGraded)): ?>
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Soal Tergradasi Otomatis</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Soal</th>
                        <th>Tipe</th>
                        <th>Jawaban Siswa</th>
                        <th>Jawaban Benar</th>
                        <th>Hasil</th>
                        <th>Bobot</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($autoGraded as $attempt): ?>
                    <tr>
                        <td><?= $attempt['question_number'] ?></td>
                        <td style="max-width: 300px;">
                            <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?= h(strip_tags(json_encode($attempt['question_content']['question_text'] ?? $attempt['question_content']['stem'] ?? 'Soal #' . $attempt['question_id']))) ?>
                            </div>
                        </td>
                        <td><span class="badge badge-primary"><?= h($attempt['question_type'] ?? '-') ?></span></td>
                        <td>
                            <div class="font-mono text-sm" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php
                                $ans = json_decode($attempt['answer'] ?? '{}', true);
                                if (is_array($ans)) {
                                    if (isset($ans['selected'])) echo 'Opsi: ' . implode(', ', $ans['selected']);
                                    elseif (isset($ans['blanks'])) echo 'Isian: ' . implode(' | ', $ans['blanks']);
                                    elseif (isset($ans['matches'])) echo 'Cocokkan: ' . count($ans['matches']) . ' pasangan';
                                    elseif (isset($ans['order'])) echo 'Urutan: ' . implode(' → ', $ans['order']);
                                    else echo json_encode($ans);
                                }
                                ?>
                            </div>
                        </td>
                        <td>
                            <div class="font-mono text-sm" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php
                                $correct = json_decode($attempt['correct_answer'] ?? '{}', true);
                                if (is_array($correct)) {
                                    if (isset($correct['selected'])) echo 'Opsi: ' . implode(', ', $correct['selected']);
                                    elseif (isset($correct['blanks'])) echo 'Isian: ' . implode(' | ', $correct['blanks']);
                                    elseif (isset($correct['matches'])) echo 'Cocokkan: ' . count($correct['matches']) . ' pasangan';
                                    elseif (isset($correct['order'])) echo 'Urutan: ' . implode(' → ', $correct['order']);
                                    else echo json_encode($correct);
                                }
                                ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge <?= ($attempt['is_correct'] ?? false) ? 'badge-success' : 'badge-danger' ?>">
                                <?= ($attempt['is_correct'] ?? false) ? 'Benar' : 'Salah' ?>
                            </span>
                        </td>
                        <td class="text-center font-mono"><?= $attempt['points_earned'] ?? 0 ?> / <?= $attempt['points'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('gradingForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        const action = e.submitter?.value || '';
        if (action === 'save_and_release') {
            if (!confirm('Yakin ingin menyimpan dan merilis nilai untuk peserta ini?')) {
                e.preventDefault();
            }
        }
    });

    // Validate rubric totals on input
    document.querySelectorAll('input[name^="rubric_scores"]').forEach(input => {
        input.addEventListener('change', function() {
            const attemptId = this.name.match(/rubric_scores\[(\d+)\]/);
            if (!attemptId) return;
            attemptId = attemptId[1];
            
            let total = 0;
            document.querySelectorAll(`input[name^="rubric_scores[${attemptId}]"]`).forEach(inp => {
                total += parseFloat(inp.value) || 0;
            });
            
            const maxScoreInput = document.querySelector(`input[name="max_scores[${attemptId}]"]`);
            const maxScore = maxScoreInput ? parseFloat(maxScoreInput.value) : 0;
            
            if (total > maxScore) {
                this.style.borderColor = 'var(--danger)';
                this.setCustomValidity(`Total rubrik (${total}) melebihi bobot soal (${maxScore})`);
            } else {
                this.style.borderColor = '';
                this.setCustomValidity('');
            }
        });
    });
});
</script>