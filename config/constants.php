<?php

declare(strict_types=1);

return [
    'roles' => [
        'admin' => 'Administrator',
        'teacher' => 'Guru',
        'student' => 'Siswa',
    ],

    'user_status' => [
        'active' => 'Aktif',
        'inactive' => 'Tidak Aktif',
        'pending' => 'Menunggu Verifikasi',
        'suspended' => 'Ditangguhkan',
    ],

    'exam_status' => [
        'draft' => 'Draft',
        'published' => 'Dipublikasikan',
        'ongoing' => 'Sedang Berlangsung',
        'completed' => 'Selesai',
        'archived' => 'Diarsipkan',
    ],

    'participant_status' => [
        'not_started' => 'Belum Mulai',
        'in_progress' => 'Sedang Mengerjakan',
        'submitted' => 'Dikumpulkan',
        'graded' => 'Dinilai',
        'absent' => 'Tidak Hadir',
    ],

    'question_types' => [
        'mcq_single' => 'Pilihan Ganda (Satu Jawaban)',
        'mcq_multiple' => 'Pilihan Ganda (Beberapa Jawaban)',
        'true_false' => 'Benar/Salah',
        'essay' => 'Uraian',
        'fill_blank' => 'Isian Singkat',
        'matching' => 'Pencocokan',
        'ordering' => 'Pengurutan',
    ],

    'question_difficulty' => [
        'easy' => 'Mudah',
        'medium' => 'Sedang',
        'hard' => 'Sulit',
    ],

    'media_types' => [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'audio' => ['mp3', 'ogg', 'wav'],
        'video' => ['mp4', 'webm', 'mov'],
    ],

    'pagination' => [
        'per_page' => (int) env('PAGINATION_PER_PAGE', 15),
    ],
];