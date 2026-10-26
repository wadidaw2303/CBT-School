# CBT-School Knowledge Base
**Version:** 1.0  
**Last Updated:** 2026-10-04  
**Purpose:** Centralized reference for patterns, conventions, and decisions

---

## 🎯 CORE PRINCIPLES

1. **Single Source of Truth** — `design-system.css` owns all design tokens; no duplicate values in `app.css`
2. **Plain PHP Templates** — No Blade, no Twig; use `h()` for escaping, `asset()` for assets
3. **Permission-First** — Every UI element gated by `can($module)` via `ModuleAccess`
4. **School Config Runtime** — Branding injected via `window.SCHOOL_CONFIG` → `theme.js` → CSS vars
5. **Mobile-First Responsive** — Design system utilities + layout-specific breakpoints

---

## 🔑 KEY PATTERNS

### Layout Injection (All 4+1 Layouts)
```php
// In <head>, BEFORE any CSS
<script>
  window.SCHOOL_CONFIG = <?= json_encode($schoolConfig ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>
<link rel="stylesheet" href="<?= asset('assets/css/design-system.css') ?>">
<script src="<?= asset('assets/js/theme.js') ?>" defer></script>
```

### Permission Check in PHP
```php
// Helper function (src/Core/Helpers.php)
can('assessment.grading')  // bool

// In templates
<?php if (can('admin.users')): ?>
  <a href="<?= route('admin.users.index') ?>">Users</a>
<?php endif; ?>
```

### ModuleAccess Wildcards
```php
// ModuleAccess::$roleModuleMap
'admin' => ['admin.*', 'academic.*', 'assessment.*', 'communication.*']
// Matches: admin.users, academic.years, assessment.grading, etc.
```

### Route Helper
```php
route('admin.users.index')           // Named route
route('admin.users.edit', ['id' => 1])  // With parameters
url('admin/users')                   // Path only
asset('assets/css/app.css')          // Public asset with version
```

### Flash Messages
```php
// Set in controller
flash('success', 'Data berhasil disimpan');
flash('error', 'Terjadi kesalahan');

// Get in layout (auto-rendered in app.php, admin.php, etc.)
has_flash('success')
get_flash('success')
```

---

## 📁 FILE NAMING CONVENTIONS

| Type | Pattern | Example |
|------|---------|---------|
| Layout | `layouts/{name}.php` | `layouts/admin.php` |
| Partial | `partials/{kebab-case}.blade.php` | `partials/sidebar-nav.blade.php` |
| Question Partial | `partials/questions/{type}/{mode}.php` | `partials/questions/mcq/render.php` |
| Page/View | `{role}/{feature}/{action}.php` | `admin/users/index.php` |
| CSS | `assets/css/{name}.css` | `assets/css/design-system.css` |
| JS Component | `assets/js/components/{PascalCase}.js` | `assets/js/components/Modal.js` |
| JS Feature | `assets/js/{kebab-case}.js` | `assets/js/exam.js` |
| PHP Class | `src/{Namespace}/{PascalCase}.php` | `src/Support/ModuleAccess.php` |

---

## 🎨 DESIGN SYSTEM USAGE

### Colors (Semantic Tokens)
```css
/* Use these, NOT raw hex */
color: var(--color-primary);
background: var(--color-bg-primary);
border-color: var(--color-border);
color: var(--color-text-secondary);

/* Status colors */
color: var(--color-success);
color: var(--color-warning);
color: var(--color-danger);
color: var(--color-info);

/* Exam-specific */
--exam-timer-normal: var(--color-text-primary);
--exam-timer-warning: var(--color-warning);
--exam-timer-danger: var(--color-danger);
--exam-palette-unvisited: var(--color-border);
--exam-palette-visited: var(--color-primary);
--exam-palette-current: var(--color-primary);
--exam-palette-flagged: var(--color-warning);
--exam-question-answered: var(--color-success);
--exam-question-unanswered: var(--color-text-tertiary);
```

### Spacing Scale
```css
/* --space-1 = 4px, --space-2 = 8px, ... --space-12 = 48px */
padding: var(--space-4);        /* 16px */
margin: var(--space-2) var(--space-6);  /* 8px 24px */
gap: var(--space-3);            /* 12px */
```

### Typography
```css
font-family: var(--font-family);        /* Inter, system-ui fallback */
font-size: var(--text-base);            /* 1rem = 16px */
font-weight: var(--font-weight-medium); /* 500 */
line-height: var(--leading-normal);     /* 1.5 */
```

