<?php
/**
 * Admin Sidebar Navigation
 * Usage: @include('partials.sidebar-nav')
 * Requires: $breadcrumbs, current route via request()->routeIs()
 */

$navItems = [
    'dashboard' => [
        'label' => 'Dashboard',
        'icon' => 'home',
        'route' => 'admin.dashboard',
        'permission' => 'core',
        'children' => [],
    ],
    'academic' => [
        'label' => 'Akademik',
        'icon' => 'graduation-cap',
        'permission' => 'academic',
        'children' => [
            ['label' => 'Tahun Ajaran', 'route' => 'admin.academic-years.index', 'permission' => 'academic.years'],
            ['label' => 'Semester', 'route' => 'admin.semesters.index', 'permission' => 'academic.semesters'],
            ['label' => 'Kelas', 'route' => 'admin.classes.index', 'permission' => 'academic.classes'],
            ['label' => 'Mata Pelajaran', 'route' => 'admin.subjects.index', 'permission' => 'academic.subjects'],
            ['label' => 'Enrollment', 'route' => 'admin.enrollment.index', 'permission' => 'admin.enrollment'],
            ['label' => 'Jadwal Ujian', 'route' => 'admin.exam-schedule.index', 'permission' => 'academic.schedule'],
        ],
    ],
    'assessment' => [
        'label' => 'Penilaian',
        'icon' => 'clipboard-check',
        'permission' => 'assessment',
        'children' => [
            ['label' => 'Ujian', 'route' => 'admin.exams.index', 'permission' => 'assessment.exams'],
            ['label' => 'Bank Soal', 'route' => 'admin.questions.index', 'permission' => 'academic.questions'],
            ['label' => 'Penilaian', 'route' => 'admin.grading.index', 'permission' => 'assessment.grading'],
            ['label' => 'Proctoring', 'route' => 'admin.proctoring.index', 'permission' => 'assessment.proctoring'],
            ['label' => 'Hasil Ujian', 'route' => 'admin.results.index', 'permission' => 'assessment.results'],
            ['label' => 'Analitik', 'route' => 'admin.analytics.index', 'permission' => 'assessment.analytics'],
            ['label' => 'Sertifikat', 'route' => 'admin.certificates.index', 'permission' => 'assessment.certificates'],
            ['label' => 'Laporan', 'route' => 'admin.reports.index', 'permission' => 'assessment.reports'],
        ],
    ],
    'users' => [
        'label' => 'Pengguna',
        'icon' => 'users',
        'permission' => 'admin.users',
        'children' => [
            ['label' => 'Manajemen User', 'route' => 'admin.users.index', 'permission' => 'admin.users'],
            ['label' => 'Peran & Hak Akses', 'route' => 'admin.roles.index', 'permission' => 'admin.roles'],
        ],
    ],
    'communication' => [
        'label' => 'Komunikasi',
        'icon' => 'megaphone',
        'permission' => 'communication',
        'children' => [
            ['label' => 'Pengumuman', 'route' => 'admin.announcements.index', 'permission' => 'communication.announcements'],
            ['label' => 'Notifikasi', 'route' => 'admin.notifications.index', 'permission' => 'communication.notifications'],
        ],
    ],
    'settings' => [
        'label' => 'Pengaturan',
        'icon' => 'settings',
        'permission' => 'admin.settings',
        'children' => [
            ['label' => 'Umum', 'route' => 'admin.settings.general', 'permission' => 'admin.settings'],
            ['label' => 'Tampilan', 'route' => 'admin.settings.appearance', 'permission' => 'admin.appearance'],
            ['label' => 'Integrasi', 'route' => 'admin.settings.integration', 'permission' => 'admin.integration'],
            ['label' => 'Backup', 'route' => 'admin.settings.backup', 'permission' => 'admin.backup'],
        ],
    ],
    'system' => [
        'label' => 'Sistem',
        'icon' => 'server',
        'permission' => 'admin.audit',
        'children' => [
            ['label' => 'Log Aktivitas', 'route' => 'admin.activity-logs.index', 'permission' => 'admin.audit'],
            ['label' => 'Maintenance', 'route' => 'admin.settings.maintenance', 'permission' => 'admin.maintenance'],
        ],
    ],
];

$icons = [
    'home' => '<svg class="sidebar-nav__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
    'graduation-cap' => '<svg class="sidebar-nav__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 21a6 6 0 0 0-12 0"/><path d="M21 13a6 6 0 0 0-12 0"/><path d="M15 5v4a2 2 0 0 1-4 0V5"/><path d="M3 13h18"/><path d="M5 21a2 2 0 0 1 4 0"/></svg>',
    'clipboard-check' => '<svg class="sidebar-nav__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M9 14l3 3 7-7"/></svg>',
    'users' => '<svg class="sidebar-nav__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'megaphone' => '<svg class="sidebar-nav__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 11a8 8 0 0 0 0 2"/><path d="M8 8a12 12 0 0 1 0 8"/><path d="M12 5a16 16 0 0 1 0 14"/><path d="M13 17a2 2 0 0 1 0-4"/><path d="M9 17a2 2 0 0 0 0-4"/></svg>',
    'settings' => '<svg class="sidebar-nav__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
    'server' => '<svg class="sidebar-nav__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"/><rect x="2" y="14" width="20" height="8" rx="2" ry="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>',
    'chevron-down' => '<svg class="sidebar-nav__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>',
    'chevron-right' => '<svg class="sidebar-nav__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>',
];

