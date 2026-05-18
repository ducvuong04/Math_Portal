<?php
require_once 'includes/db.php';

try {
    // 1. Create quiz_sets table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `quiz_sets` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `topic_id` int(11) NOT NULL,
      `title` varchar(255) NOT NULL,
      `description` text DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `topic_id` (`topic_id`),
      CONSTRAINT `quiz_sets_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Add quiz_set_id to quizzes table
    // Check if column exists first to avoid error
    $columns = $pdo->query("SHOW COLUMNS FROM `quizzes` LIKE 'quiz_set_id'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE `quizzes` ADD COLUMN `quiz_set_id` int(11) DEFAULT NULL;");
        $pdo->exec("ALTER TABLE `quizzes` ADD CONSTRAINT `fk_quiz_set` FOREIGN KEY (`quiz_set_id`) REFERENCES `quiz_sets` (`id`) ON DELETE CASCADE;");
    }

    // 3. Populate some initial quiz sets
    $stmt = $pdo->query("SELECT id FROM topics LIMIT 1");
    $topic = $stmt->fetch();
    if ($topic) {
        $topic_id = $topic['id'];
        
        // Check if sets already exist
        $setCheck = $pdo->query("SELECT COUNT(*) FROM quiz_sets")->fetchColumn();
        if ($setCheck == 0) {
            $pdo->prepare("INSERT INTO quiz_sets (topic_id, title, description) VALUES (?, ?, ?)")
                ->execute([$topic_id, 'Bộ đề cơ bản - Phần 1', 'Tổng hợp các câu hỏi nhận biết và thông hiểu.']);
            
            $set_id = $pdo->lastInsertId();
            
            // Assign existing quizzes to this set
            $pdo->prepare("UPDATE quizzes SET quiz_set_id = ? WHERE quiz_set_id IS NULL")->execute([$set_id]);
        }
    }

    echo "Database migration completed successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
