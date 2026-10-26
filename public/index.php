<?php

declare(strict_types=1);

/**
 * CBT School - Front Controller
 * Single entry point for all HTTP requests
 */

// Define base path
define('BASE_PATH', __DIR__ . '/..');

// Load configuration first
require_once BASE_PATH . '/src/Core/Config.php';
\Core\Config::getInstance(BASE_PATH . '/.env');

// Load helpers
require_once BASE_PATH . '/src/Core/Helpers.php';

// Load core classes
require_once BASE_PATH . '/src/Core/App.php';
require_once BASE_PATH . '/src/Core/Database.php';
require_once BASE_PATH . '/src/Core/Session.php';
require_once BASE_PATH . '/src/Core/Csrf.php';
require_once BASE_PATH . '/src/Core/Validator.php';
require_once BASE_PATH . '/src/Core/View.php';
require_once BASE_PATH . '/src/Core/Request.php';
require_once BASE_PATH . '/src/Core/Response.php';
require_once BASE_PATH . '/src/Core/Router.php';

// Load Auth
require_once BASE_PATH . '/src/Auth/Auth.php';
require_once BASE_PATH . '/src/Auth/User.php';
require_once BASE_PATH . '/src/Auth/Role.php';
require_once BASE_PATH . '/src/Auth/Middleware/AuthMiddleware.php';
require_once BASE_PATH . '/src/Auth/Middleware/GuestMiddleware.php';
require_once BASE_PATH . '/src/Auth/Middleware/RoleMiddleware.php';
require_once BASE_PATH . '/src/Auth/Middleware/PermissionMiddleware.php';

// Initialize App
$app = \Core\App::getInstance(BASE_PATH);
$router = $app->getRouter();

// Global view data
\Core\View::share('appName', config('app.name'));
\Core\View::share('currentUser', auth());
\Core\View::share('currentRoute', $app->getRequest()->getPathInfo());

// ==================== PUBLIC ROUTES ====================

$router->get('/', function () {
    return \Core\Response::view('home');
})->name('home');

$router->get('/login', function () {
    return \Core\Response::view('auth/login');
})->name('login')->middleware(\Auth\Middleware\GuestMiddleware::class);

$router->post('/login', function () {
    $request = \Core\App::getInstance()->getRequest();
    $email = $request->input('email');
    $password = $request->input('password');
    $remember = $request->input('remember', false);

    $validator = \Core\validator($request->all(), [
        'email' => 'required|string',
        'password' => 'required|string',
    ]);

    if ($validator->fails()) {
        \Core\Session::flashInput($request->all());
        \Core\Session::flash('error', 'Email dan kata sandi wajib diisi.');
        return \Core\Response::back();
    }

    if (\Auth\Auth::getInstance()->attempt($email, $password, $remember)) {
        $user = \Auth\Auth::getInstance()->user();
        $role = $user?->getRole()?->slug;

        return match ($role) {
            'admin' => \Core\Response::redirect(url('admin/dashboard')),
            'teacher' => \Core\Response::redirect(url('teacher/dashboard')),
            'student' => \Core\Response::redirect(url('student/dashboard')),
            default => \Core\Response::redirect(url('/')),
        };
    }

    \Core\Session::flashInput($request->all());
    \Core\Session::flash('error', 'Email atau kata sandi salah.');
    return \Core\Response::back();
})->name('login.post');

$router->get('/forgot-password', function () {
    return \Core\Response::view('auth/forgot-password', ['title' => 'Lupa Kata Sandi']);
})->name('forgot-password')->middleware(\Auth\Middleware\GuestMiddleware::class);

$router->post('/forgot-password', function () {
    $request = \Core\App::getInstance()->getRequest();
    $email = $request->input('email');

    $validator = \Core\validator($request->all(), [
        'email' => 'required|email',
    ]);

    if ($validator->fails()) {
        \Core\Session::flashInput($request->all());
        \Core\Session::flash('error', 'Email tidak valid.');
        return \Core\Response::back();
    }

    if (\Auth\Auth::getInstance()->sendPasswordResetLink($email)) {
        \Core\Session::flash('success', 'Tautan reset kata sandi telah dikirim ke email Anda.');
    } else {
        \Core\Session::flash('error', 'Email tidak ditemukan.');
    }
    return \Core\Response::back();
})->name('forgot-password.post');

$router->get('/reset-password', function () {
    $request = \Core\App::getInstance()->getRequest();
    $email = $request->query('email');
    $token = $request->query('token');

    if (!$email || !$token || !\Auth\Auth::getInstance()->validateResetToken($email, $token)) {
        \Core\Session::flash('error', 'Tautan reset tidak valid atau telah kadaluarsa.');
        return \Core\Response::redirect(url('forgot-password'));
    }

    return \Core\Response::view('auth/reset-password', [
        'title' => 'Reset Kata Sandi',
        'email' => $email,
        'token' => $token
    ]);
})->name('reset-password')->middleware(\Auth\Middleware\GuestMiddleware::class);

$router->post('/reset-password', function () {
    $request = \Core\App::getInstance()->getRequest();
    $email = $request->input('email');
    $token = $request->input('token');
    $password = $request->input('password');
    $passwordConfirmation = $request->input('password_confirmation');

    $validator = \Core\validator($request->all(), [
        'email' => 'required|email',
        'token' => 'required|string',
        'password' => 'required|string|min:8|confirmed',
    ]);

    if ($validator->fails()) {
        \Core\Session::flashInput($request->all());
        \Core\Session::flash('error', $validator->first('password'));
        return \Core\Response::back();
    }

    if (\Auth\Auth::getInstance()->resetPassword($email, $token, $password)) {
        \Core\Session::flash('success', 'Kata sandi berhasil direset. Silakan login.');
        return \Core\Response::redirect(url('login'));
    }

    \Core\Session::flash('error', 'Tautan reset tidak valid atau telah kadaluarsa.');
    return \Core\Response::back();
})->name('reset-password.post');

$router->post('/logout', function () {
    \Auth\Auth::getInstance()->logout();
    return \Core\Response::redirect(url('login'));
})->name('logout');

// ==================== ADMIN ROUTES ====================

