# CBT-School Architecture Document
**Version:** 1.0  
**Last Updated:** 2026-10-04  
**Project Root:** `G:/My Drive/CBT-School`

---

## 🏗️ HIGH-LEVEL ARCHITECTURE

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENT (Browser)                         │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐             │
│  │   Admin     │  │  Teacher    │  │  Student    │             │
│  │  Dashboard  │  │  Dashboard  │  │  Dashboard  │             │
│  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘             │
└─────────┼────────────────┼────────────────┼────────────────────┘
          │                │                │
          ▼                ▼                ▼
┌─────────────────────────────────────────────────────────────────┐
│                     NGINX / PHP-FPM                             │
│                         (public/index.php)                      │
└────────────────────────────────┬────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────┐
│                      CORE FRAMEWORK (src/Core)                  │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐           │
│  │  App     │ │ Router   │ │ Request  │ │ Response │           │
│  │  Config  │ │ Database │ │ Session  │ │ Validator│           │
│  │  View    │ │ Csrf     │ │ Helpers  │ │          │           │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘           │
└────────────────────────────────┬────────────────────────────────┘
                                 │
         ┌───────────────────────┼───────────────────────┐
         ▼                       ▼                       ▼
┌─────────────────┐   ┌─────────────────┐   ┌─────────────────┐
│   Auth Module   │   │  ModuleAccess   │   │   Controllers   │
│  (src/Auth)     │   │ (src/Support)   │   │ (per-module)    │
└────────┬────────┘   └────────┬────────┘   └────────┬────────┘
         │                     │                     │
         ▼                     ▼                     ▼
┌─────────────────────────────────────────────────────────────────┐
│                      TEMPLATES (MVC View Layer)                 │
│  layouts/      partials/      admin/      teacher/   student/   │
│  ├ admin.php   ├ sidebar-    ├ dashboard   ├ dashboard  ├ dash│
│  ├ exam.php    │ nav.blade   ├ users/      ├ questions  │ boar│
│  ├ auth.php    ├ questions/  ├ roles/      ├ exams/     │ d   │
│  ├ public.php  │ (21 files)  ├ settings/   ├ grading/   │     │
│  └ app.php     └ ...         └ ...         └ ...        │     │
└────────────────────────────────┬────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────┐
│                      PUBLIC ASSETS                              │
│  css/design-system.css    js/theme.js      js/exam.js          │
│  css/app.css              js/components/     js/components/    │
│                                                           Modal.js│
└─────────────────────────────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────┐
│                        DATABASE (MySQL)                         │
│  users, roles, permissions, academic_years, semesters,         │
│  classes, subjects, exams, questions, exam_sessions,           │
│  answers, results, certificates, activity_logs, settings       │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔐 AUTHORIZATION FLOW (ModuleAccess)

```
Request → Controller → can($module) / @can($module)
                    │
                    ▼
         ┌────────────────────────┐
         │   ModuleAccess.php     │
         │                        │
         │  $roleModuleMap = [    │
         │    'super_admin' => ['*'],           │
         │    'admin'       => ['admin.*',      │
         │                        'academic.*', │
         │                        'assessment.*',│
         │                        'communication.*'],│
         │    'guru'        => ['assessment.exams',│
         │                        'assessment.grading',│
         │                        'academic.questions',│
         │                        'academic.classes'],│
         │    ...                                │
         │  ]                                    │
         │                                       │
         │  bladeCan($module):                   │
         │    $user = auth();                   │
         │    $role = $user->getRole()->name;   │
         │    return can($role, $module);       │
         └────────────────────────┘
                    │
                    ▼
         Wildcard match: assessment.* → assessment.grading ✅
                    │
                    ▼
         Returns: true / false
```

---

## 🎨 DESIGN SYSTEM ARCHITECTURE

