<?php
require_once __DIR__ . '/../includes/db.php';
$stmt = $pdo->query("SELECT id, title, theory FROM topics");
while ($row = $stmt->fetch()) {
    if (strpos($row['theory'], 'http') !== false || strpos($row['theory'], 'href') !== false || strpos($row['theory'], '<a') !== false || strpos($row['theory'], '.jsp') !== false) {
        echo "ID: " . $row['id'] . "\n";
        echo "Title: " . $row['title'] . "\n";
        echo "Theory snippet:\n" . substr($row['theory'], 0, 1000) . "\n";
        echo "-----------------------------------------\n";
    }
}
?>
