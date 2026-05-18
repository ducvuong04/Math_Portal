<?php
require_once __DIR__ . '/../includes/db.php';

try {
    $pdo->exec("ALTER TABLE `quizzes` ADD COLUMN `assignment_id` INT(11) NULL AFTER `id`, ADD KEY (`assignment_id`);");
    echo "Migration successful: assignment_id column added to quizzes table.";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
?>
