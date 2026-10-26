<?php

declare(strict_types=1);

/**
 * Add status column to essay_gradings table
 */

return [
    'up' => function (\Core\Database $db) {
        // Check if column exists
        $columns = $db->query("SHOW COLUMNS FROM `essay_gradings` LIKE 'status'")->fetchAll();
        if (empty($columns)) {
            $db->execute("ALTER TABLE `essay_gradings` ADD COLUMN `status` ENUM('draft', 'graded') NOT NULL DEFAULT 'draft' AFTER `rubric_scores`");
        }
    },
    'down' => function (\Core\Database $db) {
        $columns = $db->query("SHOW COLUMNS FROM `essay_gradings` LIKE 'status'")->fetchAll();
        if (!empty($columns)) {
            $db->execute("ALTER TABLE `essay_gradings` DROP COLUMN `status`");
        }
    }
];