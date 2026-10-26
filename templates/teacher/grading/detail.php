<?php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => url('teacher/dashboard')],
    ['label' => 'Penilaian Ujian', 'url' => url('teacher/grading')],
    ['label' => 'Detail: ' . h($participant['student_name']), 'url' => null]
];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Detail Hasil Ujian</h1>
        <p class="page-subtitle">
            <?= h($participant['exam_title']) ?> ·
            <?= h($participant['student_name']) ?> (<?= h($participant['student_nis']) ?>) ·
            <?= h($participant['class_name'] ?? '-') ?>
        </p>
    </div>
    <div class="page-actions">
        <?php if (($participant['pending_essay_count'] ?? 0) > 0): ?>
            <a href="<?= url('teacher/grading/attempt/' . $participant['id']) ?>" class="btn btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Nilai Essay
            </a>
        <?php endif; ?>
        <a href="<?= url('teacher/grading') ?>" class="btn btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Kembali
        </a>
    </div>
</div>

<!-- Participant Info Cards -->
<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary);">
                <?= $participant['percentage'] !== null ? number_format($participant['percentage'], 2) : '-' ?>%
            </div>
            <div class="text-secondary">Skor Akhir</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <div style="font-size: 2rem; font-weight: 700; color: <?= ($participant['is_passed'] ?? false) ? 'var(--success)' : 'var(--danger)' ?>">
                <?= ($participant['is_passed'] ?? false) ? 'LULUS' : 'TIDAK LULUS' ?>
            </div>
            <div class="text-secondary">Kelulusan (<?= $participant['passing_score'] ?? 70 ?>%)</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--info);">
                <?= $participant['grade'] ?? '-' ?>
            </div>
            <div class="text-secondary">Grade</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--warning);">
                <?= round(($participant['duration_seconds'] ?? 0) / 60) ?> menit
            </div>
            <div class="text-secondary">Waktu Pengerjaan</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <div style="font-size: 1.5rem; font-weight: 700;">
                <?= $participant['score'] ?? 0 ?> / <?= $participant['max_score'] ?? 0 ?>
            </div>
            <div class="text-secondary">Poin</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <div style="font-size: 1.5rem; font-weight: 700;">
                <span class="badge <?= match($participant['status']) {
                    'not_started' => 'badge-secondary',
                    'in_progress' => 'badge-info',
                    'submitted' => 'badge-warning',
                    'graded' => 'badge-success',
                    default => 'badge-secondary'
                } ?>">
                    <?= match($participant['status']) {
                        'not_started' => 'Belum Mulai',
                        'in_progress' => 'Sedang Ujian',
                        'submitted' => 'Diserahkan',
                        'graded' => 'Dinilai',
                        default => $participant['status']
                    } ?>
                </span>
            </div>
            <div class="text-secondary">Status</div>
        </div>
    </div>
</div>

<!-- Question Breakdown -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Rincian Per Soal</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($attempts)): ?>
            <div class="empty-state" style="padding: 3rem; text-align: center;">
                <p class="text-secondary">Belum ada data jawaban.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Soal</th>
                            <th>Tipe</th>
                            <th>Jawaban Siswa</th>
                            <th>Jawaban Benar</th>
                            <th style="width: 100px;">Hasil</th>
                            <th style="width: 100px;">Skor</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attempts as $attempt): 
                            $isEssay = in_array($attempt['question_type'] ?? '', ['essay']);
                            $isGraded = ($attempt['is_correct'] ?? null) !== null;
                        ?>
                        <tr>
                            <td><?= $attempt['question_number'] ?></td>
                            <td style="max-width: 350px;">
                                <div style="font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= h(strip_tags(json_encode($attempt['question_content']['question_text'] ?? $attempt['question_content']['stem'] ?? 'Soal #' . $attempt['question_id']))) ?>
                                </div>
                                <?php if ($isEssay && !empty($attempt['question_content']['rubric'])): ?>
                                    <button type="button" class="btn btn-ghost btn-sm" style="margin-top: 0.25rem; padding: 0.25rem 0.5rem;" 
                                            onclick="showRubric(<?= htmlspecialchars(json_encode($attempt['question_content']['rubric']), ENT_QUOTES) ?>, '<?= h($attempt['question_content']['rubric_items'] ? json_encode($attempt['question_content']['rubric_items']) : '[]') ?>')">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                                        Lihat Rubrik
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-primary"><?= h($attempt['question_type'] ?? '-') ?></span>
                            </td>
                            <td>
                                <div class="font-mono text-sm" style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= h(json_encode($attempt['answer'] ?? [])) ?>">
                                    <?php
                                    $ans = json_decode($attempt['answer'] ?? '{}', true);
                                    if (is_array($ans)) {
                                        if (isset($ans['selected'])) echo 'Opsi: ' . implode(', ', $ans['selected']);
                                        elseif (isset($ans['text'])) echo 'Teks: ' . mb_substr($ans['text'], 0, 100) . (mb_strlen($ans['text']) > 100 ? '...' : '');
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
                                <div class="font-mono text-sm" style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= h(json_encode($attempt['correct_answer'] ?? [])) ?>">
                                    <?php
                                    $correct = json_decode($attempt['correct_answer'] ?? '{}', true);
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
                                <?php if ($attempt['is_correct'] === null): ?>
                                    <span class="badge badge-warning">Menunggu</span>
                                <?php elseif ($attempt['is_correct']): ?>
                                    <span class="badge badge-success">Benar</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Salah</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center font-mono">
                                <?= $attempt['points_earned'] ?? 0 ?> / <?= $attempt['points'] ?>
                            </td>
                            <td>
                                <?php if ($isEssay && !$isGraded): ?>
                                    <a href="<?= url('teacher/grading/attempt/' . $participant['id']) ?>" class="btn btn-primary btn-sm">Nilai</a>
                                <?php elseif ($isEssay && $isGraded): ?>
                                    <a href="<?= url('teacher/grading/attempt/' . $participant['id'] . '?edit=' . $attempt['id']) ?>" class="btn btn-secondary btn-sm">Edit</a>
                                <?php else: ?>
                                    <span class="text-secondary">Otomatis</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Essay Feedback Section -->
<?php 
$essayDetails = array_filter($attempts, fn($a) => in_array($a['question_type'] ?? '', ['essay']));
if (!empty($essayDetails)): 
?>
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Umpan Balik Essay</h3>
    </div>
    <div class="card-body">
        <?php foreach ($essayDetails as $detail): 
            $grading = $detail['essay_grading'] ?? null;
            $answer = json_decode($detail['answer'] ?? '{}', true);
            $answerText = $answer['text'] ?? '';
        ?>
        <div style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--border);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <div style="font-weight: 500; margin-bottom: 0.5rem;">
                        Soal #<?= $detail['question_number'] ?>: 
                        <?= h(strip_tags(json_encode($detail['question_content']['question_text'] ?? $detail['question_content']['stem'] ?? ''))) ?>
                    </div>
                    <div class="text-sm text-secondary">
                        Bobot: <?= $detail['points'] ?> poin · 
                        Status: <span class="badge <?= $grading && $grading['status'] === 'graded' ? 'badge-success' : 'badge-warning' ?>">
                            <?= $grading && $grading['status'] === 'graded' ? 'Dinilai' : 'Draf' ?>
                        </span>
                    </div>
                </div>
                <?php if ($grading): ?>
                    <div class="text-right" style="text-align: right;">
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">
                            <?= number_format($grading['score'] ?? 0, 1) ?> / <?= $detail['points'] ?>
                        </div>
                        <div class="text-sm text-secondary">Dinilai: <?= $grading['graded_at'] ? format_datetime($grading['graded_at']) : '-' ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div>
                    <div style="font-weight: 500; margin-bottom: 0.5rem;">Jawaban Siswa:</div>
                    <div style="background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 8px; padding: 1rem; min-height: 100px; white-space: pre-wrap;">
