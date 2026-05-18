<?php
require_once __DIR__ . '/../includes/db.php';
$stmt = $pdo->prepare("SELECT id, title, theory FROM topics WHERE title LIKE ? OR theory LIKE ?");
$stmt->execute(['%xu thế trung tâm%', '%xu thế trung tâm%']);
while ($row = $stmt->fetch()) {
    echo "ID: " . $row['id'] . " | Title: " . $row['title'] . "\n";
    // Find all list items containing links in theory
    if (preg_match_all('/<li[^>]*>.*?<a[^>]*>.*?<\/a>.*?<\/li>/is', $row['theory'], $matches)) {
        foreach ($matches[0] as $m) {
            echo "   MATCHED LI: " . trim(strip_tags($m)) . "\n";
            echo "   RAW LI: " . trim($m) . "\n";
        }
    }
}
?>