### Border Radius
```css
border-radius: var(--radius-sm);   /* 4px */
border-radius: var(--radius-md);   /* 8px */
border-radius: var(--radius-lg);   /* 12px */
border-radius: var(--radius-full); /* 9999px */
```

### Shadows
```css
box-shadow: var(--shadow-sm);   /* Subtle */
box-shadow: var(--shadow-md);   /* Cards */
box-shadow: var(--shadow-lg);   /* Modals, dropdowns */
box-shadow: var(--shadow-xl);   /* Toasts, popovers */
```

### Breakpoints (Mobile-First)
```css
/* Base: < 640px */
/* @media (min-width: 640px)  { }  -- sm */
/* @media (min-width: 768px)  { }  -- md */
/* @media (min-width: 1024px) { }  -- lg */
/* @media (min-width: 1280px) { }  -- xl */
```

### Utility Classes (Common)
```html
<!-- Flexbox -->
<div class="flex items-center justify-between gap-4">
<div class="flex-1">          <!-- flex: 1 1 0% -->
<div class="flex-col md:flex-row">

<!-- Spacing -->
<div class="p-4 md:p-6">
<div class="mx-auto">
<div class="gap-3">

<!-- Text -->
<span class="text-sm text-text-secondary font-medium">
<h1 class="text-2xl font-bold text-text-primary">

<!-- Display -->
<div class="hidden md:block">
<div class="invisible">

<!-- Sizing -->
<div class="w-full max-w-2xl">
<div class="h-12 min-h-[200px]">

<!-- Exam-specific -->
<div class="exam-timer exam-timer--warning">
<div class="exam-palette-item exam-palette-item--current exam-palette-item--answered">
```

---

## 🧩 COMPONENT PATTERNS

### Button
```html
<button class="btn btn--primary btn--md">Primary</button>
<button class="btn btn--secondary btn--sm">Secondary</button>
<button class="btn btn--ghost btn--lg">Ghost</button>
<button class="btn btn--danger btn--xs">Danger</button>
<button class="btn btn--outline btn--md">Outline</button>

<!-- Sizes: btn--xs, btn--sm, btn--md, btn--lg, btn--xl -->
<!-- States: :disabled, :hover, :focus-visible, .btn--loading -->
```

### Form Control
```html
<div class="form-group">
  <label class="form-label" for="email">Email</label>
  <input type="email" id="email" class="form-control" placeholder="user@domain.com" required>
  <span class="form-hint">We'll never share your email</span>
  <span class="form-error" aria-live="polite">Invalid email</span>
</div>

<!-- Validation states (added via JS or server-rendered) -->
<input class="form-control form-control--error">
<input class="form-control form-control--success">
<select class="form-control">
<textarea class="form-control" rows="4"></textarea>
```

### Table
```html
<div class="table-responsive">
  <table class="table table--striped table--hover">
    <thead>
      <tr>
        <th scope="col">Name</th>
        <th scope="col" class="text-right">Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>John Doe</td>
        <td class="text-right">
          <div class="btn-group">
            <a href="#" class="btn btn--ghost btn--sm">Edit</a>
            <button class="btn btn--danger btn--sm">Delete</button>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</div>
```

### Card
```html
<article class="card card--elevated">
  <div class="card-header">
    <h3 class="card-title">Exam Title</h3>
    <span class="badge badge--success">Active</span>
  </div>
  <div class="card-body">
    <p class="text-text-secondary">Description...</p>
  </div>
  <div class="card-footer">
    <a href="#" class="btn btn--primary btn--sm">Start</a>
  </div>
</article>
```

### Badge
```html
<span class="badge badge--primary">Primary</span>
<span class="badge badge--success">Success</span>
<span class="badge badge--warning">Warning</span>
<span class="badge badge--danger">Danger</span>
<span class="badge badge--info">Info</span>
<span class="badge badge--neutral">Neutral</span>
```

### Alert (Flash Messages)
```html
<div class="alert alert--success" role="alert">
  <svg class="alert-icon" aria-hidden="true">...</svg>
  <div class="alert-content">Success message</div>
  <button class="alert-dismiss" aria-label="Dismiss">×</button>
</div>
<!-- Variants: alert--success, alert--danger, alert--warning, alert--info -->
```

