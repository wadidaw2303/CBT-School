<?php

declare(strict_types=1);

return [
    [
        'name' => 'Administrator',
        'slug' => 'admin',
        'description' => 'Akses penuh ke seluruh sistem',
        'permissions' => json_encode(['*'], JSON_THROW_ON_ERROR),
    ],
    [
        'name' => 'Guru',
        'slug' => 'teacher',
        'description' => 'Mengelola soal, ujian, dan penilaian',
        'permissions' => json_encode([
            'questions.view',
            'questions.create',
            'questions.edit',
            'questions.delete',
            'questions.import',
            'exams.view',
            'exams.create',
            'exams.edit',
            'exams.delete',
            'exams.publish',
            'exams.duplicate',
            'grading.view',
            'grading.essay',
            'grading.release',
            'grading.finalize',
            'reports.view',
            'reports.export',
            'classes.view',
            'students.view',
        ], JSON_THROW_ON_ERROR),
    ],
    [
        'name' => 'Siswa',
        'slug' => 'student',
        'description' => 'Mengerjakan ujian dan melihat hasil',
        'permissions' => json_encode([
            'exams.view',
            'exams.take',
            'results.view',
            'profile.edit',
        ], JSON_THROW_ON_ERROR),
    ],
];