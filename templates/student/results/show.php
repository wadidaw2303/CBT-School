<?php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => url('student/dashboard')],
    ['label' => 'Hasil Ujian', 'url' => url('student/results')],
    ['label' => $attempt['exam_title'] ?? 'Detail', 'url' => null]
];

$score = $attempt['score'] ?? 0;
$passed = $score >= ($attempt['passing_score'] ?? 70);
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= h($attempt['exam_title'] ?? 'Detail Hasil') ?></h1>
        <p class="page-subtitle">
            <?= h($attempt['subject_name'] ?? '-') ?> ·
            <?= format_datetime($attempt['started_at']) ?> ·
            Durasi: <?= round(($attempt['duration_seconds'] ?? 0) / 60) ?> menit
        </p>
    </div>
    <div class="page-actions">
        <a href="<?= url('student/results') ?>" class="btn btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Kembali
        </a>
    </div>
</div>

<!-- Score Card -->
<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
    <div class="card" style="text-align: center; background: linear-gradient(135deg, <?= $passed ? 'var(--success)' : 'var(--danger)' ?>, var(--primary)); color: white;">
        <div class="card-body" style="padding: 2rem;">
            <div style="font-size: 1rem; opacity: 0.9; margin-bottom: 0.5rem;">SKOR AKHIR</div>
            <div style="font-size: 4rem; font-weight: 700; line-height: 1;"><?= number_format($score, 2) ?>%</div>
            <div style="margin-top: 1rem; font-size: 1.25rem; font-weight: 600;">
                <?= $passed ? 'LULUS' : 'TIDAK LULUS' ?>
            </div>
            <div style="margin-top: 0.5rem; opacity: 0.8; font-size: 0.875rem;">
                Nilai Kelulusan: <?= $attempt['passing_score'] ?? 70 ?>%
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Grade</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--primary);"><?= $attempt['grade'] ?? '-' ?></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Benar / Total</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--success);"><?= (int)$attempt['correct_count'] ?> / <?= (int)$attempt['total_questions'] ?></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Waktu Pengerjaan</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--info);">
                <?= round(($attempt['duration_seconds'] ?? 0) / 60) ?> menit
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Mulai</div>
            <div style="font-size: 1rem; font-weight: 600;"><?= format_datetime($attempt['started_at']) ?></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Selesai</div>
            <div style="font-size: 1rem; font-weight: 600;"><?= format_datetime($attempt['completed_at']) ?></div>
        </div>
    </div>
</div>

<!-- Question Breakdown -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Rincian Soal</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Soal</th>
                        <th>Tipe</th>
                        <th>Jawaban Anda</th>
                        <th>Jawaban Benar</th>
                        <th style="width: 80px;">Hasil</th>
                        <th style="width: 80px;">Bobot</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($details as $index => $detail): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td>
                            <div style="max-width: 400px;">
                                <div style="font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= h(strip_tags(json_encode($detail['question_content']['question_text'] ?? $detail['question_content']['stem'] ?? 'Soal #' . $detail['question_id']))) ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-primary"><?= h($detail['question_type'] ?? '-') ?></span>
                        </td>
                        <td>
                            <div class="font-mono text-sm" style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= h(json_encode($detail['answer'] ?? [])) ?>">
                                <?php
                                $ans = $detail['answer'] ?? [];
                                if (is_array($ans)) {
                                    if (isset($ans['selected'])) echo 'Opsi: ' . implode(', ', $ans['selected']);
                                    elseif (isset($ans['text'])) echo 'Teks: ' . $ans['text'];
                                    elseif (isset($ans['blanks'])) echo 'Isian: ' . implode(' | ', $ans['blanks']);
                                    elseif (isset($ans['matches'])) echo 'Cocokkan: ' . count($ans['matches']) . ' pasangan';
                                    elseif (isset($ans['order'])) echo 'Urutan: ' . implode(' → ', $ans['order']);
                                    else echo json_encode($ans);
                                } else {
                                    echo h($ans);
                                }
                                ?>
                            </div>
                        </td>
                        <td>
                            <div class="font-mono text-sm" style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= h(json_encode($detail['correct_answer'] ?? [])) ?>">
                                <?php
                                $correct = $detail['correct_answer'] ?? [];
                                if (is_array($correct)) {
                                    if (isset($correct['selected'])) echo 'Opsi: ' . implode(', ', $correct['selected']);
                                    elseif (isset($correct['text'])) echo 'Teks: ' . $correct['text'];
                                    elseif (isset($correct['blanks'])) echo 'Isian: ' . implode(' | ', $correct['blanks']);
                                    elseif (isset($correct['matches'])) echo 'Cocokkan: ' . count($correct['matches']) . ' pasangan';
                                    elseif (isset($correct['order'])) echo 'Urutan: ' . implode(' → ', $correct['order']);
                                    else echo json_encode($correct);
                                } else {
                                    echo h($correct);
                                }
                                ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <?php
                            $isCorrect = $detail['is_correct'] ?? false;
                            ?>
                            <span class="badge <?= $isCorrect ? 'badge-success' : 'badge-danger' ?>">
                                <?= $isCorrect ? 'Benar' : 'Salah' ?>
                            </span>
                        </td>
                        <td class="text-center font-mono"><?= $detail['points'] ?? 1 ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Essay Feedback (if any) -->
    <?php
    $essayDetails = array_filter($details, fn($d) => in_array($d['question_type'] ?? '', ['essay', 'fill_blank', 'matching', 'ordering']));
    if (!empty($essayDetails)):
    ?>
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Umpan Balik Guru</h3>
        </div>
        <div class="card-body">
            <?php foreach ($essayDetails as $detail): ?>
                <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border);">
                    <div style="font-weight: 500; margin-bottom: 0.5rem;">
                        Soal #<?= $detail['question_id'] ?>: <?= h(strip_tags(json_encode($detail['question_content']['question_text'] ?? $detail['question_content']['stem'] ?? ''))) ?>
                    </div>
                    <?php if (!empty($detail['teacher_feedback'])): ?>
                        <div style="background: var(--bg-secondary); padding: 1rem; border-radius: 8px; border-left: 3px solid var(--primary);">
                            <div class="text-sm text-secondary" style="margin-bottom: 0.5rem;">Umpan Balik:</div>
                            <div><?= nl2br(h($detail['teacher_feedback'])) ?></div>
                        </div>
                    <?php else: ?>
                        <div class="text-secondary text-sm">Belum ada umpan balik dari guru</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>