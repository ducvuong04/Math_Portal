<?php
require_once __DIR__ . '/../includes/db.php';
try {
    $pdo->exec("ALTER TABLE assignments ADD COLUMN allow_review TINYINT(1) DEFAULT 1, ADD COLUMN show_answers TINYINT(1) DEFAULT 1");
    echo "SUCCESS: Added configuration columns.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