$router->group(['prefix' => 'admin', 'middleware' => [\Auth\Middleware\AuthMiddleware::class, \Auth\Middleware\RoleMiddleware::class, \Auth\Middleware\PermissionMiddleware::class], 'as' => 'admin.'], function ($router) {
    $router->get('/dashboard', function () {
        $db = \Core\Database::getInstance();
        $stats = [
            'users' => $db->table('users')->count(),
            'teachers' => $db->table('users')->where('role_id', '=', 2)->count(),
            'students' => $db->table('users')->where('role_id', '=', 3)->count(),
            'exams' => $db->table('exams')->count(),
            'questions' => $db->table('questions')->count(),
            'active_exams' => $db->table('exams')->where('status', '=', 'published')->count(),
        ];

        $recentUsers = $db->table('users')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->select(['users.*', 'roles.name as role_name'])
            ->orderBy('users.created_at', 'desc')
            ->limit(5)
            ->get();

        $recentExams = $db->table('exams')
            ->leftJoin('subjects', 'exams.subject_id', '=', 'subjects.id')
            ->leftJoin('users', 'exams.teacher_id', '=', 'users.id')
            ->select(['exams.*', 'subjects.name as subject_name', 'users.name as teacher_name'])
            ->orderBy('exams.created_at', 'desc')
            ->limit(5)
            ->get();

        return \Core\Response::view('admin/dashboard', [
            'title' => 'Dashboard Admin',
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentExams' => $recentExams,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => null]
            ]
        ]);
    })->name('dashboard');

    // Users
    $router->get('/users', function () {
        $request = \Core\App::getInstance()->getRequest();
        $page = (int) $request->query('page', 1);
        $search = $request->query('search', '');
        $role = $request->query('role', '');
        $status = $request->query('status', '');

        $users = \Auth\User::paginate(15, $page, compact('search', 'role', 'status'));
        $roles = \Auth\Role::all();

        return \Core\Response::view('admin/users/index', [
            'title' => 'Kelola Pengguna',
            'users' => $users,
            'roles' => $roles,
            'filters' => compact('search', 'role', 'status'),
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('admin/dashboard')],
                ['label' => 'Pengguna', 'url' => null]
            ]
        ]);
    })->name('users');

    $router->get('/users/create', function () {
        $roles = \Auth\Role::all();
        return \Core\Response::view('admin/users/create', [
            'title' => 'Tambah Pengguna',
            'roles' => $roles,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('admin/dashboard')],
                ['label' => 'Pengguna', 'url' => url('admin/users')],
                ['label' => 'Tambah', 'url' => null]
            ]
        ]);
    })->name('users.create');

    $router->post('/users', function () {
        $request = \Core\App::getInstance()->getRequest();
        $data = $request->only(['name', 'email', 'password', 'role_id', 'phone', 'is_active']);

        $validator = \Core\validator($data, [
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            \Core\Session::flashInput($data);
            \Core\Session::flash('error', $validator->first('email') ?: $validator->first('password') ?: 'Validasi gagal');
            return \Core\Response::back();
        }

        $user = \Auth\User::create($data);

        // Create profile if student
        $role = \Auth\Role::find($data['role_id']);
        if ($role && $role->slug === 'student') {
            \Core\Database::getInstance()->table('user_profiles')->insert([
                'user_id' => $user->id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        \Core\Session::flash('success', 'Pengguna berhasil dibuat.');
        return \Core\Response::redirect(url('admin/users'));
    })->name('users.store');

    $router->get('/users/{id}/edit', function ($id) {
        $user = \Auth\User::findOrFail($id);
        $roles = \Auth\Role::all();
        return \Core\Response::view('admin/users/edit', [
            'title' => 'Edit Pengguna',
            'user' => $user,
            'roles' => $roles,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('admin/dashboard')],
                ['label' => 'Pengguna', 'url' => url('admin/users')],
                ['label' => 'Edit', 'url' => null]
            ]
        ]);
    })->name('users.edit');

    $router->put('/users/{id}', function ($id) {
        $request = \Core\App::getInstance()->getRequest();
        $user = \Auth\User::findOrFail($id);
        $data = $request->only(['name', 'email', 'role_id', 'phone', 'is_active']);

        if ($request->filled('password')) {
            $data['password'] = $request->input('password');
        }

        $validator = \Core\validator($data, [
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            \Core\Session::flashInput($data);
            \Core\Session::flash('error', $validator->first('email') ?: 'Validasi gagal');
            return \Core\Response::back();
        }

        $user->update($data);
        \Core\Session::flash('success', 'Pengguna berhasil diperbarui.');
        return \Core\Response::redirect(url('admin/users'));
    })->name('users.update');

    $router->delete('/users/{id}', function ($id) {
        $user = \Auth\User::findOrFail($id);
        if ($user->id === auth()->id) {
            return \Core\Response::json(['message' => 'Tidak dapat menghapus akun sendiri'], 422);
        }
        $user->delete();
        return \Core\Response::json(['message' => 'Pengguna dihapus']);
    })->name('users.destroy');

    // Roles
    $router->get('/roles', function () {
        $roles = \Auth\Role::all();
        return \Core\Response::view('admin/roles/index', [
            'title' => 'Peran & Hak Akses',
            'roles' => $roles,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('admin/dashboard')],
                ['label' => 'Peran', 'url' => null]
            ]
        ]);
    })->name('roles');

    $router->post('/roles', function () {
        $request = \Core\App::getInstance()->getRequest();
        $data = $request->only(['name', 'slug', 'description', 'permissions']);

        $validator = \Core\validator($data, [
            'name' => 'required|string|max:100',
            'slug' => 'required|alpha_dash|max:50|unique:roles,slug',
            'description' => 'nullable|string',
            'permissions' => 'array',
        ]);

        if ($validator->fails()) {
            \Core\Session::flashInput($data);
            \Core\Session::flash('error', $validator->first('name') ?: 'Validasi gagal');
            return \Core\Response::back();
        }

        $data['permissions'] = $data['permissions'] ?? [];
        \Auth\Role::create($data);
        \Core\Session::flash('success', 'Peran berhasil dibuat.');
        return \Core\Response::redirect(url('admin/roles'));
    })->name('roles.store');

    $router->put('/roles/{id}', function ($id) {
        $request = \Core\App::getInstance()->getRequest();
        $role = \Auth\Role::findOrFail($id);
        $data = $request->only(['name', 'description', 'permissions']);

        if ($role->slug === 'admin') {
            return \Core\Response::json(['message' => 'Peran admin tidak dapat diubah'], 422);
        }

        $validator = \Core\validator($data, [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'permissions' => 'array',
        ]);

        if ($validator->fails()) {
            \Core\Session::flashInput($data);
            \Core\Session::flash('error', 'Validasi gagal');
            return \Core\Response::back();
        }

        $data['permissions'] = $data['permissions'] ?? [];
        $role->update($data);
        \Core\Session::flash('success', 'Peran berhasil diperbarui.');
        return \Core\Response::redirect(url('admin/roles'));
    })->name('roles.update');

    $router->delete('/roles/{id}', function ($id) {
        $role = \Auth\Role::findOrFail($id);
        if (in_array($role->slug, ['admin', 'teacher', 'student'])) {
            return \Core\Response::json(['message' => 'Peran sistem tidak dapat dihapus'], 422);
        }
        $role->delete();
        return \Core\Response::json(['message' => 'Peran dihapus']);
    })->name('roles.destroy');

    // Academic Years
    $router->resource('academic-years', 'Admin\AcademicYearController');
    $router->resource('semesters', 'Admin\SemesterController');
    $router->resource('classes', 'Admin\ClassController');
    $router->resource('subjects', 'Admin\SubjectController');

    // Settings
    $router->get('/settings', function () {
        return \Core\Response::view('admin/settings/index', [
            'title' => 'Pengaturan Sistem',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('admin/dashboard')],
                ['label' => 'Pengaturan', 'url' => null]
            ]
        ]);
    })->name('settings');

    // Activity Logs
    $router->get('/activity-logs', function () {
        $request = \Core\App::getInstance()->getRequest();
        $page = (int) $request->query('page', 1);
        $logs = \Core\Database::getInstance()->table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->select(['activity_logs.*', 'users.name as user_name'])
            ->orderBy('activity_logs.created_at', 'desc')
            ->paginate(25, $page);

        return \Core\Response::view('admin/activity-logs/index', [
            'title' => 'Log Aktivitas',
            'logs' => $logs,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('admin/dashboard')],
                ['label' => 'Log Aktivitas', 'url' => null]
            ]
        ]);
    })->name('activity-logs');

    // ==================== ADMIN EXAM MANAGEMENT ====================

    // Exams Index
    $router->get('/exams', function () {
        $request = \Core\App::getInstance()->getRequest();
        $page = (int) $request->query('page', 1);
        $search = $request->query('search', '');
        $status = $request->query('status', '');
        $subject = $request->query('subject', '');

        $query = \Core\Database::getInstance()->table('exams')
            ->leftJoin('subjects', 'exams.subject_id', '=', 'subjects.id')
            ->leftJoin('users', 'exams.teacher_id', '=', 'users.id')
            ->leftJoin('classes', 'exams.class_id', '=', 'classes.id')
            ->leftJoin('academic_years', 'exams.academic_year_id', '=', 'academic_years.id')
            ->leftJoin('semesters', 'exams.semester_id', '=', 'semesters.id')
            ->select([
                'exams.*',
                'subjects.name as subject_name',
                'users.name as teacher_name',
                'classes.name as class_name',
                'academic_years.name as academic_year_name',
                'semesters.name as semester_name'
            ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('exams.title', 'LIKE', "%{$search}%")
                  ->orWhere('exams.slug', 'LIKE', "%{$search}%");
            });
        }
        if ($status) {
            $query->where('exams.status', '=', $status);
        }
        if ($subject) {
            $query->where('exams.subject_id', '=', $subject);
        }

        $exams = $query->orderBy('exams.created_at', 'desc')->paginate(15, $page);
        $subjects = \Core\Database::getInstance()->table('subjects')->orderBy('name')->get();

        return \Core\Response::view('admin/exams/index', [
            'title' => 'Kelola Ujian',
            'exams' => $exams,
            'subjects' => $subjects,
            'filters' => compact('search', 'status', 'subject'),
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('admin/dashboard')],
                ['label' => 'Ujian', 'url' => null]
            ]
        ]);
    })->name('exams');

    // Exams Create
    $router->get('/exams/create', function () {
        $db = \Core\Database::getInstance();
        $subjects = $db->table('subjects')->orderBy('name')->get();
        $classes = $db->table('classes')->orderBy('name')->get();
        $academicYears = $db->table('academic_years')->orderBy('name', 'desc')->get();
        $semesters = $db->table('semesters')->orderBy('name')->get();
        $teachers = $db->table('users')->where('role_id', '=', 2)->orderBy('name')->get();

        return \Core\Response::view('admin/exams/create', [
            'title' => 'Buat Ujian Baru',
            'subjects' => $subjects,
            'classes' => $classes,
            'academicYears' => $academicYears,
            'semesters' => $semesters,
            'teachers' => $teachers,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('admin/dashboard')],
                ['label' => 'Ujian', 'url' => url('admin/exams')],
                ['label' => 'Tambah', 'url' => null]
            ]
        ]);
    })->name('exams.create');

    $router->post('/exams', function () {
        $request = \Core\App::getInstance()->getRequest();
        $data = $request->all();

        $validator = \Core\validator($data, [
            'title' => 'required|string|max:200',
            'slug' => 'required|alpha_dash|max:100|unique:exams,slug',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'nullable|exists:classes,id',
            'teacher_id' => 'required|exists:users,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'total_questions' => 'nullable|integer|min:1',
            'passing_score' => 'nullable|numeric|min:0|max:100',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'show_results' => 'boolean',
            'allow_review' => 'boolean',
            'fullscreen_mode' => 'boolean',
            'ip_restriction' => 'nullable|string',
            'max_attempts' => 'nullable|integer|min:1',
            'settings' => 'array',
        ]);

        if ($validator->fails()) {
            \Core\Session::flashInput($data);
            \Core\Session::flash('error', $validator->first() ?: 'Validasi gagal');
            return \Core\Response::back();
        }

        $data['settings'] = json_encode($data['settings'] ?? []);
        $data['status'] = 'draft';

        $exam = \Core\Database::getInstance()->table('exams')->insert($data);

        \Core\Session::flash('success', 'Ujian berhasil dibuat.');
        return \Core\Response::redirect(url('admin/exams'));
    })->name('exams.store');

    // Exams Edit
    $router->get('/exams/{id}/edit', function ($id) {
        $db = \Core\Database::getInstance();
        $exam = $db->table('exams')->where('id', '=', $id)->first();
        if (!$exam) abort(404);

        $subjects = $db->table('subjects')->orderBy('name')->get();
        $classes = $db->table('classes')->orderBy('name')->get();
        $academicYears = $db->table('academic_years')->orderBy('name', 'desc')->get();
        $semesters = $db->table('semesters')->orderBy('name')->get();
        $teachers = $db->table('users')->where('role_id', '=', 2)->orderBy('name')->get();

        return \Core\Response::view('admin/exams/edit', [
            'title' => 'Edit Ujian',
            'exam' => $exam,
            'subjects' => $subjects,
            'classes' => $classes,
            'academicYears' => $academicYears,
            'semesters' => $semesters,
            'teachers' => $teachers,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('admin/dashboard')],
                ['label' => 'Ujian', 'url' => url('admin/exams')],
                ['label' => 'Edit', 'url' => null]
            ]
        ]);
    })->name('exams.edit');

    $router->put('/exams/{id}', function ($id) {
        $request = \Core\App::getInstance()->getRequest();
        $db = \Core\Database::getInstance();
        $exam = $db->table('exams')->where('id', '=', $id)->first();
        if (!$exam) abort(404);

        $data = $request->all();
        $validator = \Core\validator($data, [
            'title' => 'required|string|max:200',
            'slug' => 'required|alpha_dash|max:100|unique:exams,slug,' . $id,
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'nullable|exists:classes,id',
            'teacher_id' => 'required|exists:users,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'total_questions' => 'nullable|integer|min:1',
            'passing_score' => 'nullable|numeric|min:0|max:100',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'show_results' => 'boolean',
            'allow_review' => 'boolean',
            'fullscreen_mode' => 'boolean',
            'ip_restriction' => 'nullable|string',
            'max_attempts' => 'nullable|integer|min:1',
            'settings' => 'array',
            'status' => 'required|in:draft,published,archived',
        ]);

        if ($validator->fails()) {
            \Core\Session::flashInput($data);
            \Core\Session::flash('error', $validator->first() ?: 'Validasi gagal');
            return \Core\Response::back();
        }

        $data['settings'] = json_encode($data['settings'] ?? []);
        $db->table('exams')->where('id', '=', $id)->update($data);

        \Core\Session::flash('success', 'Ujian berhasil diperbarui.');
        return \Core\Response::redirect(url('admin/exams'));
    })->name('exams.update');

    // Exams Show
    $router->get('/exams/{id}', function ($id) {
        $db = \Core\Database::getInstance();
        $exam = $db->table('exams')
            ->leftJoin('subjects', 'exams.subject_id', '=', 'subjects.id')
            ->leftJoin('users', 'exams.teacher_id', '=', 'users.id')
            ->leftJoin('classes', 'exams.class_id', '=', 'classes.id')
            ->leftJoin('academic_years', 'exams.academic_year_id', '=', 'academic_years.id')
            ->leftJoin('semesters', 'exams.semester_id', '=', 'semesters.id')
            ->where('exams.id', '=', $id)
            ->select([
                'exams.*',
                'subjects.name as subject_name',
                'users.name as teacher_name',
                'classes.name as class_name',
                'academic_years.name as academic_year_name',
                'semesters.name as semester_name'
            ])
            ->first();

        if (!$exam) abort(404);

        // Get questions
        $questions = $db->table('exam_questions eq')
            ->leftJoin('questions q', 'eq.question_id', '=', 'q.id')
            ->where('eq.exam_id', '=', $id)
            ->select(['eq.*', 'q.type', 'q.content', 'q.points', 'q.difficulty'])
            ->orderBy('eq.order_number')
            ->get();

        // Get participants
        $participants = $db->table('exam_participants ep')
            ->leftJoin('users u', 'ep.user_id', '=', 'u.id')
            ->leftJoin('user_profiles up', 'u.id', '=', 'up.user_id')
            ->leftJoin('classes c', 'up.class_id', '=', 'c.id')
            ->where('ep.exam_id', '=', $id)
            ->select(['ep.*', 'u.name as student_name', 'up.student_id as student_nis', 'c.name as class_name'])
            ->orderBy('ep.created_at')
            ->get();

        // Stats
        $stats = [
            'total_questions' => count($questions),
            'total_points' => array_sum(array_column($questions, 'points')),
            'participants' => count($participants),
            'started' => count(array_filter($participants, fn($p) => $p['status'] !== 'not_started')),
            'completed' => count(array_filter($participants, fn($p) => in_array($p['status'], ['graded', 'submitted']))),
            'graded' => count(array_filter($participants, fn($p) => $p['status'] === 'graded')),
        ];

        return \Core\Response::view('admin/exams/show', [
            'title' => 'Detail Ujian: ' . $exam['title'],
            'exam' => $exam,
            'questions' => $questions,
            'participants' => $participants,
            'stats' => $stats,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('admin/dashboard')],
                ['label' => 'Ujian', 'url' => url('admin/exams')],
                ['label' => $exam['title'], 'url' => null]
            ]
        ]);
    })->name('exams.show');

    // Exams Questions (Question Bank Picker)
    $router->get('/exams/{id}/questions', function ($id) {
        $db = \Core\Database::getInstance();
        $exam = $db->table('exams')->where('id', '=', $id)->first();
        if (!$exam) abort(404);

        $request = \Core\App::getInstance()->getRequest();
        $page = (int) $request->query('page', 1);
        $search = $request->query('search', '');
        $category = $request->query('category', '');
        $type = $request->query('type', '');
        $difficulty = $request->query('difficulty', '');

        $query = $db->table('questions q')
            ->leftJoin('question_categories qc', 'q.category_id', '=', 'qc.id')
            ->leftJoin('users u', 'q.created_by', '=', 'u.id')
            ->where('q.status', '=', 'active')
            ->select(['q.*', 'qc.name as category_name', 'u.name as author_name']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('q.title', 'LIKE', "%{$search}%")
                  ->orWhere('q.content', 'LIKE', "%{$search}%");
            });
        }
        if ($category) {
            $query->where('q.category_id', '=', $category);
        }
        if ($type) {
            $query->where('q.type', '=', $type);
        }
        if ($difficulty) {
            $query->where('q.difficulty', '=', $difficulty);
        }

        $questions = $query->orderBy('q.created_at', 'desc')->paginate(20, $page);

        $categories = $db->table('question_categories')->orderBy('name')->get();

        // Already selected questions
        $selected = $db->table('exam_questions')
            ->where('exam_id', '=', $id)
            ->pluck('question_id');

        return \Core\Response::view('admin/exams/questions', [
            'title' => 'Kelola Soal: ' . $exam['title'],
            'exam' => $exam,
            'questions' => $questions,
            'categories' => $categories,
            'selected' => $selected,
            'filters' => compact('search', 'category', 'type', 'difficulty'),
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('admin/dashboard')],
                ['label' => 'Ujian', 'url' => url('admin/exams')],
                ['label' => $exam['title'], 'url' => url('admin/exams/' . $id)],
                ['label' => 'Soal', 'url' => null]
            ]
        ]);
    })->name('exams.questions');

    // Exam Questions - Add
    $router->post('/exams/{id}/questions', function ($id) {
        $request = \Core\App::getInstance()->getRequest();
        $db = \Core\Database::getInstance();

        $questionIds = $request->input('question_ids', []);
        $points = $request->input('points', []);

        $exam = $db->table('exams')->where('id', '=', $id)->first();
        if (!$exam) abort(404);

        // Get max order
        $maxOrder = $db->table('exam_questions')
            ->where('exam_id', '=', $id)
            ->max('order_number') ?? 0;

        foreach ($questionIds as $index => $questionId) {
            $order = $maxOrder + $index + 1;
            $pointValue = (float)($points[$questionId] ?? 1);
            $db->table('exam_questions')->insert([
                'exam_id' => $id,
                'question_id' => $questionId,
                'points' => $pointValue,
                'order_number' => $order,
            ]);
        }

        \Core\Session::flash('success', count($questionIds) . ' soal ditambahkan ke ujian.');
        return \Core\Response::redirect(url('admin/exams/' . $id . '/questions'));
    })->name('exams.questions.add');

    // Exam Questions - Update Order/Points
    $router->put('/exams/{id}/questions', function ($id) {
        $request = \Core\App::getInstance()->getRequest();
        $db = \Core\Database::getInstance();

        $questions = $request->input('questions', []);

        foreach ($questions as $q) {
            $db->table('exam_questions')
                ->where('exam_id', '=', $id)
                ->where('question_id', '=', $q['question_id'])
                ->update([
                    'points' => (float)($q['points'] ?? 1),
                    'order_number' => (int)$q['order_number'],
                ]);
        }

        return \Core\Response::json(['message' => 'Soal berhasil diperbarui']);
    })->name('exams.questions.update');

    // Exam Questions - Remove
    $router->delete('/exams/{id}/questions/{questionId}', function ($id, $questionId) {
        $db = \Core\Database::getInstance();
        $db->table('exam_questions')
            ->where('exam_id', '=', $id)
            ->where('question_id', '=', $questionId)
            ->delete();

        return \Core\Response::json(['message' => 'Soal dihapus dari ujian']);
    })->name('exams.questions.remove');

    // Exams Participants
    $router->get('/exams/{id}/participants', function ($id) {
        $db = \Core\Database::getInstance();
        $exam = $db->table('exams')->where('id', '=', $id)->first();
        if (!$exam) abort(404);

        $request = \Core\App::getInstance()->getRequest();
        $page = (int) $request->query('page', 1);
        $search = $request->query('search', '');
        $status = $request->query('status', '');

        $query = $db->table('exam_participants ep')
            ->leftJoin('users u', 'ep.user_id', '=', 'u.id')
            ->leftJoin('user_profiles up', 'u.id', '=', 'up.user_id')
            ->leftJoin('classes c', 'up.class_id', '=', 'c.id')
            ->where('ep.exam_id', '=', $id)
            ->select(['ep.*', 'u.name as student_name', 'up.student_id as student_nis', 'c.name as class_name']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('u.name', 'LIKE', "%{$search}%")
                  ->orWhere('up.student_id', 'LIKE', "%{$search}%");
            });
        }
        if ($status) {
            $query->where('ep.status', '=', $status);
        }

        $participants = $query->orderBy('ep.created_at')->paginate(20, $page);

        // Classes for bulk assign
        $classes = $db->table('classes')->orderBy('name')->get();

        return \Core\Response::view('admin/exams/participants', [
            'title' => 'Peserta Ujian: ' . $exam['title'],
            'exam' => $exam,
            'participants' => $participants,
            'classes' => $classes,
            'filters' => compact('search', 'status'),
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('admin/dashboard')],
                ['label' => 'Ujian', 'url' => url('admin/exams')],
                ['label' => $exam['title'], 'url' => url('admin/exams/' . $id)],
                ['label' => 'Peserta', 'url' => null]
            ]
        ]);
    })->name('exams.participants');

    // Exams Participants - Add Individual
    $router->post('/exams/{id}/participants', function ($id) {
        $request = \Core\App::getInstance()->getRequest();
        $db = \Core\Database::getInstance();

        $userIds = $request->input('user_ids', []);

        $exam = $db->table('exams')->where('id', '=', $id)->first();
        if (!$exam) abort(404);

        $added = 0;
        foreach ($userIds as $userId) {
            $exists = $db->table('exam_participants')
                ->where('exam_id', '=', $id)
                ->where('user_id', '=', $userId)
                ->first();

            if (!$exists) {
                $db->table('exam_participants')->insert([
                    'exam_id' => $id,
                    'user_id' => $userId,
                    'status' => 'not_started',
                ]);
                $added++;
            }
        }

        \Core\Session::flash('success', "$added peserta ditambahkan.");
        return \Core\Response::redirect(url('admin/exams/' . $id . '/participants'));
    })->name('exams.participants.add');

    // Exams Participants - Bulk Assign by Class
    $router->post('/exams/{id}/participants/bulk', function ($id) {
        $request = \Core\App::getInstance()->getRequest();
        $db = \Core\Database::getInstance();

        $classId = (int)$request->input('class_id');

        $exam = $db->table('exams')->where('id', '=', $id)->first();
        if (!$exam) abort(404);

        $students = $db->table('class_students')
            ->where('class_id', '=', $classId)
            ->pluck('user_id');

        $added = 0;
        foreach ($students as $userId) {
            $exists = $db->table('exam_participants')
                ->where('exam_id', '=', $id)
                ->where('user_id', '=', $userId)
                ->first();

            if (!$exists) {
                $db->table('exam_participants')->insert([
                    'exam_id' => $id,
                    'user_id' => $userId,
                    'status' => 'not_started',
                ]);
                $added++;
            }
        }

        \Core\Session::flash('success', "$added peserta dari kelas ditambahkan.");
        return \Core\Response::redirect(url('admin/exams/' . $id . '/participants'));
    })->name('exams.participants.bulk');

    // Exams Participants - Import from CSV/Excel
    $router->post('/exams/{id}/participants/import', function ($id) {
        $request = \\Core\\App::getInstance()->getRequest();
        if (!$request->hasValidCsrfToken()) {
            \\Core\\Session::flash('error', 'Token CSRF tidak valid');
            return \\Core\\Response::back();
        }

        $db = \\Core\\Database::getInstance();
        $exam = $db->table('exams')->where('id', '=', $id)->first();
        if (!$exam) abort(404);

        if (!isset($_FILES['participants_file']) || !is_uploaded_file($_FILES['participants_file']['tmp_name'])) {
            \\Core\\Session::flash('error', 'File tidak diunggah');
            return \\Core\\Response::back();
        }

        $file = $_FILES['participants_file'];
        // Only allow CSV for simplicity
        $allowedExt = ['csv'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt)) {
            \\Core\\Session::flash('error', 'Hanya file CSV yang diizinkan');
            return \\Core\\Response::back();
        }

        $added = 0;
        $notFound = 0;
        $alreadyExists = 0;

        if (($handle = fopen($file['tmp_name'], "r")) !== false) {
            $header = null;
            $rowNum = 0;
            while (($data = fgetcsv($handle, 0, ",")) !== false) {
                $rowNum++;
                if ($rowNum === 1) {
                    // Assume first row is header
                    $header = array_map('strtolower', $data);
                    continue;
                }
                if (empty($data)) {
                    continue;
                }
                // Map row to associative array using header
                $row = array_combine($header, $data);
                if (!$row) {
                    $row = [];
                    // If header parsing failed, try to infer columns by position? We'll skip for simplicity.
                    continue;
                }

                $email = isset($row['email']) ? trim($row['email']) : null;
                $studentId = null;
                // Try various column names for student ID
                foreach (['student_id', 'nis', 'nim', 'nrp'] as $key) {
                    if (isset($row[$key]) && trim($row[$key]) !== '') {
                        $studentId = trim($row[$key]);
                        break;
                    }
                }

                $userId = null;
                if ($email) {
                    $user = $db->table('users')->where('email', '=', $email)->first();
                    if ($user) {
                        $userId = $user['id'];
                    }
                } elseif ($studentId) {
                    $profile = $db->table('user_profiles')->where('student_id', '=', $studentId)->first();
                    if ($profile) {
                        $userId = $profile['user_id'];
                    }
                }

                if (!$userId) {
                    $notFound++;
                    continue;
                }

                $exists = $db->table('exam_participants')
                    ->where('exam_id', '=', $id)
                    ->where('user_id', '=', $userId)
                    ->first();

                if ($exists) {
                    $alreadyExists++;
                    continue;
                }

                $db->table('exam_participants')->insert([
                    'exam_id' => $id,
                    'user_id' => $userId,
                    'status' => 'not_started',
                ]);
                $added++;
            }
            fclose($handle);
        }

        $message = "$added peserta ditambahkan.";
        if ($notFound > 0) {
            $message .= " $notFound tidak ditemukan.";
        }
        if ($alreadyExists > 0) {
            $message .= " $alreadyExists sudah terdaftar.";
        }
        \\Core\\Session::flash('success', $message);
        return \\Core\\Response::redirect(url('admin/exams/' . $id . '/participants'));
    })->name('exams.participants.import');

    // Exams Participants - Remove
    $router->delete('/exams/{id}/participants/{participantId}', function ($id, $participantId) {
        $db = \Core\Database::getInstance();

        $participant = $db->table('exam_participants')
            ->where('id', '=', $participantId)
            ->where('exam_id', '=', $id)
            ->first();

        if (!$participant) abort(404);

        if ($participant['status'] !== 'not_started') {
            return \Core\Response::json(['message' => 'Tidak dapat menghapus peserta yang sudah memulai ujian'], 422);
        }

        $db->table('exam_participants')->where('id', '=', $participantId)->delete();

        return \Core\Response::json(['message' => 'Peserta dihapus']);
    })->name('exams.participants.remove');

    // Exams Publish/Unpublish
    $router->post('/exams/{id}/publish', function ($id) {
        $db = \Core\Database::getInstance();
        $exam = $db->table('exams')->where('id', '=', $id)->first();
        if (!$exam) abort(404);

        $db->table('exams')->where('id', '=', $id)->update(['status' => 'published']);

        \Core\Session::flash('success', 'Ujian dipublikasikan.');
        return \Core\Response::redirect(url('admin/exams/' . $id));
    })->name('exams.publish');

    $router->post('/exams/{id}/unpublish', function ($id) {
        $db = \Core\Database::getInstance();
        $exam = $db->table('exams')->where('id', '=', $id)->first();
        if (!$exam) abort(404);

        $db->table('exams')->where('id', '=', $id)->update(['status' => 'draft']);

        \Core\Session::flash('success', 'Ujian di-unpublish (kembali ke draft).');
        return \Core\Response::redirect(url('admin/exams/' . $id));
    })->name('exams.unpublish');

    $router->delete('/exams/{id}', function ($id) {
        $db = \Core\Database::getInstance();
        $exam = $db->table('exams')->where('id', '=', $id)->first();
        if (!$exam) abort(404);

        $db->table('exams')->where('id', '=', $id)->delete();

        \Core\Session::flash('success', 'Ujian dihapus.');
        return \Core\Response::redirect(url('admin/exams'));
    })->name('exams.destroy');

    // Question Bank (Admin)
    $router->resource('questions', 'Admin\QuestionController');
    $router->resource('question-categories', 'Admin\QuestionCategoryController');
});

