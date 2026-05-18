<?php
require_once __DIR__ . '/../includes/db.php';

$count = 0;
$stmt = $pdo->query("SELECT id, file_path FROM assignment_submissions WHERE score IS NULL AND file_path LIKE 'quiz_score:%'");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $parts = explode(':', $row['file_path']);
    if(isset($parts[1])) {
        $score = (float)$parts[1];
        $upd = $pdo->prepare("UPDATE assignment_submissions SET score = ? WHERE id = ?");
        $upd->execute([$score, $row['id']]);
        $count++;
    }
}
echo "SUCCESS: Migrated $count scores.\n";
?>
