<?php
require_once __DIR__ . '/../includes/db.php';

try {
    $stmt = $pdo->query("DESCRIBE exams");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in 'exams' table:\n";
    foreach ($cols as $c) {
        echo "- " . $c['Field'] . " (" . $c['Type'] . ")\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
