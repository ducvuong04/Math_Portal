<?php
require_once __DIR__ . '/../includes/db.php';

$tablesStmt = $pdo->query("SHOW TABLES");
$tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    echo "====================================\n";
    echo "TABLE: $table\n";
    echo "====================================\n";
    $descStmt = $pdo->query("DESCRIBE `$table`");
    while ($row = $descStmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  Field: {$row['Field']} | Type: {$row['Type']} | Null: {$row['Null']} | Default: {$row['Default']}\n";
    }
    echo "\n";
}
?>