### Modal (JS Component)
```html
<!-- Trigger -->
<button data-modal-target="confirm-modal" class="btn btn--danger">Delete</button>

<!-- Modal -->
<div class="modal" id="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
  <div class="modal-backdrop" data-modal-close></div>
  <div class="modal-container">
    <header class="modal-header">
      <h3 id="modal-title" class="modal-title">Confirm Delete</h3>
      <button class="modal-close" data-modal-close aria-label="Close">×</button>
    </header>
    <div class="modal-body">Are you sure?</div>
    <footer class="modal-footer">
      <button class="btn btn--ghost" data-modal-close>Cancel</button>
      <button class="btn btn--danger" data-modal-confirm>Delete</button>
    </footer>
  </div>
</div>

<!-- JS -->
import Modal from '/assets/js/components/Modal.js';
new Modal(document.getElementById('confirm-modal'));
```

### Dropdown (Future)
```html
<div class="dropdown">
  <button class="dropdown-toggle btn btn--ghost" aria-expanded="false" aria-haspopup="true">
    Menu <svg class="dropdown-chevron">...</svg>
  </button>
  <div class="dropdown-menu" role="menu">
    <a class="dropdown-item" role="menuitem" href="#">Item 1</a>
    <a class="dropdown-item" role="menuitem" href="#">Item 2</a>
    <hr class="dropdown-divider">
    <a class="dropdown-item dropdown-item--danger" role="menuitem" href="#">Delete</a>
  </div>
</div>
```

### Toast (Future)
```javascript
import { toast } from '/assets/js/components/Toast.js';

toast.success('Berhasil disimpan');
toast.error('Terjadi kesalahan');
toast.warning('Perhatian!');
toast.info('Informasi baru');

// Options
toast.success('Saved', {
  duration: 5000,
  position: 'top-right',
  action: { label: 'Undo', onClick: () => {} }
});
```

---

## 🧭 NAVIGATION PATTERNS

### Admin Sidebar (sidebar-nav.blade.php)
```php
// Structure: sections → items with permissions
$navItems = [
    'dashboard' => ['label' => 'Dashboard', 'icon' => 'home', 'route' => 'admin.dashboard', 'permission' => 'core'],
    'academic' => [
        'label' => 'Akademik', 'icon' => 'graduation-cap', 'permission' => 'academic',
        'children' => [
            ['label' => 'Tahun Ajaran', 'route' => 'admin.academic-years.index', 'permission' => 'academic.years'],
            // ...
        ]
    ],
    // ...
];

// Render: recursive function handles nested <details>/<summary>
// Active state: request()->routeIs('admin.users.index*')
```

### Breadcrumbs
```php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Users', 'url' => route('admin.users.index')],
    ['label' => 'Edit User'],  // current (no url)
];
```

---

## ⚙️ MODULE ACCESS MATRIX

| Role | Modules (Wildcard = all sub-modules) |
|------|--------------------------------------|
| `super_admin` | `*` (everything) |
| `admin` | `admin.*`, `academic.*`, `assessment.*`, `communication.*` |
| `administrasi` | `academic.*`, `assessment.exams`, `assessment.results`, `communication.announcements` |
| `guru` | `assessment.exams`, `assessment.grading`, `academic.questions`, `academic.classes` |
| `kepala_sekolah` | `assessment.results`, `assessment.analytics`, `assessment.reports`, `assessment.certificates`, `academic.*` (read) |
| `proktor` | `assessment.proctoring` |
| `murid` | `assessment.exams` (take only), `assessment.results` (own) |
| `orang_tua` | `assessment.results` (child), `assessment.certificates` (child) |

---

## 📝 QUESTION PARTIAL CONVENTIONS

### File Structure (21 files total)
```
partials/questions/
├── mcq/
│   ├── create.php      # Teacher: create new MCQ
│   ├── edit.php        # Teacher: edit existing MCQ
│   └── render.php      # Student: take MCQ in exam
├── essay/
│   ├── create.php
│   ├── edit.php
│   └── render.php
├── truefalse/
│   ├── create.php
│   ├── edit.php
│   └── render.php
├── fillblank/
│   ├── create.php
│   ├── edit.php
│   └── render.php
├── matching/
│   ├── create.php
│   ├── edit.php
│   └── render.php
├── ordering/
│   ├── create.php
│   ├── edit.php
│   └── render.php
└── coding/
    ├── create.php
    ├── edit.php
    └── render.php
```

### Render Mode Contract (Student Taking Exam)
```php
// Input: $question (array/object with: id, type, question_text, options, points, etc.)
// Output: HTML with:
//   - data-question-id="<?= $question['id'] ?>"
//   - data-question-type="<?= $question['type'] ?>"
//   - name="answers[<?= $question['id'] ?>]" on inputs
//   - class="question-card" wrapper
//   - Accessible labels, ARIA attributes
```

