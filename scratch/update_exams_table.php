<?php
require_once __DIR__ . '/../includes/db.php';
try {
    $pdo->exec("ALTER TABLE exams ADD COLUMN category VARCHAR(50) DEFAULT 'midterm'");
    echo "SUCCESS: Added category column.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
