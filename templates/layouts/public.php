<!DOCTYPE html>
<html lang="<?= h(config('app.locale', 'id')) ?>" dir="<?= $rtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= h(csrf_token()) ?>">
    <title><?= $title ?? h(config('app.name')) ?></title>

    <!-- School Config Inline (sebelum CSS) -->
    <script>
      window.SCHOOL_CONFIG = <?= json_encode($schoolConfig ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/design-system.css') ?>">
    <script src="<?= asset('assets/js/theme.js') ?>" defer></script>
    <link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg-primary: #f8fafc;
            --bg-secondary: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --radius-sm: 4px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --transition: 150ms ease;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg-primary); color: var(--text-primary); line-height: 1.6; }
        a { color: var(--primary); text-decoration: none; }
        a:hover { color: var(--primary-hover); }

        .public-layout { display: flex; flex-direction: column; min-height: 100vh; }

        /* Header */
        .public-header {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .header-container { max-width: 1200px; margin: 0 auto; padding: 1rem 1.5rem; display: flex; align-items: center; justify-content: space-between; }
        .header-logo { display: flex; align-items: center; gap: 0.75rem; font-weight: 700; font-size: 1.25rem; color: var(--text-primary); }
        .header-logo-icon { width: 40px; height: 40px; background: var(--primary); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.125rem; }
        .header-nav { display: flex; align-items: center; gap: 2rem; }
        .header-nav a { font-weight: 500; color: var(--text-secondary); font-size: 0.9375rem; transition: color var(--transition); }
        .header-nav a:hover { color: var(--primary); }
        .header-actions { display: flex; align-items: center; gap: 1rem; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; border-radius: var(--radius-md); border: 1px solid transparent; cursor: pointer; transition: all var(--transition); }
        .btn-primary { background: var(--primary); color: white; border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-outline { background: transparent; color: var(--primary); border-color: var(--primary); }
        .btn-outline:hover { background: var(--primary); color: white; }

        /* Hero */
        .hero { padding: 5rem 1.5rem; text-align: center; background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-primary) 100%); }
        .hero-container { max-width: 800px; margin: 0 auto; }
        .hero-badge { display: inline-block; background: rgba(37, 99, 235, 0.1); color: var(--primary); padding: 0.375rem 1rem; border-radius: 9999px; font-size: 0.8125rem; font-weight: 600; margin-bottom: 1.5rem; }
        .hero-title { font-size: 3rem; font-weight: 700; color: var(--text-primary); line-height: 1.1; margin-bottom: 1.5rem; }
        .hero-title span { color: var(--primary); }
        .hero-description { font-size: 1.25rem; color: var(--text-secondary); margin-bottom: 2.5rem; max-width: 600px; margin-left: auto; margin-right: auto; }
        .hero-actions { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
        .btn-lg { padding: 0.875rem 2rem; font-size: 1rem; }

        /* Features */
        .features { padding: 5rem 1.5rem; background: var(--bg-secondary); }
        .features-container { max-width: 1200px; margin: 0 auto; }
        .section-header { text-align: center; margin-bottom: 3rem; }
        .section-title { font-size: 2.25rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1rem; }
        .section-subtitle { font-size: 1.125rem; color: var(--text-secondary); max-width: 600px; margin: 0 auto; }
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }
        .feature-card { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 2rem; transition: all var(--transition); }
        .feature-card:hover { border-color: var(--primary); box-shadow: var(--shadow-md); transform: translateY(-4px); }
        .feature-icon { width: 56px; height: 56px; background: rgba(37, 99, 235, 0.1); color: var(--primary); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem; }
        .feature-title { font-size: 1.25rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.75rem; }
        .feature-description { font-size: 1rem; color: var(--text-secondary); line-height: 1.7; }

        /* Stats */
        .stats { padding: 4rem 1.5rem; background: var(--text-primary); color: white; }
        .stats-container { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; text-align: center; }
        .stat-value { font-size: 3rem; font-weight: 700; font-family: 'JetBrains Mono', monospace; color: var(--primary); margin-bottom: 0.5rem; }
        .stat-label { font-size: 1rem; color: rgba(255,255,255,0.7); }

        /* Footer */
        .public-footer { background: var(--text-primary); color: white; padding: 3rem 1.5rem 1.5rem; }
        .footer-container { max-width: 1200px; margin: 0 auto; }
        .footer-grid { display: grid; grid-template-columns: 2fr repeat(3, 1fr); gap: 3rem; margin-bottom: 2rem; }
        .footer-brand .logo { display: flex; align-items: center; gap: 0.75rem; font-weight: 700; font-size: 1.25rem; margin-bottom: 1rem; }
        .footer-brand .logo-icon { width: 40px; height: 40px; background: var(--primary); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.125rem; }
        .footer-brand p { color: rgba(255,255,255,0.6); font-size: 0.9375rem; line-height: 1.7; max-width: 300px; }
        .footer-column h4 { font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem; color: rgba(255,255,255,0.8); }
        .footer-column ul { list-style: none; }
        .footer-column li { margin-bottom: 0.5rem; }
        .footer-column a { color: rgba(255,255,255,0.6); font-size: 0.9375rem; transition: color var(--transition); }
        .footer-column a:hover { color: var(--primary); }
        .footer-bottom { border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; }
        .footer-copyright { color: rgba(255,255,255,0.4); font-size: 0.875rem; }
        .footer-social { display: flex; gap: 1rem; }
        .footer-social a { color: rgba(255,255,255,0.6); transition: color var(--transition); }
        .footer-social a:hover { color: var(--primary); }

        @media (max-width: 768px) {
            .hero-title { font-size: 2rem; }
            .hero-description { font-size: 1rem; }
            .header-nav { display: none; }
            .footer-grid { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 480px) {
            .footer-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="public-layout">
        <header class="public-header">
            <div class="header-container">
                <a href="<?= url('/') ?>" class="header-logo">
                    <span class="header-logo-icon">CBT</span>
                    <span><?= h(config('app.name')) ?></span>
                </a>
                <nav class="header-nav">
                    <a href="#features">Fitur</a>
                    <a href="#stats">Statistik</a>
                    <a href="<?= url('login') ?>">Masuk</a>
                </nav>
                <div class="header-actions">
                    <a href="<?= url('login') ?>" class="btn btn-outline">Masuk</a>
                    <a href="<?= url('register') ?>" class="btn btn-primary">Daftar</a>
                </div>
            </div>
        </header>

        <main>
            <?= $content ?>
        </main>

        <footer class="public-footer">
            <div class="footer-container">
                <div class="footer-grid">
                    <div class="footer-brand">
                        <div class="logo">
                            <span class="logo-icon">CBT</span>
                            <span><?= h(config('app.name')) ?></span>
                        </div>
                        <p>Platform Computer Based Testing modern untuk sekolah. Kelola ujian, bank soal, dan penilaian dengan mudah dan efisien.</p>
                    </div>
                    <div class="footer-column">
                        <h4>Produk</h4>
                        <ul>
                            <li><a href="#">Bank Soal</a></li>
                            <li><a href="#">Manajemen Ujian</a></li>
                            <li><a href="#">Penilaian Otomatis</a></li>
                            <li><a href="#">Laporan & Analitik</a></li>
                        </ul>
                    </div>
                    <div class="footer-column">
                        <h4>Dukungan</h4>
                        <ul>
                            <li><a href="#">Dokumentasi</a></li>
                            <li><a href="#">Panduan Pengguna</a></li>
                            <li><a href="#">FAQ</a></li>
                            <li><a href="#">Kontak Kami</a></li>
                        </ul>
                    </div>
                    <div class="footer-column">
                        <h4>Legal</h4>
                        <ul>
                            <li><a href="#">Kebijakan Privasi</a></li>
                            <li><a href="#">Syarat Layanan</a></li>
                            <li><a href="#">Keamanan Data</a></li>
                        </ul>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p class="footer-copyright">&copy; <?= date('Y') ?> <?= h(config('app.name')) ?>. Hak cipta dilindungi.</p>
                    <div class="footer-social">
                        <a href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M18.77 7.46H14.5v-1.9c0-.9.6-1.1 1-1.1h3V.5h-4.33C10.24.5 9.5 3.44 9.5 5.32v2.15h-3v4h3v12h5v-12h3.85l.42-4Z"/></svg></a>
                        <a href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.058-1.28.072-1.689.072-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 1.214-1.979 1.99-3.798 1.99-2.815 0-4.844-2.382-4.676-4.751.074-1.07.321-1.556 1.233-1.556h.003v-1.334c0-.755.31-1.087 1.565-1.087.806 0 2.359.145 3.59.955-1.235-.07-2.113-.537-2.113-1.234v-1.053c0-.686.464-.986 1.046-.986.605 0 2.344.324 3.685 1.663.15-.317.36-.531.614-.531.658 0 2.049.215 3.046 1.378-.987 1.13-1.837 1.823-2.207 2.652v1.067c-.003 1.985.706 3.553 2.207 4.752zm-8.24 1.838c.753 0 1.014-.636 1.003-1.005-.008-.599-.814-1.23-2.022-1.451l-.003-.036 1.077-2.492c1.745-.91 2.472-2.354 2.472-3.713 0-2.497-2.105-4.037-4.914-4.037-2.604 0-4.507 1.955-4.869 4.037l1.11 2.554c.703-.062 1.322.159 1.517.634.078.094.098.174.104.294l.008.244-.025.718h-3.009v2.694h3.257Z"/></svg></a>
                        <a href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg></a>
                        <a href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg></a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>