function renderNavItem($item, $icons, $level = 0) {
    $hasChildren = !empty($item['children']);
    $canAccess = can($item['permission'] ?? 'core');
    if (!$canAccess && !$hasChildren) return '';

    $active = false;
    if (!empty($item['route'])) {
        $active = request()->routeIs($item['route'] . '*');
    }
    if ($hasChildren) {
        foreach ($item['children'] as $child) {
            if (request()->routeIs($child['route'] . '*')) {
                $active = true;
                break;
            }
        }
    }

    $indent = str_repeat('  ', $level + 1);
    $html = '';

    if ($hasChildren) {
        $childHtml = '';
        foreach ($item['children'] as $child) {
            $childHtml .= renderNavItem($child, $icons, $level + 1);
        }
        if ($childHtml === '') return '';

        $html .= "{$indent}<details class=\"sidebar-nav__group\" {$active ? 'open' : ''}>\n";
        $html .= "{$indent}  <summary class=\"sidebar-nav__summary " . ($active ? 'sidebar-nav__summary--open' : '') . "\">\n";
        $html .= "{$indent}    {$icons[$item['icon'] ?? 'chevron-right']}\n";
        $html .= "{$indent}    <span class=\"sidebar-nav__label\">{$item['label']}</span>\n";
        $html .= "{$indent}    {$icons['chevron-down']}\n";
        $html .= "{$indent}  </summary>\n";
        $html .= "{$indent}  <ul class=\"sidebar-nav__sublist\">\n";
        $html .= $childHtml;
        $html .= "{$indent}  </ul>\n";
        $html .= "{$indent}</details>\n";
    } else {
        $html .= "{$indent}<a href=\"" . route($item['route']) . "\" class=\"sidebar-nav__item " . ($active ? 'sidebar-nav__item--active' : '') . "\" aria-current=\"" . ($active ? 'page' : 'false') . "\">\n";
        $html .= "{$indent}  {$icons[$item['icon'] ?? 'chevron-right']}\n";
        $html .= "{$indent}  <span class=\"sidebar-nav__label\">{$item['label']}</span>\n";
        $html .= "{$indent}</a>\n";
    }

    return $html;
}
?>

<nav class="sidebar-nav" aria-label="Navigasi Admin">
    <ul class="sidebar-nav__list">
        <?php foreach ($navItems as $sectionKey => $section): ?>
            <?php if (can($section['permission'])): ?>
                <?php if (!empty($section['children'])): ?>
                    <?= renderNavItem($section, $icons) ?>
                <?php else: ?>
                    <?php
                    $active = request()->routeIs($section['route'] . '*');
                    $indent = '  ';
                    ?>
                    <li>
                        <a href="<?= route($section['route']) ?>" class="sidebar-nav__item <?= $active ? 'sidebar-nav__item--active' : '' ?>" aria-current="<?= $active ? 'page' : 'false' ?>">
                            <?= $icons[$section['icon'] ?? 'home'] ?>
                            <span class="sidebar-nav__label"><?= $section['label'] ?></span>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</nav>

<style>
/* Sidebar Nav - scoped to this partial */
.sidebar-nav__list { list-style: none; padding: 0; margin: 0; }
.sidebar-nav__group { border-radius: var(--radius-lg); overflow: hidden; }
.sidebar-nav__summary {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-2) var(--space-3);
  border-radius: var(--radius-lg);
  color: var(--color-text-secondary);
  font-size: var(--text-sm);
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition-fast);
  list-style: none;
}
.sidebar-nav__summary::-webkit-details-marker { display: none; }
.sidebar-nav__summary:hover {
  background: var(--color-bg-primary);
  color: var(--color-text-primary);
}
.sidebar-nav__summary--open {
  background: var(--color-primary);
  color: white;
}
.sidebar-nav__summary--open .sidebar-nav__chevron { transform: rotate(180deg); }
.sidebar-nav__chevron {
  flex-shrink: 0;
  margin-left: auto;
  transition: transform var(--transition-fast);
  color: inherit;
}
.sidebar-nav__sublist {
  list-style: none;
  padding: var(--space-1) 0 0 var(--space-10);
  margin: 0;
  border-inline-start: 2px solid var(--color-border);
}
.sidebar-nav__item {
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
.sidebar-nav__item:hover {
  background: var(--color-bg-primary);
  color: var(--color-text-primary);
}
.sidebar-nav__item--active {
  background: var(--color-primary);
  color: white;
}
.sidebar-nav__item--active:hover {
  background: var(--color-primary-hover);
}
.sidebar-nav__icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
  color: inherit;
}

/* Collapsed sidebar state */
.layout-admin__sidebar.collapsed .sidebar-nav__label,
.layout-admin__sidebar.collapsed .sidebar-nav__chevron,
.layout-admin__sidebar.collapsed .sidebar-nav__sublist {
  display: none;
}
.layout-admin__sidebar.collapsed .sidebar-nav__summary,
.layout-admin__sidebar.collapsed .sidebar-nav__item {
  justify-content: center;
  padding: var(--space-2);
}
.layout-admin__sidebar.collapsed .sidebar-nav__sublist {
  position: absolute;
  left: var(--sidebar-collapsed-width);
  top: 0;
  background: var(--color-bg-elevated);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  padding: var(--space-2);
  min-width: 200px;
  z-index: var(--z-dropdown);
}
.layout-admin__sidebar.collapsed .sidebar-nav__group:hover .sidebar-nav__sublist,
.layout-admin__sidebar.collapsed .sidebar-nav__group:focus-within .sidebar-nav__sublist {
  display: block;
}
.layout-admin__sidebar.collapsed .sidebar-nav__group:hover .sidebar-nav__summary,
.layout-admin__sidebar.collapsed .sidebar-nav__group:focus-within .sidebar-nav__summary {
  background: var(--color-primary);
  color: white;
}
</style>