```
design-system.css (Single Source of Truth)
│
├── 1. TOKENS (CSS Custom Properties)
│   ├── Colors: --color-primary, --color-success, --color-danger, etc.
│   ├── Spacing: --space-1..--space-12
│   ├── Typography: --font-family, --text-xs..--text-4xl
│   ├── Radius: --radius-sm..--radius-full
│   ├── Shadows: --shadow-sm..--shadow-xl
│   ├── Z-Index: --z-dropdown..--z-toast
│   ├── Transitions: --transition-fast..--transition-slow
│   ├── Exam-specific: --exam-timer-*, --exam-palette-*, --exam-question-*
│   └── School Config Override: injected via theme.js → SCHOOL_CONFIG
│
├── 2. RESET & BASE
│   ├── CSS Reset (modern-normalize style)
│   ├── Typography base
│   └── Focus visible
│
├── 3. LAYOUTS (4 variants)
│   ├── .layout-admin      → admin.php
│   ├── .layout-exam       → exam.php
│   ├── .layout-auth       → auth.php
│   ├── .layout-public     → public.php
│   └── .layout-app        → app.php (Student/Teacher)
│
├── 4. COMPONENTS
│   ├── Buttons (.btn, .btn--*, .btn-group)
│   ├── Forms (.form-control, .form-label, .input-group, validation states)
│   ├── Tables (.table, .table--striped, .table--hover, .table--responsive)
│   ├── Cards (.card, .card--elevated, .card--outlined)
│   ├── Badges (.badge, .badge--*)
│   ├── Alerts (.alert, .alert--*)
│   ├── Modals (.modal, .modal--*)
│   ├── Dropdowns (.dropdown, .dropdown-menu)
│   ├── Toasts (.toast, .toast--*)
│   ├── Tabs (.tabs, .tab-list, .tab-panel)
│   ├── Accordion (.accordion)
│   ├── Tooltip (.tooltip)
│   ├── Avatar (.avatar, .avatar--*)
│   ├── Breadcrumbs (.breadcrumbs)
│   ├── Pagination (.pagination)
│   ├── Progress (.progress, .progress--circular)
│   ├── Sidebar (.sidebar, .sidebar-nav, .sidebar__item)
│   ├── Header (.header)
│   ├── Footer (.footer)
│   ├── Exam Timer (.exam-timer)
│   ├── Exam Palette (.exam-palette, .palette-item)
│   ├── Question Card (.question-card)
│   └── Certificate (.certificate)
│
├── 5. UTILITIES
│   ├── Display, Flexbox, Grid
│   ├── Spacing (m-*, p-*)
│   ├── Text (color, weight, alignment, truncate)
│   ├── Visibility (hidden, invisible, sr-only)
│   ├── Sizing (w-*, h-*, min/max)
│   ├── Borders, Radius, Shadows
│   ├── Position, Overflow
│   ├── Transitions, Animations
│   ├── Print styles
│   └── RTL support
│
└── 6. PAGE-SPECIFIC
    ├── Dashboard stats, charts
    ├── Login/Register forms
    ├── Exam taking (fullscreen, timer, palette)
    ├── Question types (MCQ, Essay, etc.)
    ├── Grading interface
    ├── Certificate preview
    └── Print styles

theme.js (Runtime Token Injection)
│
├── Reads window.SCHOOL_CONFIG (injected by layouts)
├── Applies to document.documentElement.style:
│   ├── --school-primary-color
│   ├── --school-secondary-color
│   ├── --school-logo-url
│   ├── --school-favicon-url
│   ├── --school-name
│   ├── --school-short-name
│   └── --school-rtl
├── Updates <title>, favicon, logo elements
└── Dispatches 'school-config-applied' event
```

---

## 📂 MODULE STRUCTURE

```
src/
├── Core/                    # Framework kernel (NO business logic)
│   ├── App.php             # Application bootstrap, container
│   ├── Config.php          # Configuration loader
│   ├── Database.php        # PDO wrapper, query builder
│   ├── Router.php          # Route registration, dispatching
│   ├── Request.php         # HTTP request wrapper
│   ├── Response.php        # HTTP response builder
│   ├── View.php            # Template rendering (plain PHP)
│   ├── Session.php         # Session management
│   ├── Csrf.php            # CSRF token generation/validation
│   ├── Validator.php       # Validation rules
│   └── Helpers.php         # Global helpers (can(), route(), asset(), etc.)
│
├── Auth/                   # Authentication & Authorization
│   ├── Auth.php            # Auth facade, login/logout, user()
│   ├── User.php            # User model, relationships
│   ├── Role.php            # Role model, permissions
│   └── Middleware/
│       ├── AuthMiddleware.php
│       ├── RoleMiddleware.php
│       └── PermissionMiddleware.php
│
├── Support/                # Cross-cutting concerns
│   └── ModuleAccess.php    # ✅ RBAC module-permission matrix
│
└── Modules/                # Business logic (future)
    ├── Academic/
    ├── Assessment/
    ├── Communication/
    └── Administration/
```

---

## 🌐 ROUTING STRUCTURE