### Create/Edit Mode Contract (Teacher)
```php
// Input: $question (null for create, populated for edit), $examId
// Output: Form HTML with:
//   - action="<?= route('admin.questions.store') ?>" or update route
//   - method="POST" with _method=PUT for edit
//   - CSRF field
//   - Dynamic option management (add/remove/reorder)
//   - Validation error display (via $errors bag)
//   - Preview toggle (optional)
```

### Data Schema per Type
```php
// MCQ
$question = [
    'type' => 'mcq',
    'question_text' => 'What is 2+2?',
    'options' => [
        ['id' => 'opt_1', 'text' => '3', 'is_correct' => false],
        ['id' => 'opt_2', 'text' => '4', 'is_correct' => true],
        ['id' => 'opt_3', 'text' => '5', 'is_correct' => false],
    ],
    'points' => 10,
    'difficulty' => 'easy',
    'explanation' => '2+2=4',
];

// Essay
$question = [
    'type' => 'essay',
    'question_text' => 'Explain photosynthesis',
    'rubric' => [
        ['criterion' => 'Accuracy', 'max_points' => 10, 'description' => 'Correct process'],
        ['criterion' => 'Detail', 'max_points' => 5, 'description' => 'Mentions light/calvin cycle'],
    ],
    'word_limit' => 500,
    'points' => 15,
];

// TrueFalse
$question = [
    'type' => 'truefalse',
    'question_text' => 'The earth is flat',
    'correct_answer' => 'false',
    'explanation' => 'Earth is an oblate spheroid',
    'points' => 5,
];

// FillBlank
$question = [
    'type' => 'fillblank',
    'question_text' => 'The capital of France is [[blank1]]. It is known for [[blank2]].',
    'blanks' => [
        ['index' => 1, 'answer' => 'Paris', 'case_sensitive' => false],
        ['index' => 2, 'answer' => 'Eiffel Tower', 'case_sensitive' => false],
    ],
    'points' => 10,
];

// Matching
$question = [
    'type' => 'matching',
    'pairs' => [
        ['left_id' => 'L1', 'left_text' => 'HTML', 'right_id' => 'R1', 'right_text' => 'Markup Language'],
        ['left_id' => 'L2', 'left_text' => 'CSS', 'right_id' => 'R2', 'right_text' => 'Styling'],
        ['left_id' => 'L3', 'left_text' => 'JS', 'right_id' => 'R3', 'right_text' => 'Programming'],
    ],
    'points' => 15,
];

// Ordering
$question = [
    'type' => 'ordering',
    'items' => [
        ['id' => 'I1', 'text' => 'Wake up', 'correct_order' => 1],
        ['id' => 'I2', 'text' => 'Brush teeth', 'correct_order' => 2],
        ['id' => 'I3', 'text' => 'Eat breakfast', 'correct_order' => 3],
        ['id' => 'I4', 'text' => 'Go to school', 'correct_order' => 4],
    ],
    'points' => 10,
];

// Coding
$question = [
    'type' => 'coding',
    'language' => 'python',
    'starter_code' => 'def solve():\n    # Write your code here\n    pass',
    'test_cases' => [
        ['input' => '2 3\n', 'expected_output' => '5\n', 'hidden' => false],
        ['input' => '10 20\n', 'expected_output' => '30\n', 'hidden' => true],
    ],
    'time_limit' => 2,  // seconds
    'memory_limit' => 256,  // MB
    'points' => 20,
];
```

---

## 🔧 COMMON HELPERS (src/Core/Helpers.php)

```php
// Escaping
h($value)                    // htmlspecialchars
h_attr($value)               // for HTML attributes
h_js($value)                 // for JavaScript strings

// URLs
asset('assets/css/app.css')  // /assets/css/app.css?v=123
route('admin.users.index')   // /admin/users
route('admin.users.edit', ['id' => 1])  // /admin/users/1/edit
url('admin/dashboard')       // /admin/dashboard
redirect('admin/users')      // RedirectResponse

// Auth
auth()                       // User|null
is_admin()                   // bool
is_teacher()                 // bool
is_student()                 // bool
can($module)                 // bool (via ModuleAccess)

// Flash
flash('success', 'Message')
has_flash('success')
get_flash('success')

// Config
config('app.name')
config('school.primary_color', '#default')

// Request
request()->get('key')
request()->post('key')
request()->routeIs('admin.users.*')
request()->ajax()

// Time
now()                        // Carbon instance
format_date($date, 'd M Y')
format_time($date, 'H:i')

// Array
arr_get($array, 'key.subkey', 'default')
arr_set($array, 'key.subkey', 'value')
```

