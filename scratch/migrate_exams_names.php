<?php
require_once __DIR__ . '/../includes/db.php';

try {
    $pdo->exec("ALTER TABLE `exams` 
        ADD COLUMN `original_q_name` VARCHAR(255) NULL AFTER `question_file`, 
        ADD COLUMN `original_a_name` VARCHAR(255) NULL AFTER `answer_file`;");
    echo "Migration successful: original name columns added to exams table.";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
?>
