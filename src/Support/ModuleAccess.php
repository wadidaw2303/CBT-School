<?php

namespace App\Support;

class ModuleAccess
{
    public static $roleModules = [
        'super_admin' => ['*'],
        'admin' => ['core', 'academic', 'assessment', 'admin', 'communication'],
        'administrasi' => ['core', 'academic', 'assessment.certificates', 'assessment.reports', 'admin.users', 'admin.enrollment', 'communication.announcements'],
        'guru' => ['core', 'academic.exams.own', 'academic.classes.own', 'academic.questions.own', 'assessment.grading', 'assessment.analytics.own', 'communication.announcements'],
        'kepala_sekolah' => ['core', 'assessment.analytics', 'assessment.reports.readonly', 'assessment.certificates.verify', 'communication.announcements.broadcast'],
        'proktor' => ['core', 'assessment.proctoring', 'assessment.sessions'],
        'murid' => ['core', 'academic.exams.take', 'academic.schedule', 'assessment.results.own', 'assessment.certificates.own', 'communication.announcements.read'],
        'orang_tua' => ['core', 'assessment.results.child', 'academic.schedule.child', 'communication.announcements.read'],
    ];

    public static function can(string $role, string $module): bool
    {
        $modules = self::$roleModules[$role] ?? [];
        if (in_array('*', $modules, true)) {
            return true;
        }

        // Support wildcard: assessment.* matches assessment.grading
        foreach ($modules as $m) {
            if ($m === $module) {
                return true;
            }
            if (str_ends_with($m, '.*') && str_starts_with($module, rtrim($m, '.*'))) {
                return true;
            }
        }

        return false;
    }

    public static function getAccessibleModules(string $role): array
    {
        return self::$roleModules[$role] ?? ['core'];
    }

    // Blade directive: @can('module.name')
    public static function bladeCan(string $module): bool
    {
        $user = auth()->user();
        return $user ? self::can($user->role, $module) : false;
    }
}