---

## 🚀 DEPLOYMENT CHECKLIST

### Environment (.env)
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://cbt.example.com

DB_HOST=127.0.0.1
DB_DATABASE=cbt_school
DB_USERNAME=cbt_user
DB_PASSWORD=secure_password

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

SCHOOL_NAME="SMA Negeri 1 Contoh"
SCHOOL_SHORT_NAME="SMAN1"
SCHOOL_PRIMARY_COLOR="#1e40af"
SCHOOL_SECONDARY_COLOR="#059669"
SCHOOL_LOGO_URL="/assets/images/logo.png"
SCHOOL_FAVICON_URL="/favicon.ico"
SCHOOL_RTL=false
```

### Nginx Config (Key Parts)
```nginx
server {
    listen 443 ssl http2;
    server_name cbt.example.com;
    root /var/www/cbt-school/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/cbt.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/cbt.example.com/privkey.pem;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    # Static assets - long cache
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff2?)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # PHP
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    # SPA-like fallback for exam routes (if needed)
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Block sensitive files
    location ~ /\.(env|git|htaccess) { deny all; }
    location ~ /(config|database|src|templates|tests|storage)/ { deny all; }
}
```

### PHP-FPM Pool (www.conf)
```ini
[www]
user = www-data
group = www-data
listen = /run/php/php8.2-fpm.sock
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
request_terminate_timeout = 300
php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 300
php_admin_value[post_max_size] = 20M
php_admin_value[upload_max_filesize] = 20M
```

---

## 🐛 DEBUGGING TIPS

### Enable Debug Mode
```php
// .env
APP_DEBUG=true

// In code
if (config('app.debug')) {
    dump($variable);  // Symfony VarDumper style
    dd($variable);    // dump + die
}
```

### Common Issues

| Symptom | Likely Cause | Fix |
|---------|--------------|-----|
| Styles not applying | `design-system.css` not loaded | Check layout injection order |
| School config not applied | `theme.js` runs before `SCHOOL_CONFIG` | Ensure `<script>window.SCHOOL_CONFIG</script>` BEFORE `<link design-system.css>` |
| Permission denied | `can()` returns false | Check `ModuleAccess::$roleModuleMap` for role/module |
| Exam timer not working | `exam.js` not loaded | Check `layout-exam.php` includes exam.js |
| Auto-save failing | CSRF token mismatch | Ensure `csrf_token()` in meta tag, `exam.js` reads it |
| Modal not opening | JS error | Check console, `Modal.js` imported correctly |

### Useful Console Commands (if artisan-like CLI added)
```bash
php scripts/migrate.php          # Run migrations
php scripts/seed.php             # Seed database
php scripts/clear-cache.php      # Clear compiled views, config cache
php scripts/backup.php           # Backup database
php scripts/optimize.php         # Optimize autoloader, cache routes
```

---

## 📚 REFERENCE LINKS

- **Design System Spec:** `public/assets/css/design-system.css` (search `@layer tokens`)
- **Theme JS:** `public/assets/js/theme.js`
- **Exam JS:** `public/assets/js/exam.js`
- **ModuleAccess:** `src/Support/ModuleAccess.php`
- **Helpers:** `src/Core/Helpers.php`
- **Layouts:** `templates/layouts/*.php`
- **Partials:** `templates/partials/*.blade.php`
- **Audit Checkpoint:** `AUDIT_CHECKPOINT.md`
- **Architecture:** `ARCHITECTURE.md`
- **Diagrams:** `docs/diagrams.mmd`

---

## 🔄 UPDATE PROTOCOL

**After EVERY completed task:**
1. Update `AUDIT_CHECKPOINT.md` — move item from PENDING → COMPLETED
2. Update `ARCHITECTURE.md` if architecture changed
3. Update `docs/diagrams.mmd` if new components/flows added
4. Update this `docs/knowledge-base.md` if new patterns discovered
5. Commit with message: `feat: <description> [checkpoint-update]`

**Before STARTING new task:**
1. Read `AUDIT_CHECKPOINT.md` — confirm current state
2. Check `ARCHITECTURE.md` for relevant patterns
3. Check this file for conventions

---

**Next Knowledge Base Update:** After Question Partials Phase 1 — will add rendering pipeline details and question type registry pattern.