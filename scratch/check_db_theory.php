<?php
require_once __DIR__ . '/../includes/db.php';
$stmt = $pdo->prepare("SELECT theory FROM topics WHERE topic_id_str = ?");
$stmt->execute(['menh-de']);
$theory = $stmt->fetchColumn();
echo "RAW THEORY FROM DB:\n";
var_dump($theory);
?>
