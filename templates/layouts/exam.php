<?php
/**
 * Exam Layout - Fullscreen exam taking interface
 * Renders all questions as hidden pages, switches via JS
 * Includes question partials for each question type
 */

$exam = $exam ?? (object)[];
$participant = $participant ?? (object)[];
$questionPages = $questionPages ?? [];
$totalQuestions = $totalQuestions ?? 0;
$examConfig = $examConfig ?? [];
$schoolConfig = $schoolConfig ?? [];
$rtl = $rtl ?? false;

$examId = $exam->id ?? '';
$participantId = $participant->id ?? '';
$durationMinutes = $exam->duration_minutes ?? 60;
$examTitle = $exam->title ?? 'Ujian';

$showPoints = $examConfig['show_points'] ?? true;
$allowFlagging = $examConfig['allow_flagging'] ?? true;
$shuffleQuestions = $examConfig['shuffle_questions'] ?? false;
$shuffleOptions = $examConfig['shuffle_options'] ?? false;

$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$apiBase = $baseUrl . '/api';
?>
<!DOCTYPE html>
<html lang="id" <?= $rtl ? 'dir="rtl"' : '' ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <title><?= htmlspecialchars($examTitle) ?> - CBT School</title>
    
    <!-- Prevent caching for exam security -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- CSP for exam security -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self' https:;">
    
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/design-system.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/app.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/exam.css">
    
    <style>
        /* Exam-specific inline styles for print/fullscreen */
        @media print {
            .exam-header, .exam-footer, .question-palette, .navigation-controls, .fullscreen-warning { display: none !important; }
            .question-page { display: block !important; page-break-after: always; }
            .exam-container { max-width: none; padding: 0; }
        }
        
        .exam-fullscreen { position: fixed; inset: 0; z-index: 9999; background: #fff; }
        .exam-fullscreen .exam-header { display: none; }
        .exam-fullscreen .question-page { display: block !important; }
    </style>
</head>
<body class="exam-body">
    <!-- Fullscreen Enter Overlay -->
    <div id="fullscreenOverlay" class="fullscreen-overlay" role="dialog" aria-modal="true" aria-labelledby="fs-title">
        <div class="fullscreen-overlay__content">
            <h2 id="fs-title">Mode Layar Penuh</h2>
            <p>Ujian ini memerlukan mode layar penuh untuk keamanan. Keluar dari mode layar penuh akan menghitung sebagai pelanggaran.</p>
            <button id="enterFullscreenBtn" class="btn btn--primary btn--lg">Masukkan Layar Penuh & Mulai Ujian</button>
        </div>
    </div>

    <!-- Fullscreen Exit Warning -->
    <div id="fullscreenWarning" class="fullscreen-warning" hidden role="alert" aria-live="assertive">
        <div class="fullscreen-warning__content">
            <div class="fullscreen-warning__icon" aria-hidden="true">⚠</div>
            <h3>Peringatan: Keluar Layar Penuh</h3>
            <p>Anda telah keluar dari mode layar penuh. <span id="warningCount">0</span>/3 pelanggaran tercatat.</p>
            <p class="fullscreen-warning__note">Keluar lebih dari 3 kali akan mengakhiri ujian secara otomatis.</p>
            <button id="enterFullscreenBtn" class="btn btn--primary">Kembali ke Layar Penuh</button>
        </div>
    </div>

    <!-- Exam Container -->
    <div class="exam-container" id="examContainer">
        <!-- Header -->
        <header class="exam-header" role="banner">
            <div class="exam-header__left">
                <h1 class="exam-header__title"><?= htmlspecialchars($examTitle) ?></h1>
                <span class="exam-header__meta" id="examMeta"><?= htmlspecialchars($participant->name ?? 'Peserta') ?> | <?= htmlspecialchars($participant->access_code ?? $participantId) ?></span>
            </div>
            <div class="exam-header__center">
                <div class="timer-display" role="timer" aria-live="polite" aria-label="Waktu tersisa">
                    <svg class="timer-display__icon" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
                        <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span id="timerDisplay" class="timer-display__time">00:00:00</span>
                    <div class="timer-display__progress" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                        <div id="timerProgressBar" class="timer-display__progress-bar"></div>
                    </div>
                </div>
                <div class="connection-status" id="connectionStatus" aria-live="polite">
                    <span class="connection-status__indicator" aria-hidden="true"></span>
                    <span id="connectionText" class="connection-status__text">Terhubung</span>
                </div>
            </div>
            <div class="exam-header__right">
                <button id="flagBtn" class="btn btn--ghost btn--icon-only" aria-pressed="false" aria-label="Tandai soal ini" <?= !$allowFlagging ? 'hidden' : '' ?>>
                    <svg class="btn__icon" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                        <path d="M18.39 3.39a3.7 3.7 0 0 1 5.24 0l3.47 3.47a3.7 3.7 0 0 1 0 5.24L14.48 18.48a3.7 3.7 0 0 1-5.24 0l-3.47-3.47a3.7 3.7 0 0 1 0-5.24L18.39 3.39z"/>
                    </svg>
                    <span>Tandai</span>
                </button>
                <button id="paletteToggleBtn" class="btn btn--ghost btn--icon-only" aria-expanded="false" aria-controls="questionPalette" aria-label="Buka panel navigasi soal">
                    <svg class="btn__icon" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    <span class="visually-hidden">Panel Soal</span>
                </button>
            </div>
        </header>

        <!-- Question Palette (Sidebar) -->
        <aside id="questionPalette" class="question-palette" role="complementary" aria-label="Navigasi soal">
            <div class="question-palette__header">
                <h2>Navigasi Soal</h2>
                <button id="paletteCloseBtn" class="btn btn--ghost btn--icon-only" aria-label="Tutup panel">
                    <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="question-palette__stats" role="status" aria-live="polite">
                <div class="stat">
                    <span id="statAnswered" class="stat__value">0</span>
                    <span class="stat__label">Dijawab</span>
                </div>
                <div class="stat">
                    <span id="statFlagged" class="stat__value">0</span>
                    <span class="stat__label">Ditandai</span>
                </div>
                <div class="stat">
                    <span id="statUnanswered" class="stat__value"><?= $totalQuestions ?></span>
                    <span class="stat__label">Belum</span>
                </div>
            </div>
            <ul id="paletteList" class="question-palette__list" role="listbox" aria-label="Daftar soal"></ul>
            <button id="goToFirstUnansweredBtn" class="btn btn--secondary btn--block" type="button">Ke Soal Belum Dijawab Pertama</button>
        </aside>

        <!-- Palette Mobile Toggle -->
        <button id="paletteToggleMobile" class="palette-toggle-mobile" aria-expanded="false" aria-controls="questionPalette" aria-label="Buka navigasi soal">
            <svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                <rect x="3" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/>
                <rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            <span class="palette-toggle-mobile__badge" aria-hidden="true"></span>
        </button>

        <!-- Main Question Area -->
        <main class="exam-main" role="main">
            <div class="exam-question-area" id="questionArea">
                <?php foreach ($questionPages as $page): 
                    $index = $page['index'];
                    $type = $page['type'];
                    $question = $page['question'];
                    $answer = $page['answer'];
                    $config = $page['exam'];
                    $mode = $page['mode'] ?? 'exam';
                    
                    // Prepare variables for partial
                    $partialVars = [
                        'examId' => $examId,
                        'question' => $question,
                        'errors' => [],
                        'old' => $answer ? ['answer' => $answer] : [],
                        'exam' => [
                            'shuffle_options' => $config['shuffle_options'] ?? false,
                            'show_points' => $config['show_points'] ?? true,
                            'allow_flagging' => $config['allow_flagging'] ?? true,
                        ],
                        'index' => $index,
                        'answer' => $answer,
                        'mode' => 'exam', // tell partial this is exam mode (not create/edit)
                    ];
                    
                    // Extract to local scope for include
                    extract($partialVars);
                ?>
                <section 
                    class="question-page" 
                    data-question-index="<?= $index ?>"
                    data-question-id="<?= htmlspecialchars($question['id']) ?>"
                    data-question-type="<?= htmlspecialchars($type) ?>"
                    <?= $index !== 0 ? 'hidden' : '' ?>
                    role="region"
                    aria-label="Soal <?= $index + 1 ?>"
                    aria-labelledby="questionTitle-<?= $index ?>">
                    
                    <!-- Question Header -->
                    <header class="question-page__header">
                        <div class="question-page__meta">
                            <span id="questionTitle-<?= $index ?>" class="question-page__number">Soal <?= $index + 1 ?> dari <?= $totalQuestions ?></span>
                            <?php if ($showPoints): ?>
                            <span class="question-page__points" data-points="<?= $question['points'] ?? 1 ?>">
                                <?= $question['points'] ?? 1 ?> poin
                            </span>
                            <?php endif; ?>
                            <span class="question-page__type" data-type="<?= htmlspecialchars($type) ?>">
                                <?= $typeLabels[$type] ?? ucfirst($type) ?>
                            </span>
                        </div>
                    </header>
                    
                    <!-- Question Content via Partial -->
                    <div class="question-page__content">
                        <?php
                            $partialPath = "partials/questions/{$type}/render.php";
                            $fullPath = __DIR__ . "/../{$partialPath}";
                            if (file_exists($fullPath)) {
                                include $fullPath;
                            } else {
                                // Fallback for unknown types
                                echo '<div class="alert alert--error">Tipe soal tidak dikenal: ' . htmlspecialchars($type) . '</div>';
                            }
                        ?>
                    </div>
                    
                    <!-- Question Footer -->
                    <footer class="question-page__footer">
                        <?php if (!empty($question['explanation'])): ?>
                        <details class="question-explanation">
                            <summary>Lihat Penjelasan</summary>
                            <div class="question-explanation__content"><?= $question['explanation'] ?></div>
                        </details>
                        <?php endif; ?>
                    </footer>
                </section>
                <?php endforeach; ?>
            </div>
            
            <!-- Navigation Controls -->
            <nav class="exam-footer navigation-controls" aria-label="Navigasi ujian">
                <button id="prevBtn" class="btn btn--secondary" type="button" aria-label="Soal sebelumnya">
                    <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                    <span>Sebelumnya</span>
                </button>
                <div class="navigation-controls__progress">
                    <span id="currentQuestionNum">1</span> / <span id="totalQuestions"><?= $totalQuestions ?></span>
                </div>
                <button id="nextBtn" class="btn btn--primary" type="button" aria-label="Soal selanjutnya">
                    <span>Selanjutnya</span>
                    <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                </button>
            </nav>
        </main>
    </div>

    <!-- Submit Confirmation Modal -->
    <div id="submitModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="submitModalTitle" hidden>
        <div class="modal__overlay" tabindex="-1"></div>
        <div class="modal__content">
            <header class="modal__header">
                <h2 id="submitModalTitle">Konfirmasi Pengumpulan</h2>
                <button class="modal__close" aria-label="Tutup">&times;</button>
            </header>
            <div class="modal__body">
                <p>Apakah Anda yakin ingin mengumpulkan ujian? Anda tidak dapat mengubah jawaban setelah mengumpulkan.</p>
                <div class="submit-summary" role="status" aria-live="polite">
                    <div class="submit-summary__item">
                        <span class="submit-summary__label">Dijawab</span>
                        <span id="modalAnswered" class="submit-summary__value">0</span>
                    </div>
                    <div class="submit-summary__item submit-summary__item--warning">
                        <span class="submit-summary__label">Belum Dijawab</span>
                        <span id="modalUnanswered" class="submit-summary__value"><?= $totalQuestions ?></span>
                    </div>
                    <div class="submit-summary__item submit-summary__item--flagged">
                        <span class="submit-summary__label">Ditandai</span>
                        <span id="modalFlagged" class="submit-summary__value">0</span>
                    </div>
                </div>
            </div>
            <footer class="modal__footer">
                <button id="cancelSubmitBtn" class="btn btn--secondary" type="button">Batal</button>
                <button id="confirmSubmitBtn" class="btn btn--danger" type="button">Kumpulkan Ujian</button>
            </footer>
        </div>
    </div>

    <!-- Time Up Modal -->
    <div id="timeUpModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="timeUpModalTitle" hidden>
        <div class="modal__overlay" tabindex="-1"></div>
        <div class="modal__content">
            <header class="modal__header">
                <h2 id="timeUpModalTitle">Waktu Habis</h2>
            </header>
            <div class="modal__body">
                <div class="time-up__icon" aria-hidden="true">⏱</div>
                <p>Waktu ujian telah habis. Jawaban Anda akan dikumpulkan secara otomatis.</p>
            </div>
            <footer class="modal__footer">
                <button id="timeUpOkBtn" class="btn btn--primary btn--block" type="button">Kumpulkan Sekarang</button>
            </footer>
        </div>
    </div>

    <!-- Exam Configuration for JS -->
    <script id="examConfig" type="application/json"><?= json_encode([
        'examId' => $examId,
        'participantId' => $participantId,
        'durationMinutes' => $durationMinutes,
        'totalQuestions' => $totalQuestions,
        'shuffleQuestions' => $shuffleQuestions ? 'true' : 'false',
        'shuffleOptions' => $shuffleOptions ? 'true' : 'false',
        'apiBase' => $apiBase,
        'questions' => array_map(fn($p) => [
            'id' => $p['question']['id'],
            'type' => $p['type'],
            'points' => $p['question']['points'] ?? 1,
        ], $questionPages)
    ]) ?></script>

    <!-- Scripts -->
    <script src="<?= $baseUrl ?>/assets/js/theme.js"></script>
    <script src="<?= $baseUrl ?>/assets/js/exam.js"></script>
    <script>
        // Initialize exam app when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const config = JSON.parse(document.getElementById('examConfig').textContent);
            window.examApp = new ExamApp(config);
        });
    </script>
</body>
</html>