<?php

declare(strict_types=1);

/**
 * Add question_number and correct_answer columns to attempts table
 */

return [
    'up' => function (\Core\Database $db) {
        // Add question_number
        $columns = $db->query("SHOW COLUMNS FROM `attempts` LIKE 'question_number'")->fetchAll();
        if (empty($columns)) {
            $db->execute("ALTER TABLE `attempts` ADD COLUMN `question_number` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `question_id`");
        }

        // Add correct_answer (to store the correct answer for display in review)
        $columns = $db->query("SHOW COLUMNS FROM `attempts` LIKE 'correct_answer'")->fetchAll();
        if (empty($columns)) {
            $db->execute("ALTER TABLE `attempts` ADD COLUMN `correct_answer` JSON NULL AFTER `answer`");
        }
    },
    'down' => function (\Core\Database $db) {
        $columns = $db->query("SHOW COLUMNS FROM `attempts` LIKE 'question_number'")->fetchAll();
        if (!empty($columns)) {
            $db->execute("ALTER TABLE `attempts` DROP COLUMN `question_number`");
        }
        $columns = $db->query("SHOW COLUMNS FROM `attempts` LIKE 'correct_answer'")->fetchAll();
        if (!empty($columns)) {
            $db->execute("ALTER TABLE `attempts` DROP COLUMN `correct_answer`");
        }
    }
];