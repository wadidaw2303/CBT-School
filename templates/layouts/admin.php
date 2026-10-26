<!DOCTYPE html>
<html lang="<?= h(config('app.locale', 'id')) ?>" dir="<?= $rtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= h(csrf_token()) ?>">
    <title><?= $title ?? 'Admin Panel' ?> - <?= h(config('app.name')) ?></title>

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
    <link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>">
    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 60px;
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
        body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg-primary); color: var(--text-primary); line-height: 1.5; }
        a { color: var(--primary); text-decoration: none; }
        a:hover { color: var(--primary-hover); }

        /* Layout */
        .layout { display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
            transition: transform var(--transition);
        }
        .sidebar-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .sidebar-logo {
            width: 36px; height: 36px;
            background: var(--primary);
            border-radius: var(--radius-md);
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 1.125rem;
        }
        .sidebar-title { font-weight: 600; font-size: 1.125rem; color: var(--text-primary); }
        .sidebar-nav { padding: 1rem 0; }
        .nav-section { margin-bottom: 1.5rem; }
        .nav-section-title {
            padding: 0 1.5rem;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }
        .nav-item { list-style: none; }
        .nav-link {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.625rem 1.5rem;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.875rem;
            transition: all var(--transition);
            border-left: 3px solid transparent;
        }
        .nav-link:hover, .nav-link.active {
            background: rgba(37, 99, 235, 0.08);
            color: var(--primary);
            border-left-color: var(--primary);
        }
        .nav-link i { width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; }
        .nav-badge {
            margin-left: auto;
            background: var(--danger);
            color: white;
            font-size: 0.625rem;
            font-weight: 600;
            padding: 0.125rem 0.375rem;
            border-radius: 9999px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Header */
        .top-header {
            height: var(--header-height);
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
        }
        .header-left { display: flex; align-items: center; gap: 1rem; }
        .menu-toggle { display: none; background: none; border: none; padding: 0.5rem; color: var(--text-secondary); cursor: pointer; }
        .breadcrumb { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--text-secondary); }
        .breadcrumb a { color: var(--text-secondary); }
        .breadcrumb a:hover { color: var(--primary); }
        .breadcrumb-separator { color: var(--text-secondary); }
        .breadcrumb-current { color: var(--text-primary); font-weight: 500; }

        .header-right { display: flex; align-items: center; gap: 1rem; }
        .header-btn { background: none; border: none; padding: 0.5rem; color: var(--text-secondary); cursor: pointer; border-radius: var(--radius-md); transition: all var(--transition); position: relative; }
        .header-btn:hover { background: var(--bg-primary); color: var(--text-primary); }
        .header-btn .badge { position: absolute; top: 0.25rem; right: 0.25rem; background: var(--danger); color: white; font-size: 0.625rem; font-weight: 600; padding: 0.125rem 0.375rem; border-radius: 9999px; min-width: 16px; text-align: center; }

        .user-menu { position: relative; }
        .user-menu-btn { display: flex; align-items: center; gap: 0.5rem; padding: 0.375rem 0.75rem; border-radius: var(--radius-md); cursor: pointer; transition: all var(--transition); border: 1px solid transparent; }
        .user-menu-btn:hover { background: var(--bg-primary); border-color: var(--border-color); }
        .user-avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.875rem; }
        .user-name { font-weight: 500; font-size: 0.875rem; color: var(--text-primary); max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .user-role { font-size: 0.625rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.025em; }
        .user-dropdown {
            position: absolute; top: calc(100% + 0.5rem); right: 0;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            min-width: 220px;
            padding: 0.5rem;
            opacity: 0; visibility: hidden; transform: translateY(-8px);
            transition: all var(--transition);
            z-index: 1000;
        }
        .user-menu:hover .user-dropdown { opacity: 1; visibility: visible; transform: translateY(0); }
        .dropdown-item { display: flex; align-items: center; gap: 0.5rem; padding: 0.625rem 0.875rem; border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.875rem; font-weight: 500; transition: all var(--transition); }
        .dropdown-item:hover { background: var(--bg-primary); }
        .dropdown-item.danger { color: var(--danger); }
        .dropdown-divider { height: 1px; background: var(--border-color); margin: 0.5rem 0; }

        /* Page Content */
        .page-content { flex: 1; padding: 1.5rem; }
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
        .page-title { font-size: 1.5rem; font-weight: 600; color: var(--text-primary); }
        .page-subtitle { font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.25rem; }
        .page-actions { display: flex; gap: 0.75rem; }

        /* Cards */
        .card { background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); }
        .card-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; }
        .card-title { font-weight: 600; font-size: 1rem; color: var(--text-primary); }
        .card-body { padding: 1.25rem; }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; border-radius: var(--radius-md); border: 1px solid transparent; cursor: pointer; transition: all var(--transition); white-space: nowrap; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.8125rem; }
        .btn-lg { padding: 0.75rem 1.5rem; font-size: 1rem; }
        .btn-primary { background: var(--primary); color: white; border-color: var(--primary); }
        .btn-primary:hover:not(:disabled) { background: var(--primary-hover); border-color: var(--primary-hover); }
        .btn-secondary { background: var(--bg-secondary); color: var(--text-primary); border-color: var(--border-color); }
        .btn-secondary:hover:not(:disabled) { background: var(--bg-primary); border-color: var(--secondary); }
        .btn-danger { background: var(--danger); color: white; border-color: var(--danger); }
        .btn-danger:hover:not(:disabled) { background: #dc2626; border-color: #dc2626; }
        .btn-success { background: var(--success); color: white; border-color: var(--success); }
        .btn-success:hover:not(:disabled) { background: #059669; border-color: #059669; }
        .btn-outline { background: transparent; }
        .btn-outline.btn-primary { color: var(--primary); border-color: var(--primary); }
        .btn-outline.btn-primary:hover { background: var(--primary); color: white; }

        /* Forms */
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; font-size: 0.8125rem; font-weight: 500; color: var(--text-primary); margin-bottom: 0.375rem; }
        .form-input { width: 100%; padding: 0.5rem 0.875rem; font-size: 0.875rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--bg-secondary); color: var(--text-primary); transition: all var(--transition); }
        .form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .form-input::placeholder { color: var(--text-secondary); }
        .form-input.error { border-color: var(--danger); }
        .form-error { font-size: 0.75rem; color: var(--danger); margin-top: 0.25rem; }
        .form-help { font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.25rem; }
        .form-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E"); background-position: right 0.75rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; padding-right: 2.5rem; }
        textarea.form-input { min-height: 100px; resize: vertical; }
        .form-check { display: flex; align-items: center; gap: 0.5rem; }
        .form-check-input { width: 18px; height: 18px; accent-color: var(--primary); cursor: pointer; }
        .form-check-label { font-size: 0.875rem; color: var(--text-primary); cursor: pointer; }

        /* Tables */
        .table-container { overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        .table th { padding: 0.75rem 1rem; text-align: left; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); background: var(--bg-primary); border-bottom: 1px solid var(--border-color); white-space: nowrap; }
        .table td { padding: 0.875rem 1rem; border-bottom: 1px solid var(--border-color); color: var(--text-primary); vertical-align: middle; }
        .table tbody tr { transition: background var(--transition); }
        .table tbody tr:hover { background: var(--bg-primary); }
        .table tbody tr:last-child td { border-bottom: none; }

        /* Badges */
        .badge { display: inline-flex; align-items: center; padding: 0.25rem 0.5rem; font-size: 0.6875rem; font-weight: 600; border-radius: 9999px; }
        .badge-primary { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
        .badge-success { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .badge-warning { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .badge-danger { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
        .badge-secondary { background: rgba(100, 116, 139, 0.1); color: var(--secondary); }

        /* Alerts */
        .alert { padding: 0.875rem 1rem; border-radius: var(--radius-md); margin-bottom: 1rem; display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.875rem; }
        .alert-success { background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #065f46; }
        .alert-error { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #991b1b; }
        .alert-warning { background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); color: #92400e; }
        .alert-info { background: rgba(37, 99, 235, 0.1); border: 1px solid rgba(37, 99, 235, 0.2); color: #1e40af; }

        /* Pagination */
        .pagination { display: flex; align-items: center; gap: 0.25rem; }
        .pagination a, .pagination span { display: flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 0.75rem; border-radius: var(--radius-md); font-size: 0.875rem; font-weight: 500; color: var(--text-secondary); border: 1px solid var(--border-color); background: var(--bg-secondary); transition: all var(--transition); }
        .pagination a:hover { background: var(--bg-primary); border-color: var(--secondary); color: var(--text-primary); }
        .pagination .current { background: var(--primary); border-color: var(--primary); color: white; }
        .pagination .disabled { opacity: 0.5; pointer-events: none; }

        /* Modal */
        .modal-overlay { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 1rem; opacity: 0; visibility: hidden; transition: all var(--transition); }
        .modal-overlay.active { opacity: 1; visibility: visible; }
        .modal { background: var(--bg-secondary); border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); max-width: 32rem; width: 100%; max-height: 90vh; overflow: hidden; transform: scale(0.95); transition: all var(--transition); }
        .modal-overlay.active .modal { transform: scale(1); }
        .modal-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; }
        .modal-title { font-weight: 600; font-size: 1rem; }
        .modal-close { background: none; border: none; padding: 0.25rem; color: var(--text-secondary); cursor: pointer; border-radius: var(--radius-sm); }
        .modal-close:hover { background: var(--bg-primary); color: var(--text-primary); }
        .modal-body { padding: 1.25rem; overflow-y: auto; max-height: 60vh; }
        .modal-footer { padding: 1rem 1.25rem; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 0.75rem; }

        /* Grid */
        .grid { display: grid; gap: 1rem; }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }

        /* Stats Cards */
        .stat-card { background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.25rem; }
        .stat-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; }
        .stat-card-icon { width: 40px; height: 40px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; }
        .stat-card-icon.primary { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
        .stat-card-icon.success { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .stat-card-icon.warning { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .stat-card-icon.danger { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
        .stat-card-value { font-size: 1.875rem; font-weight: 700; color: var(--text-primary); line-height: 1.2; }
        .stat-card-label { font-size: 0.8125rem; color: var(--text-secondary); margin-top: 0.25rem; }
        .stat-card-trend { font-size: 0.75rem; font-weight: 500; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.25rem; }
        .stat-card-trend.positive { color: var(--success); }
        .stat-card-trend.negative { color: var(--danger); }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .menu-toggle { display: flex; }
            .sidebar-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 99; opacity: 0; visibility: hidden; transition: all var(--transition); }
            .sidebar-overlay.active { opacity: 1; visibility: visible; }
        }

        @media (max-width: 768px) {
            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }
            .page-header { flex-direction: column; align-items: flex-start; }
            .page-actions { width: 100%; }
            .page-actions .btn { flex: 1; }
        }
    </style>
</head>
<body>
    <div class="layout">
        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">CBT</div>
                <span class="sidebar-title"><?= h(config('app.name')) ?></span>
            </div>
            <nav class="sidebar-nav">
                <?php if (is_admin()): ?>
                    <?= view('partials/sidebar-nav') ?>
                <?php endif; ?>

                <?php if (is_teacher()): ?>
                <div class="nav-section">
                    <div class="nav-section-title">Dashboard</div>
                    <ul>
                        <li class="nav-item"><a href="<?= url('teacher/dashboard') ?>" class="nav-link <?= request()->getPathInfo() === '/teacher/dashboard' ? 'active' : '' ?>"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>Dashboard</a></li>
                    </ul>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Bank Soal</div>
                    <ul>
                        <li class="nav-item"><a href="<?= url('teacher/questions') ?>" class="nav-link <?= str_starts_with(request()->getPathInfo(), '/teacher/questions') ? 'active' : '' ?>"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>Bank Soal</a></li>
                        <li class="nav-item"><a href="<?= url('teacher/categories') ?>" class="nav-link <?= str_starts_with(request()->getPathInfo(), '/teacher/categories') ? 'active' : '' ?>"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>Kategori</a></li>
                    </ul>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Ujian</div>
                    <ul>
                        <li class="nav-item"><a href="<?= url('teacher/exams') ?>" class="nav-link <?= str_starts_with(request()->getPathInfo(), '/teacher/exams') ? 'active' : '' ?>"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>Daftar Ujian</a></li>
                        <li class="nav-item"><a href="<?= url('teacher/exams/create') ?>" class="nav-link"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Buat Ujian Baru</a></li>
                    </ul>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Penilaian</div>
                    <ul>
                        <li class="nav-item"><a href="<?= url('teacher/grading') ?>" class="nav-link <?= str_starts_with(request()->getPathInfo(), '/teacher/grading') ? 'active' : '' ?>"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>Penilaian Esai</a></li>
                    </ul>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Laporan</div>
                    <ul>
                        <li class="nav-item"><a href="<?= url('teacher/reports') ?>" class="nav-link <?= str_starts_with(request()->getPathInfo(), '/teacher/reports') ? 'active' : '' ?>"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>Laporan Hasil</a></li>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (is_student()): ?>
                <div class="nav-section">
                    <div class="nav-section-title">Dashboard</div>
                    <ul>
                        <li class="nav-item"><a href="<?= url('student/dashboard') ?>" class="nav-link <?= request()->getPathInfo() === '/student/dashboard' ? 'active' : '' ?>"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>Dashboard</a></li>
                    </ul>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Ujian</div>
                    <ul>
                        <li class="nav-item"><a href="<?= url('student/exams') ?>" class="nav-link <?= str_starts_with(request()->getPathInfo(), '/student/exams') ? 'active' : '' ?>"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>Ujian Saya</a></li>
                        <li class="nav-item"><a href="<?= url('student/results') ?>" class="nav-link <?= str_starts_with(request()->getPathInfo(), '/student/results') ? 'active' : '' ?>"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>Hasil Ujian</a></li>
                    </ul>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Profil</div>
                    <ul>
                        <li class="nav-item"><a href="<?= url('student/profile') ?>" class="nav-link <?= str_starts_with(request()->getPathInfo(), '/student/profile') ? 'active' : '' ?>"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>Profil Saya</a></li>
                    </ul>
                </div>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    </button>
                    <nav class="breadcrumb" aria-label="Breadcrumb">
                        <?php if (isset($breadcrumbs)): ?>
                            <?php foreach ($breadcrumbs as $i => $crumb): ?>
                                <?php if ($i > 0): ?>
                                    <span class="breadcrumb-separator">/</span>
                                <?php endif; ?>
                                <?php if (isset($crumb['url']) && $i < count($breadcrumbs) - 1): ?>
                                    <a href="<?= h($crumb['url']) ?>"><?= h($crumb['label']) ?></a>
                                <?php else: ?>
                                    <span class="breadcrumb-current"><?= h($crumb['label']) ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </nav>
                </div>
                <div class="header-right">
                    <?php if (is_admin() || is_teacher()): ?>
                    <div class="user-menu">
                        <div class="user-menu-btn">
                            <?php if (auth()->avatar): ?>
                                <img src="<?= h(auth()->getAvatarUrl()) ?>" alt="" class="user-avatar">
                            <?php else: ?>
                                <div class="user-avatar"><?= h(mb_substr(auth()->name, 0, 1)) ?></div>
                            <?php endif; ?>
                            <div>
                                <div class="user-name"><?= h(auth()->name) ?></div>
                                <div class="user-role"><?= h(auth()->getRole()?->name ?? '') ?></div>
                            </div>
                        </div>
                        <div class="user-dropdown">
                            <a href="<?= url('profile') ?>" class="dropdown-item">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Profil
                            </a>
                            <a href="<?= url('settings') ?>" class="dropdown-item">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                Pengaturan
                            </a>
                            <div class="dropdown-divider"></div>
                            <form action="<?= url('logout') ?>" method="POST" style="width:100%">
                                <?= csrf_field() ?>
                                <button type="submit" class="dropdown-item danger">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Page Content -->
            <div class="page-content">
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
        </main>
    </div>

    <script>
        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle = document.getElementById('menuToggle');

        toggle?.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        });

        overlay?.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        });

        // Close sidebar on resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 1024) {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
            }
        });

        // Flash message auto-dismiss
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });

        // Confirm delete
        document.querySelectorAll('[data-confirm]').forEach(el => {
            el.addEventListener('click', e => {
                if (!confirm(el.dataset.confirm)) e.preventDefault();
            });
        });
    </script>
</body>
</html>