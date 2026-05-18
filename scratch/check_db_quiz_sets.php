<?php
require_once 'includes/functions.php';

try {
    $stmt = $pdo->query("SHOW COLUMNS FROM quiz_sets");
    $columns = $stmt->fetchAll();
    echo "=== COLUMNS IN quiz_sets ===\n";
    foreach ($columns as $col) {
        echo "Field: {$col['Field']} | Type: {$col['Type']} | Null: {$col['Null']} | Key: {$col['Key']} | Default: {$col['Default']}\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
