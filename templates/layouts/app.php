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
      /* Layout App - Student/Teacher shared layout */
      .layout-app {
        display: flex;
        min-height: 100vh;
      }
      .layout-app__sidebar {
        width: 260px;
        flex-shrink: 0;
        background: var(--color-bg-secondary);
        border-right: 1px solid var(--color-border-color);
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        z-index: 40;
        transition: transform var(--transition-normal);
      }
      .layout-app__main {
        flex: 1;
        margin-left: 260px;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
      }
      .layout-app__header {
        height: 64px;
        background: var(--color-bg-secondary);
        border-bottom: 1px solid var(--color-border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 var(--space-6);
        position: sticky;
        top: 0;
        z-index: 30;
      }
      .header__brand {
        display: flex;
        align-items: center;
        gap: var(--space-3);
      }
      .header__logo {
        width: 36px;
        height: 36px;
        border-radius: var(--radius-lg);
        background: var(--color-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: var(--text-lg);
      }
      .header__name {
        font-weight: 600;
        font-size: var(--text-lg);
        color: var(--color-text-primary);
      }
      .header__actions {
        display: flex;
        align-items: center;
        gap: var(--space-2);
      }
      .layout-app__content {
        flex: 1;
        padding: var(--space-6);
        background: var(--color-bg-primary);
      }
      .layout-app__footer {
        padding: var(--space-4) var(--space-6);
        background: var(--color-bg-secondary);
        border-top: 1px solid var(--color-border-color);
        font-size: var(--text-sm);
        color: var(--color-text-secondary);
        text-align: center;
      }

      /* Sidebar */
      .sidebar__brand {
        padding: var(--space-4) var(--space-4);
        border-bottom: 1px solid var(--color-border-color);
      }
      .sidebar__logo {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-lg);
        background: var(--color-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: var(--text-xl);
        margin: 0 auto;
      }
      .sidebar__nav {
        flex: 1;
        padding: var(--space-4);
        overflow-y: auto;
      }
      .sidebar__section {
        margin-bottom: var(--space-6);
      }
      .sidebar__section-title {
        font-size: var(--text-xs);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--color-text-tertiary);
        padding: 0 var(--space-3);
        margin-bottom: var(--space-2);
      }
      .sidebar__item {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        padding: var(--space-2) var(--space-3);
        border-radius: var(--radius-lg);
        color: var(--color-text-secondary);
        text-decoration: none;
        font-size: var(--text-sm);
        font-weight: 500;
        transition: all var(--transition-fast);
      }
      .sidebar__item:hover {
        background: var(--color-bg-primary);
        color: var(--color-text-primary);
      }
      .sidebar__item--active {
        background: var(--color-primary);
        color: white;
      }
      .sidebar__item--active:hover {
        background: var(--color-primary-hover);
      }
      .sidebar__item-icon {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
      }
      .sidebar__user {
        padding: var(--space-4);
        border-top: 1px solid var(--color-border-color);
      }
      .sidebar__user-info {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        padding: var(--space-2);
      }
      .sidebar__user-avatar {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-full);
        background: var(--color-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: var(--text-lg);
      }
      .sidebar__user-name {
        font-weight: 600;
        font-size: var(--text-sm);
        color: var(--color-text-primary);
      }
      .sidebar__user-role {
        font-size: var(--text-xs);
        color: var(--color-text-tertiary);
        text-transform: capitalize;
      }

      /* Mobile sidebar toggle */
      .sidebar-toggle {
        display: none;
        padding: var(--space-2);
        border-radius: var(--radius-lg);
        background: transparent;
        border: none;
        color: var(--color-text-primary);
      }
      .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: var(--color-black/50);
        z-index: 35;
      }

      @media (max-width: 1024px) {
        .layout-app__sidebar {
          transform: translateX(-100%);
        }
        .layout-app__sidebar--open {
          transform: translateX(0);
        }
        .layout-app__main {
          margin-left: 0;
        }
        .sidebar-toggle {
          display: flex;
        }
        .sidebar-overlay--open {
          display: block;
        }
      }

      /* Breadcrumbs */
      .breadcrumbs {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        font-size: var(--text-sm);
        margin-bottom: var(--space-4);
        flex-wrap: wrap;
      }
      .breadcrumbs__item {
        display: flex;
        align-items: center;
        gap: var(--space-2);
      }
      .breadcrumbs__link {
        color: var(--color-text-secondary);
        text-decoration: none;
        transition: color var(--transition-fast);
      }
      .breadcrumbs__link:hover {
        color: var(--color-primary);
      }
      .breadcrumbs__separator {
        color: var(--color-text-tertiary);
      }
      .breadcrumbs__current {
        color: var(--color-text-primary);
        font-weight: 500;
      }
    </style>
