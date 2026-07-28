<?php
require 'config/database.php';
$pdo = getDatabase();

try {
    // 1. Alter completion_status ENUM on tasks table
    $pdo->exec("ALTER TABLE tasks MODIFY COLUMN completion_status ENUM('pending', 'in_progress', 'submitted_for_review', 'needs_revision', 'completed') NOT NULL DEFAULT 'pending'");
    echo "Task completion_status ENUM updated.\n";
} catch (PDOException $e) {
    echo "Error updating ENUM: " . $e->getMessage() . "\n";
}

try {
    // 2. Create task_submissions table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `task_submissions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `task_id` int(11) NOT NULL,
            `volunteer_id` int(11) NOT NULL,
            `summary` text NOT NULL,
            `hours_contributed` decimal(5,2) DEFAULT NULL,
            `challenges_faced` text DEFAULT NULL,
            `suggestions` text DEFAULT NULL,
            `proof_file_paths` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`proof_file_paths`)),
            `coordinator_feedback` text DEFAULT NULL,
            `status` enum('submitted_for_review','approved','needs_revision') NOT NULL DEFAULT 'submitted_for_review',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `task_id` (`task_id`),
            KEY `volunteer_id` (`volunteer_id`),
            CONSTRAINT `fk_submissions_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk_submissions_volunteer` FOREIGN KEY (`volunteer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "task_submissions table created successfully.\n";
} catch (PDOException $e) {
    echo "Error creating task_submissions table: " . $e->getMessage() . "\n";
}