// ==================== TEACHER ROUTES ====================

$router->group(['prefix' => 'teacher', 'middleware' => [\Auth\Middleware\AuthMiddleware::class, \Auth\Middleware\RoleMiddleware::class, \Auth\Middleware\PermissionMiddleware::class], 'as' => 'teacher.'], function ($router) {
    $router->get('/dashboard', function () {
        $user = auth();
        $db = \Core\Database::getInstance();

        $myExams = $db->table('exams')
            ->where('teacher_id', '=', $user->id)
            ->leftJoin('subjects', 'exams.subject_id', '=', 'subjects.id')
            ->leftJoin('classes', 'exams.class_id', '=', 'classes.id')
            ->select(['exams.*', 'subjects.name as subject_name', 'classes.name as class_name'])
            ->orderBy('exams.created_at', 'desc')
            ->get();

        $stats = [
            'total_exams' => count($myExams),
            'published_exams' => count(array_filter($myExams, fn($e) => $e['status'] === 'published')),
            'draft_exams' => count(array_filter($myExams, fn($e) => $e['status'] === 'draft')),
            'total_questions' => $db->table('questions')->where('created_by', '=', $user->id)->count(),
        ];

        return \Core\Response::view('teacher/dashboard', [
            'title' => 'Dashboard Guru',
            'exams' => $myExams,
            'stats' => $stats,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => null]
            ]
        ]);
    })->name('dashboard');

    // Teacher Exams
    $router->get('/exams', function () {
        $user = auth();
        $request = \Core\App::getInstance()->getRequest();
        $page = (int) $request->query('page', 1);
        $search = $request->query('search', '');
        $status = $request->query('status', '');

        $query = \Core\Database::getInstance()->table('exams')
            ->where('teacher_id', '=', $user->id)
            ->leftJoin('subjects', 'exams.subject_id', '=', 'subjects.id')
            ->leftJoin('classes', 'exams.class_id', '=', 'classes.id')
            ->select(['exams.*', 'subjects.name as subject_name', 'classes.name as class_name']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('exams.title', 'LIKE', "%{$search}%")
                  ->orWhere('exams.slug', 'LIKE', "%{$search}%");
            });
        }
        if ($status) {
            $query->where('exams.status', '=', $status);
        }

        $exams = $query->orderBy('exams.created_at', 'desc')->paginate(15, $page);

        return \Core\Response::view('teacher/exams/index', [
            'title' => 'Ujian Saya',
            'exams' => $exams,
            'filters' => compact('search', 'status'),
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('teacher/dashboard')],
                ['label' => 'Ujian', 'url' => null]
            ]
        ]);
    })->name('exams');

    $router->get('/exams/create', function () {
        $user = auth();
        $db = \Core\Database::getInstance();

        // Get teacher's class-subjects
        $classSubjects = $db->table('class_subjects cs')
            ->leftJoin('classes c', 'cs.class_id', '=', 'c.id')
            ->leftJoin('subjects s', 'cs.subject_id', '=', 's.id')
            ->leftJoin('academic_years ay', 'cs.academic_year_id', '=', 'ay.id')
            ->leftJoin('semesters sem', 'cs.semester_id', '=', 'sem.id')
            ->where('cs.teacher_id', '=', $user->id)
            ->select(['cs.*', 'c.name as class_name', 's.name as subject_name', 'ay.name as ay_name', 'sem.name as sem_name'])
            ->get();

        $academicYears = $db->table('academic_years')->orderBy('name', 'desc')->get();
        $semesters = $db->table('semesters')->orderBy('name')->get();

        return \Core\Response::view('teacher/exams/create', [
            'title' => 'Buat Ujian Baru',
            'classSubjects' => $classSubjects,
            'academicYears' => $academicYears,
            'semesters' => $semesters,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('teacher/dashboard')],
                ['label' => 'Ujian', 'url' => url('teacher/exams')],
                ['label' => 'Tambah', 'url' => null]
            ]
        ]);
    })->name('exams.create');

    $router->post('/exams', function () {
        $user = auth();
        $request = \Core\App::getInstance()->getRequest();
        $db = \Core\Database::getInstance();

        $data = $request->all();

        $validator = \Core\validator($data, [
            'title' => 'required|string|max:200',
            'slug' => 'required|alpha_dash|max:100|unique:exams,slug',
            'class_subject_id' => 'required|exists:class_subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'total_questions' => 'nullable|integer|min:1',
            'passing_score' => 'nullable|numeric|min:0|max:100',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'show_results' => 'boolean',
            'allow_review' => 'boolean',
            'fullscreen_mode' => 'boolean',
            'ip_restriction' => 'nullable|string',
            'max_attempts' => 'nullable|integer|min:1',
            'settings' => 'array',
        ]);

        if ($validator->fails()) {
            \Core\Session::flashInput($data);
            \Core\Session::flash('error', $validator->first() ?: 'Validasi gagal');
            return \Core\Response::back();
        }

        $cs = $db->table('class_subjects')
            ->where('id', '=', $data['class_subject_id'])
            ->where('teacher_id', '=', $user->id)
            ->first();

        if (!$cs) {
            \Core\Session::flash('error', 'Mata pelajaran/kelas tidak valid.');
            return \Core\Response::back();
        }

        $data['teacher_id'] = $user->id;
        $data['subject_id'] = $cs['subject_id'];
        $data['class_id'] = $cs['class_id'];
        unset($data['class_subject_id']);
        $data['settings'] = json_encode($data['settings'] ?? []);
        $data['status'] = 'draft';

        $db->table('exams')->insert($data);

        \Core\Session::flash('success', 'Ujian berhasil dibuat.');
        return \Core\Response::redirect(url('teacher/exams'));
    })->name('exams.store');

    // Grading
    $router->get('/grading', function () {
        $user = auth();
        $db = \Core\Database::getInstance();

        // Build filters
        $filters = [
            'exam_id' => $_GET['exam_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'class_id' => $_GET['class_id'] ?? '',
            'page' => (int)($_GET['page'] ?? 1),
        ];

        // Get exams for filter dropdown
        $exams = $db->table('exams')
            ->where('teacher_id', '=', $user->id)
            ->select(['id', 'title', 'subject_id'])
            ->get();

        // Get classes for filter dropdown
        $classes = $db->table('class_subjects')
            ->where('teacher_id', '=', $user->id)
            ->leftJoin('classes', 'class_subjects.class_id', '=', 'classes.id')
            ->select(['classes.id', 'classes.name'])
            ->get();

        // Build query for participants needing grading
        $query = $db->table('exam_participants ep')
            ->leftJoin('exams e', 'ep.exam_id', '=', 'e.id')
            ->leftJoin('subjects s', 'e.subject_id', '=', 's.id')
            ->leftJoin('users u', 'ep.user_id', '=', 'u.id')
            ->leftJoin('user_profiles up', 'u.id', '=', 'up.user_id')
            ->leftJoin('class_students cs', 'u.id', '=', 'cs.user_id')
            ->leftJoin('classes c', 'cs.class_id', '=', 'c.id')
            ->where('e.teacher_id', '=', $user->id)
            ->whereIn('ep.status', ['submitted', 'graded'])
            ->select([
                'ep.*',
                'e.title as exam_title',
                's.name as subject_name',
                'u.name as student_name',
                'up.student_id as student_nis',
                'c.name as class_name',
            ]);

        if ($filters['exam_id']) {
            $query->where('ep.exam_id', '=', $filters['exam_id']);
        }
        if ($filters['status']) {
            $query->where('ep.status', '=', $filters['status']);
        }
        if ($filters['class_id']) {
            $query->where('c.id', '=', $filters['class_id']);
        }

        // Get counts for stats
        $allParticipants = $db->table('exam_participants ep')
            ->leftJoin('exams e', 'ep.exam_id', '=', 'e.id')
            ->where('e.teacher_id', '=', $user->id)
            ->whereIn('ep.status', ['submitted', 'graded'])
            ->select(['ep.id', 'ep.status'])
            ->get();

        $stats = [
            'pending_grading' => count(array_filter($allParticipants, fn($p) => $p['status'] === 'submitted')),
            'in_progress_grading' => 0,
            'completed_grading' => count(array_filter($allParticipants, fn($p) => $p['status'] === 'graded')),
            'total_participants' => count($allParticipants),
        ];

        // Check if any exams have graded participants (for bulk release)
        $hasGradedExams = $db->table('exam_participants ep')
            ->leftJoin('exams e', 'ep.exam_id', '=', 'e.id')
            ->where('e.teacher_id', '=', $user->id)
            ->where('ep.status', '=', 'graded')
            ->select(['e.id', 'e.title', $db->raw('COUNT(*) as graded_count')])
            ->groupBy('e.id', 'e.title')
            ->get();

        // Paginate
        $page = $filters['page'];
        $perPage = 15;
        $offset = ($page - 1) * $perPage;
        $participants = $query->orderBy('ep.finished_at', 'desc')
            ->offset($offset)
            ->limit($perPage + 1)
            ->get();

        $hasMore = count($participants) > $perPage;
        if ($hasMore) array_pop($participants);

        // Add pending essay count to each participant
        foreach ($participants as &$p) {
            $pendingEssays = $db->table('attempts a')
                ->leftJoin('questions q', 'a.question_id', '=', 'q.id')
                ->where('a.exam_participant_id', '=', $p['id'])
                ->where('q.type', '=', 'essay')
                ->where('a.is_correct', '=', null)
                ->count();
            $p['pending_essay_count'] = $pendingEssays;
        }

        $pagination = [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $offset + count($participants) + ($hasMore ? 1 : 0),
            'from' => $offset + 1,
            'to' => $offset + count($participants),
            'total_pages' => ceil(($offset + count($participants) + ($hasMore ? 1 : 0)) / $perPage),
        ];

        return \Core\Response::view('teacher/grading/index', [
            'title' => 'Penilaian Ujian',
            'exams' => $exams,
            'classes' => $classes,
            'filters' => $filters,
            'participants' => $participants,
            'stats' => $stats,
            'pagination' => $pagination,
            'hasGradedExams' => $hasGradedExams,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('teacher/dashboard')],
                ['label' => 'Penilaian Ujian', 'url' => null]
            ]
        ]);
    })->name('grading')->middleware(\Auth\Middleware\PermissionMiddleware::class, ['grading.view']);

    // Grading - Attempt Detail (Essay Grading Page)
    $router->get('/grading/attempt/{id}', function ($id) {
        $user = auth();
        $db = \Core\Database::getInstance();

        $participant = $db->table('exam_participants ep')
            ->leftJoin('exams e', 'ep.exam_id', '=', 'e.id')
            ->leftJoin('subjects s', 'e.subject_id', '=', 's.id')
            ->leftJoin('users u', 'ep.user_id', '=', 'u.id')
            ->leftJoin('user_profiles up', 'u.id', '=', 'up.user_id')
            ->leftJoin('classes c', 'c.id', '=', 'up.class_id')
            ->where('ep.id', '=', $id)
            ->where('e.teacher_id', '=', $user->id)
            ->select(['ep.*', 'e.title as exam_title', 'e.subject_id', 's.name as subject_name', 'u.name as student_name', 'up.student_id as student_nis', 'c.name as class_name'])
            ->first();

        if (!$participant) {
            abort(404, 'Peserta ujian tidak ditemukan');
        }

        $attempts = $db->table('attempts a')
            ->leftJoin('questions q', 'a.question_id', '=', 'q.id')
            ->leftJoin('question_options qo', 'q.id', '=', 'qo.question_id')
            ->leftJoin('essay_gradings eg', 'a.id', '=', 'eg.attempt_id')
            ->where('a.exam_participant_id', '=', $id)
            ->select([
                'a.*',
                'q.type as question_type',
                'q.content as question_content',
                'q.explanation',
                'q.points',
                'eg.id as grading_id',
                'eg.score as grading_score',
                'eg.max_score as grading_max_score',
                'eg.feedback as grading_feedback',
                'eg.rubric_scores as grading_rubric_scores',
                'eg.status as grading_status',
                'eg.graded_at as grading_graded_at',
                'eg.graded_by',
                'grader.name as grader_name',
            ])
            ->leftJoin('users grader', 'eg.graded_by', '=', 'grader.id')
            ->orderBy('a.question_number')
            ->get();

        foreach ($attempts as &$a) {
            $a['question_content'] = json_decode($a['question_content'], true) ?? [];
            $a['answer'] = json_decode($a['answer'] ?? '{}', true) ?? [];
            $a['correct_answer'] = json_decode($a['correct_answer'] ?? '{}', true) ?? [];
            $a['essay_grading'] = $a['grading_id'] ? [
                'id' => $a['grading_id'],
                'score' => (float)$a['grading_score'],
                'max_score' => (float)$a['grading_max_score'],
                'feedback' => $a['grading_feedback'],
                'rubric_scores' => json_decode($a['grading_rubric_scores'] ?? '[]', true),
                'status' => $a['grading_status'],
                'graded_at' => $a['grading_graded_at'],
                'graded_by' => $a['graded_by'],
                'grader_name' => $a['grader_name'],
            ] : null;
        }

        return \Core\Response::view('teacher/grading/attempt', [
            'title' => 'Nilai Essay: ' . $participant['student_name'],
            'participant' => $participant,
            'attempts' => $attempts,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('teacher/dashboard')],
                ['label' => 'Penilaian Ujian', 'url' => url('teacher/grading')],
                ['label' => 'Nilai Essay: ' . $participant['student_name'], 'url' => null]
            ]
        ]);
    })->name('grading.attempt')->middleware(\Auth\Middleware\PermissionMiddleware::class, ['grading.essay']);

    // Grading - Save Essay Grades
    $router->post('/grading/save', function () {
        $request = \Core\App::getInstance()->getRequest();
        $user = auth();
        $db = \Core\Database::getInstance();

        if (!$request->hasValidCsrfToken()) {
            \Core\Session::flash('error', 'Token CSRF tidak valid');
            return \Core\Response::back();
        }

        $examParticipantId = (int)$request->input('exam_participant_id');
        $action = $request->input('action', 'save_draft');
        $scores = $request->input('scores', []);
        $maxScores = $request->input('max_scores', []);
        $statuses = $request->input('statuses', []);
        $feedbacks = $request->input('feedbacks', []);
        $rubricScores = $request->input('rubric_scores', []);
        $rubricMax = $request->input('rubric_max', []);
        $rubricCriteria = $request->input('rubric_criteria', []);

        $participant = $db->table('exam_participants ep')
            ->leftJoin('exams e', 'ep.exam_id', '=', 'e.id')
            ->where('ep.id', '=', $examParticipantId)
            ->where('e.teacher_id', '=', $user->id)
            ->first();

        if (!$participant) {
            abort(404, 'Peserta ujian tidak ditemukan');
        }

        $grading = new \Modules\Exam\Grading();
        $allGraded = true;

        foreach ($scores as $attemptId => $score) {
            $attemptId = (int)$attemptId;
            $score = (float)$score;
            $maxScore = (float)($maxScores[$attemptId] ?? 0);
            $status = $statuses[$attemptId] ?? 'pending';
            $feedback = $feedbacks[$attemptId] ?? '';
            $rubric = $rubricScores[$attemptId] ?? [];
            $rubricM = $rubricMax[$attemptId] ?? [];
            $rubricC = $rubricCriteria[$attemptId] ?? [];

            $rubricData = [];
            foreach ($rubric as $idx => $val) {
                $rubricData[] = [
                    'criteria' => $rubricC[$idx] ?? '',
                    'score' => (float)$val,
                    'max_score' => (float)($rubricM[$idx] ?? 0),
                ];
            }

            if ($score < 0 || $score > $maxScore) {
                \Core\Session::flash('error', 'Skor tidak valid untuk soal #' . $attemptId);
                return \Core\Response::back();
            }

            $rubricTotal = array_sum(array_column($rubricData, 'score'));
            if ($rubricTotal > $maxScore) {
                \Core\Session::flash('error', 'Total rubrik melebihi bobot soal untuk soal #' . $attemptId);
                return \Core\Response::back();
            }

            $grading->gradeEssayManually(
                $attemptId,
                $score,
                $maxScore,
                $feedback,
                $rubricData,
                $user->id
            );

            $db->table('essay_gradings')
                ->where('attempt_id', '=', $attemptId)
                ->update(['status' => $status]);

            if ($status === 'pending') {
                $allGraded = false;
            }
        }

        $pendingEssays = $db->table('attempts a')
            ->leftJoin('questions q', 'a.question_id', '=', 'q.id')
            ->where('a.exam_participant_id', '=', $examParticipantId)
            ->where('q.type', '=', 'essay')
            ->where('a.is_correct', '=', null)
            ->count();

        if ($pendingEssays === 0 && $action === 'save_and_release') {
            $grading->releaseGrades($examParticipantId, $user->id);
            \Core\Session::flash('success', 'Penilaian selesai dan nilai telah dirilis.');
        } else {
            \Core\Session::flash('success', 'Penilaian disimpan.' . ($action === 'save_and_release' ? ' (Masih ada essay yang menunggu penilaian, nilai belum dirilis)' : ''));
        }

        return \Core\Response::redirect(url('teacher/grading/attempt/' . $examParticipantId));
    })->name('grading.save')->middleware(\Auth\Middleware\PermissionMiddleware::class, ['grading.essay']);

    // Grading - Detail View (Read-only)
    $router->get('/grading/detail/{id}', function ($id) {
        $user = auth();
        $db = \Core\Database::getInstance();

        $participant = $db->table('exam_participants ep')
            ->leftJoin('exams e', 'ep.exam_id', '=', 'e.id')
            ->leftJoin('subjects s', 'e.subject_id', '=', 's.id')
            ->leftJoin('users u', 'ep.user_id', '=', 'u.id')
            ->leftJoin('user_profiles up', 'u.id', '=', 'up.user_id')
            ->leftJoin('classes c', 'c.id', '=', 'up.class_id')
            ->where('ep.id', '=', $id)
            ->where('e.teacher_id', '=', $user->id)
            ->select(['ep.*', 'e.title as exam_title', 'e.duration_minutes', 'e.passing_score', 's.name as subject_name', 'u.name as student_name', 'up.student_id as student_nis', 'c.name as class_name'])
            ->first();

        if (!$participant) {
            abort(404);
        }

        $attempts = $db->table('attempts a')
            ->leftJoin('questions q', 'a.question_id', '=', 'q.id')
            ->leftJoin('question_options qo', 'q.id', '=', 'qo.question_id')
            ->leftJoin('essay_gradings eg', 'a.id', '=', 'eg.attempt_id')
            ->where('a.exam_participant_id', '=', $id)
            ->select([
                'a.*',
                'q.type as question_type',
                'q.content as question_content',
                'q.explanation',
                'q.points',
                'eg.id as grading_id',
                'eg.score as grading_score',
                'eg.max_score as grading_max_score',
                'eg.feedback as grading_feedback',
                'eg.rubric_scores as grading_rubric_scores',
                'eg.rubric_max as grading_rubric_max',
                'eg.status as grading_status',
                'eg.graded_at as grading_graded_at',
                'eg.graded_by',
                'grader.name as grader_name',
            ])
            ->leftJoin('users grader', 'eg.graded_by', '=', 'grader.id')
            ->orderBy('a.question_number')
            ->get();

        foreach ($attempts as &$a) {
            $a['question_content'] = json_decode($a['question_content'], true) ?? [];
            $a['answer'] = json_decode($a['answer'] ?? '{}', true) ?? [];
            $a['correct_answer'] = json_decode($a['correct_answer'] ?? '{}', true) ?? [];
            $a['essay_grading'] = $a['grading_id'] ? [
                'id' => $a['grading_id'],
                'score' => (float)$a['grading_score'],
                'max_score' => (float)$a['grading_max_score'],
                'feedback' => $a['grading_feedback'],
                'rubric_scores' => json_decode($a['grading_rubric_scores'] ?? '[]', true),
                'rubric_max' => json_decode($a['grading_rubric_max'] ?? '[]', true),
                'status' => $a['grading_status'],
                'graded_at' => $a['grading_graded_at'],
                'graded_by' => $a['graded_by'],
                'grader_name' => $a['grader_name'],
            ] : null;
        }

        return \Core\Response::view('teacher/grading/detail', [
            'title' => 'Detail Hasil: ' . $participant['student_name'],
            'participant' => $participant,
            'attempts' => $attempts,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('teacher/dashboard')],
                ['label' => 'Penilaian Ujian', 'url' => url('teacher/grading')],
                ['label' => 'Detail: ' . $participant['student_name'], 'url' => null]
            ]
        ]);
    })->name('grading.detail')->middleware(\Auth\Middleware\PermissionMiddleware::class, ['grading.view']);

    // Grading - Bulk Release Grades
    $router->post('/grading/release', function () {
        $request = \Core\App::getInstance()->getRequest();
        $user = auth();
        $db = \Core\Database::getInstance();

        if (!$request->hasValidCsrfToken()) {
            \Core\Session::flash('error', 'Token CSRF tidak valid');
            return \Core\Response::back();
        }

        $examId = (int)$request->input('exam_id');

        $exam = $db->table('exams')->where('id', '=', $examId)->where('teacher_id', '=', $user->id)->first();
        if (!$exam) {
            \Core\Session::flash('error', 'Ujian tidak ditemukan');
            return \Core\Response::back();
        }

        $grading = new \Modules\Exam\Grading();
        $released = $grading->releaseGradesForExam($examId, $user->id);

        \Core\Session::flash('success', "Nilai untuk {$released} peserta telah dirilis.");
        return \Core\Response::redirect(url('teacher/grading'));
    })->name('grading.release')->middleware(\Auth\Middleware\PermissionMiddleware::class, ['grading.release']);

    // Reports
    $router->get('/reports', function () {
        return \Core\Response::view('teacher/reports/index', [
            'title' => 'Laporan Hasil',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('teacher/dashboard')],
                ['label' => 'Laporan', 'url' => null]
            ]
        ]);
    })->name('reports');
});