</head>
<body>
    <div class="layout-app">
        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        <!-- Sidebar -->
        <aside class="layout-app__sidebar" id="appSidebar" role="navigation" aria-label="Menu utama">
            <div class="sidebar__brand">
                <div class="sidebar__logo" aria-hidden="true">
                    <?= strtoupper(config('school.short_name', 'C')[0]) ?>
                </div>
            </div>
            <nav class="sidebar__nav">
                <!-- Navigation will be injected per role -->
                <div id="sidebarNav"></div>
            </nav>
            <div class="sidebar__user">
                <div class="sidebar__user-info">
                    <div class="sidebar__user-avatar" aria-hidden="true">
                        <?= auth() ? strtoupper(auth()->name[0]) : '?' ?>
                    </div>
                    <div>
                        <div class="sidebar__user-name"><?= auth()?->name ?? 'Guest' ?></div>
                        <div class="sidebar__user-role"><?= auth()?->getRole()?->name ?? '' ?></div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="layout-app__main">
            <!-- Header -->
            <header class="layout-app__header" role="banner">
                <div class="header__brand">
                    <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle menu" aria-expanded="false" aria-controls="appSidebar">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                    <div class="header__logo school-logo" aria-label="<?= h(config('school.name')) ?>" style="width: 32px; height: 32px;"></div>
                    <span class="header__name"><?= h(config('school.short_name')) ?></span>
                </div>
                <div class="header__actions">
                    <?php if (can('admin.users')): ?>
                        <a href="<?= route('admin.users.index') ?>" class="btn btn--ghost btn--sm">Manajemen User</a>
                    <?php endif; ?>
                    <?php if (can('assessment.proctoring')): ?>
                        <a href="<?= route('proktor.dashboard') ?>" class="btn btn--ghost btn--sm">Proktor</a>
                    <?php endif; ?>
                    <!-- User menu dropdown will be added here -->
                </div>
            </header>

            <!-- Content -->
            <div class="layout-app__content">
                <?php if (isset($breadcrumbs) && $breadcrumbs): ?>
                    <nav class="breadcrumbs" aria-label="Breadcrumb">
                        <?php foreach ($breadcrumbs as $i => $crumb): ?>
                            <div class="breadcrumbs__item">
                                <?php if ($i > 0): ?>
                                    <svg class="breadcrumbs__separator" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <polyline points="9 18 15 12 9 6"/>
                                    </svg>
                                <?php endif; ?>
                                <?php if (!empty($crumb['url'])): ?>
                                    <a href="<?= h($crumb['url']) ?>" class="breadcrumbs__link"><?= h($crumb['label']) ?></a>
                                <?php else: ?>
                                    <span class="breadcrumbs__current" aria-current="page"><?= h($crumb['label']) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </nav>
                <?php endif; ?>

                <!-- Flash Messages -->
                <?php if (has_flash('success')): ?>
                    <div class="alert alert--success" role="alert"><?= get_flash('success') ?></div>
                <?php endif; ?>
                <?php if (has_flash('error')): ?>
                    <div class="alert alert--danger" role="alert"><?= get_flash('error') ?></div>
                <?php endif; ?>
                <?php if (has_flash('warning')): ?>
                    <div class="alert alert--warning" role="alert"><?= get_flash('warning') ?></div>
                <?php endif; ?>
                <?php if (has_flash('info')): ?>
                    <div class="alert alert--info" role="alert"><?= get_flash('info') ?></div>
                <?php endif; ?>

                <?= $content ?? '' ?>
            </div>

            <!-- Footer -->
            <footer class="layout-app__footer" role="contentinfo">
                &copy; <?= date('Y') ?> <?= h(config('school.name')) ?>. <?= h(config('school.short_name')) ?> v<?= h(config('app.version', '1.0.0')) ?>
            </footer>
        </main>
    </div>

    <script>
      // Sidebar toggle for mobile
      function toggleSidebar() {
        const sidebar = document.getElementById('appSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const isOpen = sidebar.classList.toggle('layout-app__sidebar--open');
        overlay.classList.toggle('sidebar-overlay--open', isOpen);
        document.querySelector('.sidebar-toggle')?.setAttribute('aria-expanded', isOpen);
      }

      // Close sidebar on link click (mobile)
      document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.sidebar__item').forEach(link => {
          link.addEventListener('click', () => {
            if (window.innerWidth <= 1024) toggleSidebar();
          });
        });
      });
    </script>
    <?= $scripts ?? '' ?>
</body>
</html>