```
public/index.php
  │
  ├─ GET  /                          → home.php (public layout)
  ├─ GET  /login                     → auth/login.php (auth layout)
  ├─ POST /login                     → AuthController@login
  ├─ GET  /register                  → auth/register.php
  ├─ POST /register                  → AuthController@register
  ├─ POST /logout                    → AuthController@logout
  │
  ├─ GROUP prefix=/admin middleware=[auth, role:admin]
  │   ├─ GET  /dashboard             → admin/dashboard.php
  │   ├─ RESOURCE /users             → UserController
  │   ├─ RESOURCE /roles             → RoleController
  │   ├─ RESOURCE /academic-years    → AcademicYearController
  │   ├─ RESOURCE /semesters         → SemesterController
  │   ├─ RESOURCE /classes           → ClassController
  │   ├─ RESOURCE /subjects          → SubjectController
  │   ├─ RESOURCE /exams             → ExamController
  │   ├─ RESOURCE /questions         → QuestionController
  │   ├─ RESOURCE /grading           → GradingController
  │   ├─ RESOURCE /proctoring        → ProctoringController
  │   ├─ RESOURCE /results           → ResultController
  │   ├─ RESOURCE /analytics         → AnalyticsController
  │   ├─ RESOURCE /certificates      → CertificateController
  │   ├─ RESOURCE /reports           → ReportController
  │   ├─ RESOURCE /announcements     → AnnouncementController
  │   ├─ RESOURCE /notifications     → NotificationController
  │   ├─ RESOURCE /settings          → SettingsController
  │   ├─ RESOURCE /activity-logs     → ActivityLogController
  │   └─ RESOURCE /backups           → BackupController
  │
  ├─ GROUP prefix=/teacher middleware=[auth, role:guru]
  │   ├─ GET  /dashboard             → teacher/dashboard.php
  │   ├─ RESOURCE /exams             → TeacherExamController
  │   ├─ RESOURCE /questions         → TeacherQuestionController
  │   ├─ RESOURCE /grading           → TeacherGradingController
  │   ├─ RESOURCE /classes           → TeacherClassController
  │   └─ GET  /profile               → teacher/profile.php
  │
  ├─ GROUP prefix=/student middleware=[auth, role:murid]
  │   ├─ GET  /dashboard             → student/dashboard.php
  │   ├─ GET  /exams                 → student/exams/index.php
  │   ├─ GET  /exams/{id}/take       → student/exam/take.php (exam layout)
  │   ├─ GET  /results               → student/results/index.php
  │   ├─ GET  /results/{id}          → student/results/show.php
  │   ├─ GET  /profile               → student/profile.php
  │   └─ GET  /certificates          → student/certificates/index.php
  │
  └─ GROUP prefix=/api (optional)
      ├─ POST /exam/{id}/save        → ExamController@autoSave
      ├─ POST /exam/{id}/submit      → ExamController@submit
      └─ GET  /exam/{id}/status      → ExamController@status
```

---

## 🗄️ DATABASE SCHEMA (Key Tables)

```sql
-- Users & RBAC
users (id, name, email, password, role_id, avatar, settings, last_login_at, created_at)
roles (id, name, display_name, description, is_system, created_at)
permissions (id, module, action, description)  -- e.g., 'assessment', 'grading', 'view'
role_permissions (role_id, permission_id)

-- Academic
academic_years (id, name, start_date, end_date, is_active, created_at)
semesters (id, academic_year_id, name, start_date, end_date, is_active, created_at)
classes (id, name, academic_year_id, semester_id, teacher_id, capacity, created_at)
subjects (id, code, name, description, created_at)
class_subjects (class_id, subject_id, teacher_id)
enrollments (id, student_id, class_id, academic_year_id, semester_id, status, enrolled_at)

-- Assessment
exams (id, title, description, subject_id, class_id, teacher_id, academic_year_id, semester_id,
       start_time, end_time, duration_minutes, shuffle_questions, shuffle_options,
       show_result, passing_grade, max_attempts, status, settings, created_at)
questions (id, exam_id, type, question_text, options, correct_answer, points, order_index,
           difficulty, tags, media, explanation, created_at)
exam_sessions (id, exam_id, student_id, started_at, ended_at, status, ip_address, user_agent,
               browser_fingerprint, proctor_data, created_at)
answers (id, session_id, question_id, answer_text, answer_data, is_correct, points_earned,
         graded_by, graded_at, created_at)
results (id, session_id, total_points, earned_points, percentage, grade, status, created_at)

-- Communication
announcements (id, title, content, target_roles, target_classes, published_at, expires_at, created_by)
notifications (id, user_id, type, title, message, data, read_at, created_at)

-- System
activity_logs (id, user_id, action, module, description, ip_address, user_agent, created_at)
settings (id, key, value, type, group, is_public, created_at, updated_at)
certificates (id, result_id, template_id, serial_number, issued_at, verified_at, pdf_path)
```

---

## 🔄 DATA FLOW: EXAM TAKING

