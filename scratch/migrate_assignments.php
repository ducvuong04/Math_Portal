<?php
require_once __DIR__ . '/../includes/db.php';

try {
    $queries = [
        "CREATE TABLE IF NOT EXISTS `assignments` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `title` varchar(255) NOT NULL,
          `description` text,
          `type` enum('quiz', 'file') NOT NULL DEFAULT 'quiz',
          `deadline` datetime NOT NULL,
          `file_path` varchar(255) DEFAULT NULL,
          `teacher_id` int(11) NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "CREATE TABLE IF NOT EXISTS `assignment_submissions` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `assignment_id` int(11) NOT NULL,
          `student_id` int(11) NOT NULL,
          `file_path` varchar(255) NOT NULL,
          `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "CREATE TABLE IF NOT EXISTS `exams` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `title` varchar(255) NOT NULL,
          `question_file` varchar(255) NOT NULL,
          `answer_file` varchar(255) DEFAULT NULL,
          `teacher_id` int(11) NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    ];

    foreach ($queries as $q) {
        $pdo->exec($q);
    }

    echo "Migration successful: Tables created.";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
?>
