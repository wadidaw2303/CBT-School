<!DOCTYPE html>
<html lang="<?= h(config('app.locale', 'id')) ?>" dir="<?= $rtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= h(csrf_token()) ?>">
    <title><?= $title ?? 'Login' ?> - <?= h(config('app.name')) ?></title>

    <!-- School Config Inline (sebelum CSS) -->
    <script>
      window.SCHOOL_CONFIG = <?= json_encode($schoolConfig ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/design-system.css') ?>">
    <script src="<?= asset('assets/js/theme.js') ?>" defer></script>
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
        body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg-primary); color: var(--text-primary); line-height: 1.5; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem; }
        a { color: var(--primary); text-decoration: none; }
        a:hover { color: var(--primary-hover); }

        .auth-container { width: 100%; max-width: 420px; }
        .auth-card { background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); padding: 2.5rem; }
        .auth-header { text-align: center; margin-bottom: 2rem; }
        .auth-logo { width: 64px; height: 64px; background: var(--primary); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.5rem; margin: 0 auto 1rem; }
        .auth-title { font-size: 1.5rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.5rem; }
        .auth-subtitle { font-size: 0.875rem; color: var(--text-secondary); }

        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; font-size: 0.8125rem; font-weight: 500; color: var(--text-primary); margin-bottom: 0.5rem; }
        .form-input { width: 100%; padding: 0.75rem 1rem; font-size: 0.9375rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--bg-secondary); color: var(--text-primary); transition: all var(--transition); }
        .form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .form-input::placeholder { color: var(--text-secondary); }
        .form-input.error { border-color: var(--danger); }
        .form-error { font-size: 0.75rem; color: var(--danger); margin-top: 0.375rem; display: flex; align-items: center; gap: 0.25rem; }
        .form-row { display: flex; align-items: center; justify-content: space-between; margin-top: 0.5rem; }
        .form-check { display: flex; align-items: center; gap: 0.5rem; }
        .form-check-input { width: 18px; height: 18px; accent-color: var(--primary); cursor: pointer; }
        .form-check-label { font-size: 0.875rem; color: var(--text-primary); cursor: pointer; }
        .forgot-link { font-size: 0.875rem; color: var(--primary); font-weight: 500; }
        .forgot-link:hover { text-decoration: underline; }

        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; width: 100%; padding: 0.75rem 1rem; font-size: 0.9375rem; font-weight: 500; border-radius: var(--radius-md); border: 1px solid transparent; cursor: pointer; transition: all var(--transition); }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .btn-primary { background: var(--primary); color: white; border-color: var(--primary); }
        .btn-primary:hover:not(:disabled) { background: var(--primary-hover); border-color: var(--primary-hover); }
        .btn-secondary { background: var(--bg-secondary); color: var(--text-primary); border-color: var(--border-color); }
        .btn-secondary:hover:not(:disabled) { background: var(--bg-primary); border-color: var(--secondary); }

        .auth-footer { text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color); font-size: 0.875rem; color: var(--text-secondary); }
        .auth-footer a { font-weight: 500; }

        .alert { padding: 0.875rem 1rem; border-radius: var(--radius-md); margin-bottom: 1.25rem; font-size: 0.875rem; display: flex; align-items: flex-start; gap: 0.5rem; }
        .alert-error { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #991b1b; }
        .alert-success { background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #065f46; }
        .alert-warning { background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); color: #92400e; }
        .alert-info { background: rgba(37, 99, 235, 0.1); border: 1px solid rgba(37, 99, 235, 0.2); color: #1e40af; }

        .input-wrapper { position: relative; }
        .input-wrapper .icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-secondary); pointer-events: none; }
        .input-wrapper .form-input { padding-left: 3rem; }
        .input-wrapper .toggle-password { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-secondary); cursor: pointer; padding: 0.25rem; }
        .input-wrapper .toggle-password:hover { color: var(--text-primary); }

        @media (max-width: 480px) {
            .auth-card { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">CBT</div>
                <h1 class="auth-title"><?= h($title ?? 'Masuk') ?></h1>
                <p class="auth-subtitle"><?= $subtitle ?? 'Masuk ke akun Anda untuk melanjutkan' ?></p>
            </div>

            <?php if (has_flash('success')): ?>
                <div class="alert alert-success"><?= get_flash('success') ?></div>
            <?php endif; ?>
            <?php if (has_flash('error')): ?>
                <div class="alert alert-error"><?= get_flash('error') ?></div>
            <?php endif; ?>
            <?php if (has_flash('warning')): ?>
                <div class="alert alert-warning"><?= get_flash('warning') ?></div>
            <?php endif; ?>
            <?php if (has_flash('info')): ?>
                <div class="alert alert-info"><?= get_flash('info') ?></div>
            <?php endif; ?>

            <?= $content ?>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.parentElement.querySelector('.form-input');
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                btn.innerHTML = type === 'password'
                    ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>'
                    : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
            });
        });

        // Auto-focus first input
        const firstInput = document.querySelector('.form-input');
        firstInput?.focus();

        // Form submission loading state
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', () => {
                const btn = form.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<svg class="animate-spin" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10" stroke-opacity="1"/></svg> Memproses...';
                }
            });
        });

        <style>
            .animate-spin { animation: spin 1s linear infinite; }
            @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        </style>
    </script>
</body>
</html>