```
Student clicks "Mulai Ujian"
       │
       ▼
GET /student/exams/{id}/take
       │
       ▼
ExamController@take()
  ├─ Validate: exam exists, student enrolled, time window open, attempts < max
  ├─ Create exam_sessions record (status: in_progress)
  ├─ Load questions (with shuffle if enabled)
  └─ Render exam.php layout + student/exam/take.php
       │
       ▼
Browser loads:
  ├─ design-system.css (exam tokens: timer, palette, question states)
  ├─ theme.js (applies school branding)
  ├─ exam.js (exam taking logic)
  └─ Modal.js (confirm dialogs)
       │
       ▼
exam.js initializes:
  ├─ Timer (countdown from exam.duration_minutes)
  ├─ Palette (question grid: 1..N, color-coded states)
  ├─ Navigation (prev/next, jump to question)
  ├─ Auto-save (debounced POST /api/exam/{id}/save)
  ├─ Keyboard shortcuts (Alt+1..9, Ctrl+Enter)
  ├─ Fullscreen enforcement (if configured)
  └─ Proctoring events (tab switch, resize, blur)
       │
       ▼
Student answers → exam.js stores in memory → auto-save to answers table
       │
       ▼
Student clicks "Selesai" OR timer reaches 0
       │
       ▼
POST /api/exam/{id}/submit
       │
       ▼
ExamController@submit()
  ├─ Update exam_sessions (status: submitted, ended_at)
  ├─ Grade auto-gradable questions (MCQ, TrueFalse, FillBlank, Matching, Ordering)
  ├─ Queue manual grading (Essay, Coding) → grading queue
  ├─ Calculate percentage, determine grade
  ├─ Create results record
  ├─ If passing → generate certificate (async)
  └─ Return result redirect
       │
       ▼
Student sees result page (student/results/show.php)
```

---

## 🎯 QUESTION TYPE ARCHITECTURE

```
Question Type Registry (future: src/Support/QuestionTypes.php)

Type: mcq (Multiple Choice)
  ├─ create.php:   Form with dynamic option rows, radio for correct, points, difficulty
  ├─ edit.php:     Pre-filled form, option reorder, delete
  ├─ render.php:   Radio group, one selection, keyboard navigation
  ├─ grading:      AUTO (exact match correct_answer)
  └─ schema:       options: [{id, text, is_correct}], correct_answer: option_id

Type: essay
  ├─ create.php:   Textarea + rubric criteria builder, word limit, points
  ├─ edit.php:     Same, pre-filled
  ├─ render.php:   Textarea (rich text optional), word counter, char counter
  ├─ grading:      MANUAL (teacher grading interface)
  └─ schema:       rubric: [{criterion, max_points, description}]

Type: truefalse
  ├─ create.php:   Statement + radio (True/False), explanation, points
  ├─ edit.php:     Pre-filled
  ├─ render.php:   Two large buttons (True/False), single select
  ├─ grading:      AUTO
  └─ schema:       correct_answer: 'true'|'false'

Type: fillblank
  ├─ create.php:   Text with [[blank]] placeholders, answer per blank, case-sensitive toggle
  ├─ edit.php:     Highlight blanks, edit answers
  ├─ render.php:   Inline inputs at blank positions, tab navigation
  ├─ grading:      AUTO (exact or case-insensitive match per blank)
  └─ schema:       blanks: [{index, answer, case_sensitive}]

Type: matching
  ├─ create.php:   Two columns (left/right), drag-drop to pair, points per pair
  ├─ edit.php:     Visual pair editor
  ├─ render.php:   Two columns, click to match, line drawing (SVG)
  ├─ grading:      AUTO (all pairs must match)
  └─ schema:       pairs: [{left_id, left_text, right_id, right_text}]

Type: ordering
  ├─ create.php:   List items, drag-drop to order, points
  ├─ edit.php:     Reorder
  ├─ render.php:   Draggable list (touch + mouse), submit order
  ├─ grading:      AUTO (exact sequence) or PARTIAL (Kendall tau)
  └─ schema:       items: [{id, text, correct_order}]

Type: coding
  ├─ create.php:   Monaco editor config: language, starter code, test cases (hidden/visible)
  ├─ edit.php:     Edit code, test cases, language
  ├─ render.php:   Monaco editor (read-only starter), run button, output panel
  ├─ grading:      AUTO (run hidden test cases) + MANUAL review
  └─ schema:       language, starter_code, test_cases: [{input, expected_output, hidden}]
```

---

## 📦 DEPLOYMENT ARCHITECTURE

