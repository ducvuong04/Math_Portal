<?php
require_once 'includes/functions.php';

echo "=== Columns of topics ===\n";
$stmt = $pdo->query("DESCRIBE topics");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