<?= $answerText ? h($answerText) : '<span class="text-secondary">(Siswa tidak menjawab)</span>' ?>
                    </div>
                </div>
                <div>
                    <div style="font-weight: 500; margin-bottom: 0.5rem;">Umpan Balik Guru:</div>
                    <?php if ($grading && !empty($grading['feedback'])): ?>
                        <div style="background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 8px; padding: 1rem; min-height: 100px; white-space: pre-wrap; border-left: 3px solid var(--primary);">
<?= nl2br(h($grading['feedback'])) ?>
                        </div>
                    <?php else: ?>
                        <div style="background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 8px; padding: 1rem; min-height: 100px; color: var(--text-tertiary);">
                            Belum ada umpan balik
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($grading && !empty($grading['rubric_scores'])): ?>
            <div style="margin-top: 1rem; padding: 1rem; background: var(--bg-secondary); border-radius: 8px;">
                <div style="font-weight: 500; margin-bottom: 0.75rem;">Detail Rubrik:</div>
                <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
                    <?php 
                    $rubricItems = json_decode($detail['question_content']['rubric_items'] ?? '[]', true);
                    foreach ($grading['rubric_scores'] as $idx => $score): 
                        $max = $grading['rubric_max'][$idx] ?? 1;
                        $criteria = $rubricItems[$idx]['criteria'] ?? 'Kriteria ' . ($idx + 1);
                    ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem; background: var(--bg); border-radius: 6px;">
                        <span class="text-sm"><?= h($criteria) ?></span>
                        <span class="font-mono font-medium"><?= number_format($score, 1) ?> / <?= $max ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Rubric Modal -->
<div id="rubricModal" class="modal" style="display: none;" role="dialog" aria-labelledby="rubricTitle" aria-modal="true">
    <div class="modal-backdrop" onclick="closeRubric()"></div>
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 id="rubricTitle" class="modal-title">Rubrik Penilaian</h3>
            <button class="modal-close" onclick="closeRubric()">&times;</button>
        </div>
        <div class="modal-body" id="rubricContent"></div>
    </div>
</div>

<script>
function showRubric(rubric, rubricItems) {
    const modal = document.getElementById('rubricModal');
    const content = document.getElementById('rubricContent');
    
    let html = '<div style="white-space: pre-wrap; margin-bottom: 1rem;">' + escapeHtml(rubric) + '</div>';
    
    if (rubricItems && rubricItems.length > 0) {
        html += '<div style="font-weight: 500; margin-bottom: 0.75rem;">Item Rubrik:</div>';
        html += '<div style="display: grid; gap: 0.5rem;">';
        rubricItems.forEach((item, idx) => {
            html += '<div style="padding: 0.75rem; background: var(--bg-secondary); border-radius: 6px;">';
            html += '<div style="font-weight: 500;">' + escapeHtml(item.criteria) + ' <span class="text-secondary">(' + (item.points || 1) + ' poin)</span></div>';
            if (item.description) {
                html += '<div class="text-sm text-secondary" style="margin-top: 0.25rem;">' + escapeHtml(item.description) + '</div>';
            }
            html += '</div>';
        });
        html += '</div>';
    }
    
    content.innerHTML = html;
    modal.style.display = 'flex';
}

function closeRubric() {
    document.getElementById('rubricModal').style.display = 'none';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeRubric();
});
</script>