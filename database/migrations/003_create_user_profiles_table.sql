-- Migration: 003_create_user_profiles_table.sql
CREATE TABLE IF NOT EXISTS `user_profiles` (
    `user_id` INT UNSIGNED NOT NULL,
    `nisn` VARCHAR(20) NULL,
    `nis` VARCHAR(20) NULL,
    `class_id` INT UNSIGNED NULL,
    `major` VARCHAR(100) NULL,
    `birth_date` DATE NULL,
    `address` TEXT NULL,
    `parent_name` VARCHAR(150) NULL,
    `parent_phone` VARCHAR(20) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`),
    UNIQUE KEY `user_profiles_nisn_unique` (`nisn`),
    UNIQUE KEY `user_profiles_nis_unique` (`nis`),
    KEY `user_profiles_class_id_foreign` (`class_id`),
    CONSTRAINT `user_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;