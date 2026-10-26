-- Migration: 005_create_question_bank.sql

-- Question Categories
CREATE TABLE IF NOT EXISTS `question_categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `parent_id` INT UNSIGNED NULL,
    `subject_id` INT UNSIGNED NOT NULL,
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `question_categories_slug_subject_unique` (`slug`, `subject_id`),
    KEY `question_categories_parent_id_foreign` (`parent_id`),
    KEY `question_categories_subject_id_foreign` (`subject_id`),
    KEY `question_categories_created_by_foreign` (`created_by`),
    CONSTRAINT `question_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `question_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `question_categories_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `question_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Questions
CREATE TABLE IF NOT EXISTS `questions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid` CHAR(36) NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    `subject_id` INT UNSIGNED NOT NULL,
    `type` ENUM('mcq_single','mcq_multiple','true_false','essay','fill_blank','matching','ordering') NOT NULL,
    `difficulty` ENUM('easy','medium','hard') NOT NULL DEFAULT 'medium',
    `content` JSON NOT NULL,
    `explanation` TEXT NULL,
    `points` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    `time_limit` SMALLINT UNSIGNED NULL DEFAULT NULL COMMENT 'in seconds, null = no limit',
    `media` JSON NULL,
    `tags` JSON NULL,
    `status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `questions_uuid_unique` (`uuid`),
    KEY `questions_category_id_foreign` (`category_id`),
    KEY `questions_subject_id_foreign` (`subject_id`),
    KEY `questions_created_by_foreign` (`created_by`),
    KEY `questions_type_index` (`type`),
    KEY `questions_difficulty_index` (`difficulty`),
    KEY `questions_status_index` (`status`),
    CONSTRAINT `questions_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `question_categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `questions_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `questions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Question Options (for MCQ, True/False, Matching, Ordering)
CREATE TABLE IF NOT EXISTS `question_options` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `question_id` INT UNSIGNED NOT NULL,
    `option_key` VARCHAR(10) NOT NULL COMMENT 'A, B, C, D or 1, 2, 3...',
    `content` TEXT NOT NULL,
    `is_correct` TINYINT(1) NOT NULL DEFAULT 0,
    `media` JSON NULL,
    `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `question_options_question_id_foreign` (`question_id`),
    CONSTRAINT `question_options_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Question Media (for images, audio, video in questions)
CREATE TABLE IF NOT EXISTS `question_media` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `question_id` INT UNSIGNED NOT NULL,
    `type` ENUM('image','audio','video') NOT NULL,
    `path` VARCHAR(255) NOT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `size` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `question_media_question_id_foreign` (`question_id`),
    CONSTRAINT `question_media_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;