// ==================== STUDENT ROUTES ====================

$router->group(['prefix' => 'student', 'middleware' => [\Auth\Middleware\AuthMiddleware::class, \Auth\Middleware\RoleMiddleware::class, \Auth\Middleware\PermissionMiddleware::class], 'as' => 'student.'], function ($router) {
    $router->get('/dashboard', function () {
        $user = auth();
        $db = \Core\Database::getInstance();

        // Upcoming exams
        $upcomingExams = $db->table('exam_participants')
            ->where('user_id', '=', $user->id)
            ->where('status', '=', 'not_started')
            ->leftJoin('exams', 'exam_participants.exam_id', '=', 'exams.id')
            ->leftJoin('subjects', 'exams.subject_id', '=', 'subjects.id')
            ->where('exams.start_at', '>', date('Y-m-d H:i:s'))
            ->where('exams.status', '=', 'published')
            ->select(['exam_participants.*', 'exams.*', 'subjects.name as subject_name'])
            ->orderBy('exams.start_at', 'asc')
            ->limit(5)
            ->get();

        // In progress
        $inProgress = $db->table('exam_participants')
            ->where('user_id', '=', $user->id)
            ->where('status', '=', 'in_progress')
            ->leftJoin('exams', 'exam_participants.exam_id', '=', 'exams.id')
            ->select(['exam_participants.*', 'exams.*'])
            ->get();

        // Completed
        $completed = $db->table('exam_participants')
            ->where('user_id', '=', $user->id)
            ->whereIn('status', ['graded', 'submitted'])
            ->leftJoin('exams', 'exam_participants.exam_id', '=', 'exams.id')
            ->leftJoin('subjects', 'exams.subject_id', '=', 'subjects.id')
            ->select(['exam_participants.*', 'exams.*', 'subjects.name as subject_name'])
            ->orderBy('exam_participants.finished_at', 'desc')
            ->limit(5)
            ->get();

        return \Core\Response::view('student/dashboard', [
            'title' => 'Dashboard Siswa',
            'upcomingExams' => $upcomingExams,
            'inProgress' => $inProgress,
            'completed' => $completed,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => null]
            ]
        ]);
    })->name('dashboard');

    $router->get('/exams', function () {
        $user = auth();
        $request = \Core\App::getInstance()->getRequest();
        $page = (int) $request->query('page', 1);
        $status = $request->query('status', '');

        $query = \Core\Database::getInstance()->table('exam_participants ep')
            ->where('ep.user_id', '=', $user->id)
            ->leftJoin('exams e', 'ep.exam_id', '=', 'e.id')
            ->leftJoin('subjects s', 'e.subject_id', '=', 's.id')
            ->leftJoin('classes c', 'e.class_id', '=', 'c.id')
            ->where('e.status', '=', 'published')
            ->select(['ep.*', 'e.*', 's.name as subject_name', 'c.name as class_name']);

        if ($status) {
            $query->where('ep.status', '=', $status);
        }

        $exams = $query->orderBy('e.start_at', 'desc')->paginate(15, $page);

        return \Core\Response::view('student/exams/index', [
            'title' => 'Ujian Saya',
            'exams' => $exams,
            'filters' => compact('status'),
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('student/dashboard')],
                ['label' => 'Ujian', 'url' => null]
            ]
        ]);
    })->name('exams');

    // Exam Take
    $router->get('/exams/{id}/take', function ($id) {
        $user = auth();
        $db = \Core\Database::getInstance();

        $participant = $db->table('exam_participants ep')
            ->leftJoin('exams e', 'ep.exam_id', '=', 'e.id')
            ->where('ep.id', '=', $id)
            ->where('ep.user_id', '=', $user->id)
            ->select(['ep.*', 'e.*'])
            ->first();

        if (!$participant) abort(404);

        if ($participant['status'] === 'not_started') {
            // Check time window
            $now = date('Y-m-d H:i:s');
            if ($participant['start_at'] && $participant['start_at'] > $now) {
                \Core\Session::flash('error', 'Ujian belum dimulai.');
                return \Core\Response::redirect(url('student/exams'));
            }
            if ($participant['end_at'] && $participant['end_at'] < $now) {
                \Core\Session::flash('error', 'Ujian sudah berakhir.');
                return \Core\Response::redirect(url('student/exams'));
            }

            // Start exam
            $db->table('exam_participants')
                ->where('id', '=', $id)
                ->update([
                    'status' => 'in_progress',
                    'started_at' => $now,
                ]);

            $participant['status'] = 'in_progress';
            $participant['started_at'] = $now;
        } elseif ($participant['status'] === 'graded' || $participant['status'] === 'submitted') {
            return \Core\Response::redirect(url('student/results/' . $id));
        }

        // Get questions with options
        $questions = $db->table('exam_questions eq')
            ->leftJoin('questions q', 'eq.question_id', '=', 'q.id')
            ->where('eq.exam_id', '=', $participant['exam_id'])
            ->select(['eq.*', 'q.type', 'q.content', 'q.points', 'q.settings', 'q.explanation'])
            ->orderBy('eq.order_number')
            ->get();

        // Get existing attempts
        $attempts = $db->table('attempts')
            ->where('exam_participant_id', '=', $id)
            ->get();
        $attemptMap = [];
        foreach ($attempts as $a) {
            $attemptMap[$a['question_id']] = json_decode($a['answer'], true);
        }

        // Build question pages for layout
        $questionPages = [];
        foreach ($questions as $index => $q) {
            $questionPages[] = [
                'question' => [
                    'id' => $q['id'],
                    'type' => $q['type'],
                    'content' => json_decode($q['content'], true) ?? [],
                    'points' => (float)$q['points'],
                    'explanation' => $q['explanation'],
                    'settings' => json_decode($q['settings'] ?? '{}', true) ?? [],
                ],
                'answer' => $attemptMap[$q['question_id']] ?? null,
                'index' => $index + 1,
                'total' => count($questions),
                'mode' => 'exam',
            ];
        }

        $examConfig = json_decode($participant['settings'] ?? '{}', true) ?? [];

        return \Core\Response::view('student/exam/take', [
            'title' => 'Mengerjakan: ' . $participant['title'],
            'exam' => $participant,
            'participant' => $participant,
            'questions' => $questions,
            'questionPages' => $questionPages,
            'examConfig' => $examConfig,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('student/dashboard')],
                ['label' => 'Ujian', 'url' => url('student/exams')],
                ['label' => $participant['title'], 'url' => null]
            ]
        ]);
    })->name('exams.take');

    // Exam Auto-save
    $router->post('/exams/{id}/save', function ($id) {
        $user = auth();
        $request = \Core\App::getInstance()->getRequest();
        $db = \Core\Database::getInstance();

        if (!$request->hasValidCsrfToken()) {
            return \Core\Response::json(['success' => false, 'message' => 'Invalid CSRF'], 403);
        }

        $participant = $db->table('exam_participants ep')
            ->where('ep.id', '=', $id)
            ->where('ep.user_id', '=', $user->id)
            ->first();

        if (!$participant || $participant['status'] !== 'in_progress') {
            return \Core\Response::json(['success' => false, 'message' => 'Invalid state'], 400);
        }

        $answers = $request->input('answers', []);
        $questionNumber = (int)$request->input('question_number', 1);

        foreach ($answers as $questionId => $answer) {
            $db->table('attempts')->upsert([
                'exam_participant_id' => $id,
                'question_id' => $questionId,
                'answer' => json_encode($answer),
                'question_number' => $questionNumber,
            ], 'exam_participant_id,question_id');
        }

        return \Core\Response::json(['success' => true]);
    })->name('exams.save');

    // Exam Submit
    $router->post('/exams/{id}/submit', function ($id) {
        $user = auth();
        $request = \Core\App::getInstance()->getRequest();
        $db = \Core\Database::getInstance();

        if (!$request->hasValidCsrfToken()) {
            \Core\Session::flash('error', 'Token CSRF tidak valid');
            return \Core\Response::redirect(url('student/exams/' . $id . '/take'));
        }

        $participant = $db->table('exam_participants ep')
            ->where('ep.id', '=', $id)
            ->where('ep.user_id', '=', $user->id)
            ->first();

        if (!$participant || $participant['status'] !== 'in_progress') {
            \Core\Session::flash('error', 'Ujian tidak dapat diserahterimakan.');
            return \Core\Response::redirect(url('student/exams'));
        }

        // Save final answers
        $answers = $request->input('answers', []);
        // Get correct answers from questions
        $questions = $db->table('exam_questions eq')
            ->leftJoin('questions q', 'eq.question_id', '=', 'q.id')
            ->where('eq.exam_id', '=', $participant['exam_id'])
            ->select(['eq.question_id', 'q.type', 'q.content'])
            ->get();
        $correctAnswers = [];
        foreach ($questions as $q) {
            $content = json_decode($q['content'], true) ?? [];
            $correctAnswers[$q['question_id']] = match ($q['type']) {
                'mcq_single' => ['selected' => $content['correct_option'] ?? ''],
                'mcq_multiple' => ['selected' => array_column(array_filter($content['options'] ?? [], fn($o) => !empty($o['is_correct'])), 'id')],
                'true_false' => ['answer' => $content['correct_answer'] ?? 'true'],
                'fill_blank' => ['blanks' => array_column($content['blanks'] ?? [], 'answer')],
                'matching' => ['matches' => array_column($content['pairs'] ?? [], 'correct_right_idx', 'id')],
                'ordering' => ['order' => array_column($content['items'] ?? [], 'correct_order', 'id')],
                'essay' => ['text' => $content['answer'] ?? ''],
                default => [],
            };
        }
        foreach ($answers as $questionId => $answer) {
            $db->table('attempts')->upsert([
                'exam_participant_id' => $id,
                'question_id' => $questionId,
                'answer' => json_encode($answer),
                'correct_answer' => json_encode($correctAnswers[$questionId] ?? []),
                'question_number' => (int)$request->input('question_number', 1),
            ], 'exam_participant_id,question_id');
        }

        $finishedAt = date('Y-m-d H:i:s');
        $startedAt = $participant['started_at'] ?? $finishedAt;
        $duration = strtotime($finishedAt) - strtotime($startedAt);

        $db->table('exam_participants')
            ->where('id', '=', $id)
            ->update([
                'status' => 'submitted',
                'finished_at' => $finishedAt,
                'duration_seconds' => $duration,
            ]);

        // Auto-grade if no essays
        $hasEssays = $db->table('exam_questions eq')
            ->leftJoin('questions q', 'eq.question_id', '=', 'q.id')
            ->where('eq.exam_id', '=', $participant['exam_id'])
            ->where('q.type', '=', 'essay')
            ->count();

        if ($hasEssays === 0) {
            $grading = new \Modules\Exam\Grading();
            $result = $grading->gradeParticipant($id);
        }

        \Core\Session::flash('success', 'Ujian berhasil diserahterimakan.');
        return \Core\Response::redirect(url('student/results/' . $id));
    })->name('exams.submit');

    // Student Results
    $router->get('/results', function () {
        $user = auth();
        $request = \Core\App::getInstance()->getRequest();
        $page = (int) $request->query('page', 1);

        $results = \Core\Database::getInstance()->table('exam_participants ep')
            ->where('ep.user_id', '=', $user->id)
            ->whereIn('ep.status', ['graded', 'submitted'])
            ->leftJoin('exams e', 'ep.exam_id', '=', 'e.id')
            ->leftJoin('subjects s', 'e.subject_id', '=', 's.id')
            ->select(['ep.*', 'e.title as exam_title', 's.name as subject_name'])
            ->orderBy('ep.finished_at', 'desc')
            ->paginate(15, $page);

        return \Core\Response::view('student/results/index', [
            'title' => 'Hasil Ujian',
            'results' => $results,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('student/dashboard')],
                ['label' => 'Hasil Ujian', 'url' => null]
            ]
        ]);
    })->name('results');

    $router->get('/results/{id}', function ($id) {
        $user = auth();
        $db = \Core\Database::getInstance();

        $participant = $db->table('exam_participants ep')
            ->leftJoin('exams e', 'ep.exam_id', '=', 'e.id')
            ->leftJoin('subjects s', 'e.subject_id', '=', 's.id')
            ->where('ep.id', '=', $id)
            ->where('ep.user_id', '=', $user->id)
            ->select(['ep.*', 'e.*', 's.name as subject_name'])
            ->first();

        if (!$participant) abort(404);

        if ($participant['status'] === 'not_started' || $participant['status'] === 'in_progress') {
            return \Core\Response::redirect(url('student/exams'));
        }

        $attempts = $db->table('attempts a')
            ->leftJoin('questions q', 'a.question_id', '=', 'q.id')
            ->leftJoin('question_options qo', 'q.id', '=', 'qo.question_id')
            ->leftJoin('essay_gradings eg', 'a.id', '=', 'eg.attempt_id')
            ->where('a.exam_participant_id', '=', $id)
            ->select([
                'a.*',
                'q.type as question_type',
                'q.content as question_content',
                'q.explanation',
                'q.points',
                'eg.id as grading_id',
                'eg.score as grading_score',
                'eg.max_score as grading_max_score',
                'eg.feedback as grading_feedback',
                'eg.rubric_scores as grading_rubric_scores',
                'eg.status as grading_status',
                'eg.graded_at as grading_graded_at',
            ])
            ->orderBy('a.question_number')
            ->get();

        foreach ($attempts as &$a) {
            $a['question_content'] = json_decode($a['question_content'], true) ?? [];
            $a['answer'] = json_decode($a['answer'] ?? '{}', true) ?? [];
            $a['correct_answer'] = json_decode($a['correct_answer'] ?? '{}', true) ?? [];
            $a['essay_grading'] = $a['grading_id'] ? [
                'id' => $a['grading_id'],
                'score' => (float)$a['grading_score'],
                'max_score' => (float)$a['grading_max_score'],
                'feedback' => $a['grading_feedback'],
                'rubric_scores' => json_decode($a['grading_rubric_scores'] ?? '[]', true),
                'status' => $a['grading_status'],
                'graded_at' => $a['grading_graded_at'],
            ] : null;
        }

        return \Core\Response::view('student/results/show', [
            'title' => 'Hasil: ' . $participant['title'],
            'participant' => $participant,
            'attempts' => $attempts,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => url('student/dashboard')],
                ['label' => 'Hasil Ujian', 'url' => url('student/results')],
                ['label' => $participant['title'], 'url' => null]
            ]
        ]);
    })->name('results.show');
});

// Handle 404
$router->fallback(function () {
    return \Core\Response::view('errors/404', ['title' => 'Halaman Tidak Ditemukan'], 404);
});

// Run the app
$app->run();