```
┌─────────────────────────────────────────────────────────────┐
│                      PRODUCTION                             │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐  │
│  │   Nginx      │    │  PHP-FPM     │    │   MySQL 8.0  │  │
│  │   (SSL)      │◄───│  (pool:      │◄───│   (InnoDB)   │  │
│  │   Port 443   │    │   www-data)  │    │   Port 3306  │  │
│  └──────────────┘    └──────────────┘    └──────────────┘  │
│         │                    │                    │          │
│         ▼                    ▼                    ▼          │
│  /var/www/cbt-school  /var/www/cbt-school  /var/lib/mysql  │
│  (public/ as root)    (src/, templates/)   (cbt_school_db)  │
│                                                                     │
│  Assets:                                                          │
│  - design-system.css (cached, gzip, brotli)                      │
│  - theme.js, exam.js (ES modules, cached)                        │
│  - Logo/favicon via theme.js (CDN-ready)                         │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔧 CONFIGURATION

```
config/
├── app.php           # app.name, app.locale, app.version, app.debug
├── database.php      # mysql host, db, user, pass, charset, prefix
├── session.php       # driver, lifetime, secure, httponly, samesite
├── school.php        # school.name, school.short_name, school.logo, school.favicon,
                      # school.primary_color, school.secondary_color, school.rtl
├── exam.php          # default_duration, shuffle_default, proctoring_enabled,
                      # fullscreen_enforced, auto_save_interval
├── auth.php          # password_hash_algo, remember_token_ttl, lockout_threshold
└── filesystems.php   # local disk, upload paths, max_size, allowed_mimes
```

---

## 📈 SCALABILITY CONSIDERATIONS

| Layer | Current | Scale Target | Strategy |
|-------|---------|--------------|----------|
| Web | 1 PHP-FPM | N workers | Horizontal (stateless), shared session (Redis) |
| DB | 1 MySQL | Read replicas | Read/write split, connection pooling (ProxySQL) |
| Assets | Nginx static | CDN | CloudFlare / BunnyCDN, cache-control max-age=1y |
| Exam | In-memory JS | 1000+ concurrent | WebSocket for real-time proctoring, Redis pub/sub |
| Files | Local disk | S3-compatible | Flysystem adapter, signed URLs for certificates |

---

## 🧪 TESTING STRATEGY

```
tests/
├── Unit/
│   ├── Core/          # Router, Validator, Database, ModuleAccess
│   ├── Auth/          # User, Role, Auth facade
│   └── Support/       # ModuleAccess::can(), bladeCan()
├── Feature/
│   ├── Auth/          # Login, register, logout, password reset
│   ├── Admin/         # CRUD for each resource
│   ├── Teacher/       # Exam create, question bank, grading
│   ├── Student/       # Exam take, submit, result view
│   └── Exam/          # Timer, auto-save, palette, submit flow
├── Browser/           # Playwright/Puppeteer (exam taking E2E)
└── fixtures/          # Seed data for testing
```

---

## 📋 CODING STANDARDS

| Aspect | Standard |
|--------|----------|
| PHP | PSR-12, strict_types=1, PHP 8.2+ |
| CSS | BEM naming, CSS custom properties, mobile-first |
| JS | ES Modules, IIFE for components, no framework |
| Templates | Plain PHP, `h()` for escaping, `asset()` for assets |
| Database | PDO prepared statements, migrations for schema |
| Git | Conventional commits, feature branches, PR required |

---

## 🔗 EXTERNAL DEPENDENCIES

| Package | Purpose | Version |
|---------|---------|---------|
| vlucas/phpdotenv | Environment config | ^5.6 |
| firebase/php-jwt | JWT for API (if needed) | ^6.10 |
| monolog/monolog | Logging | ^3.0 |
| ramsey/uuid | UUID generation | ^4.7 |
| intervention/image | Image processing (avatars, certificates) | ^3.0 |

---

## 📝 CHANGE LOG (Architecture Decisions)

| Date | Change | File(s) Affected |
|------|--------|------------------|
| 2026-10-04 | Created ModuleAccess for RBAC | src/Support/ModuleAccess.php |
| 2026-10-04 | Updated can() helper to use ModuleAccess | src/Core/Helpers.php |
| 2026-10-04 | Created app.php layout (Student/Teacher) | templates/layouts/app.php |
| 2026-10-04 | Created sidebar-nav.blade.php partial | templates/partials/sidebar-nav.blade.php |
| 2026-10-04 | Refactored admin.php to use sidebar partial | templates/layouts/admin.php |
| 2026-10-04 | All 4 layouts inject SCHOOL_CONFIG + theme.js | templates/layouts/*.php |

---

**Next Architecture Update:** After Question Partials (Phase 1) — will add QuestionType registry and rendering pipeline.