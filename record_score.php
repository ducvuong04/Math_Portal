<?php
require_once 'includes/functions.php';

if (!is_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$user_id = $_SESSION['user']['id'];
$set_id = (int)$_POST['set_id'];
$score = (int)$_POST['score'];
$total = (int)$_POST['total'];
$time_spent = (int)$_POST['time_spent'];

try {
    $set = get_quiz_set($set_id);
    $attempts = get_user_attempts($user_id, $set_id);
    $has_max_attempts = !empty($set['max_attempts']) && $set['max_attempts'] > 0;
    if ($has_max_attempts && $attempts >= $set['max_attempts']) {
        die(json_encode(['success' => false, 'message' => 'Đã hết số lần làm bài!']));
    }

    $stmt = $pdo->prepare("INSERT INTO quiz_attempts (user_id, quiz_set_id, score, total_questions, time_spent) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $set_id, $score, $total, $time_spent]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
