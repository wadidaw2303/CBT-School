<?php

declare(strict_types=1);

return [
    'academic_years' => [
        [
            'name' => '2024/2025',
            'start_date' => '2024-07-01',
            'end_date' => '2025-06-30',
            'is_active' => true,
        ],
    ],
    'semesters' => [
        [
            'name' => 'Ganjil',
            'start_date' => '2024-07-01',
            'end_date' => '2024-12-31',
            'is_active' => true,
        ],
        [
            'name' => 'Genap',
            'start_date' => '2025-01-01',
            'end_date' => '2025-06-30',
            'is_active' => false,
        ],
    ],
    'subjects' => [
        ['code' => 'MTK', 'name' => 'Matematika', 'description' => 'Mata pelajaran Matematika', 'grade_level' => 10],
        ['code' => 'BIN', 'name' => 'Bahasa Indonesia', 'description' => 'Mata pelajaran Bahasa Indonesia', 'grade_level' => 10],
        ['code' => 'BIG', 'name' => 'Bahasa Inggris', 'description' => 'Mata pelajaran Bahasa Inggris', 'grade_level' => 10],
        ['code' => 'IPA', 'name' => 'Ilmu Pengetahuan Alam', 'description' => 'Mata pelajaran IPA', 'grade_level' => 10],
        ['code' => 'IPS', 'name' => 'Ilmu Pengetahuan Sosial', 'description' => 'Mata pelajaran IPS', 'grade_level' => 10],
        ['code' => 'PKN', 'name' => 'Pendidikan Kewarganegaraan', 'description' => 'Mata pelajaran PKN', 'grade_level' => 10],
        ['code' => 'AGM', 'name' => 'Pendidikan Agama', 'description' => 'Mata pelajaran Pendidikan Agama', 'grade_level' => 10],
        ['code' => 'PJK', 'name' => 'Pendidikan Jasmani', 'description' => 'Mata pelajaran PJK', 'grade_level' => 10],
        ['code' => 'SBK', 'name' => 'Seni Budaya', 'description' => 'Mata pelajaran Seni Budaya', 'grade_level' => 10],
        ['code' => 'TIK', 'name' => 'Teknologi Informasi', 'description' => 'Mata pelajaran TIK', 'grade_level' => 10],
    ],
];