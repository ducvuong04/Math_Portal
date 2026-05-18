<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_teacher()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$submission_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$submission_id) {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM assignment_submissions WHERE id = ?");
    $stmt->execute([$submission_id]);
    $submission = $stmt->fetch();

    if (!$submission) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy bài làm của học sinh']);
        exit;
    }

    $answers = [];
    if (!empty($submission['answers_json'])) {
        $answers = json_decode($submission['answers_json'], true);
    }

    $qStmt = $pdo->prepare("SELECT * FROM quizzes WHERE assignment_id = ? ORDER BY id ASC");
    $qStmt->execute([$submission['assignment_id']]);
    $quizzes = $qStmt->fetchAll();

    $details = [];
    foreach ($quizzes as $q) {
        if (empty(trim($q['question'])) || empty(trim($q['opt_a']))) continue;

        $selected = isset($answers[$q['id']]) ? (int)$answers[$q['id']] : null;
        $details[] = [
            'question' => $q['question'],
            'opt_a' => $q['opt_a'],
            'opt_b' => $q['opt_b'],
            'opt_c' => $q['opt_c'],
            'opt_d' => $q['opt_d'],
            'selected' => $selected,
            'correct' => (int)$q['answer']
        ];
    }

    echo json_encode(['success' => true, 'details' => $details]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
