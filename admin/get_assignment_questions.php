<?php
require_once '../includes/functions.php';

if (!is_teacher()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$assignment_id = $_GET['id'] ?? null;
if (!$assignment_id) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE assignment_id = ? ORDER BY id ASC");
$stmt->execute([$assignment_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($questions);
?>
