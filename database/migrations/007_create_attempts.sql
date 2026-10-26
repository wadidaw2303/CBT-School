-- Migration: 007_create_attempts.sql

-- Attempts (Student answers per question)
CREATE TABLE IF NOT EXISTS `attempts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid` CHAR(36) NOT NULL,
    `exam_participant_id` INT UNSIGNED NOT NULL,
    `question_id` INT UNSIGNED NOT NULL,
    `answer` JSON NULL,
    `is_correct` TINYINT(1) NULL DEFAULT NULL,
    `points_earned` DECIMAL(5,2) NULL DEFAULT NULL,
    `graded_by` INT UNSIGNED NULL DEFAULT NULL,
    `graded_at` TIMESTAMP NULL DEFAULT NULL,
    `is_submitted` TINYINT(1) NOT NULL DEFAULT 0,
    `submitted_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `attempts_uuid_unique` (`uuid`),
    UNIQUE KEY `attempts_participant_question_unique` (`exam_participant_id`, `question_id`),
    KEY `attempts_question_id_foreign` (`question_id`),
    KEY `attempts_graded_by_foreign` (`graded_by`),
    KEY `attempts_is_submitted_index` (`is_submitted`),
    CONSTRAINT `attempts_exam_participant_id_foreign` FOREIGN KEY (`exam_participant_id`) REFERENCES `exam_participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `attempts_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `attempts_graded_by_foreign` FOREIGN KEY (`graded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Essay Gradings (for manual grading of essay questions)
CREATE TABLE IF NOT EXISTS `essay_gradings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `attempt_id` INT UNSIGNED NOT NULL,
    `score` DECIMAL(5,2) NOT NULL,
    `max_score` DECIMAL(5,2) NOT NULL,
    `feedback` TEXT NULL,
    `rubric_scores` JSON NULL,
    `graded_by` INT UNSIGNED NOT NULL,
    `graded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `essay_gradings_attempt_id_unique` (`attempt_id`),
    KEY `essay_gradings_graded_by_foreign` (`graded_by`),
    CONSTRAINT `essay_gradings_attempt_id_foreign` FOREIGN KEY (`attempt_id`) REFERENCES `attempts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `essay_gradings_graded_by_foreign` FOREIGN KEY (`graded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exam Access Codes (for public exam links)
CREATE TABLE IF NOT EXISTS `exam_access_codes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `exam_id` INT UNSIGNED NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `max_uses` INT UNSIGNED NULL DEFAULT NULL,
    `used_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `expires_at` TIMESTAMP NULL DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `exam_access_codes_code_unique` (`code`),
    KEY `exam_access_codes_exam_id_foreign` (`exam_id`),
    KEY `exam_access_codes_created_by_foreign` (`created_by`),
    CONSTRAINT `exam_access_codes_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `exam_access_codes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;