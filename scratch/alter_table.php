<?php
require_once __DIR__ . '/../includes/db.php';

try {
    $pdo->exec("ALTER TABLE assignments MODIFY COLUMN type ENUM('quiz', 'file', 'word_quiz') NOT NULL DEFAULT 'quiz'");
    echo "SUCCESS: Table schema updated.\n";
    
    // Now fix the existing ones
    $pdo->exec("UPDATE assignments SET type = 'word_quiz' WHERE id IN (SELECT DISTINCT assignment_id FROM quizzes) AND type != 'quiz'");
    echo "SUCCESS: Existing records updated.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
