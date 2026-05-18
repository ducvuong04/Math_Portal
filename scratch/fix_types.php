<?php
require_once __DIR__ . '/../includes/db.php';

// Cập nhật tất cả các bài tập có type trống về 'quiz' để kiểm tra
$stmt = $pdo->prepare("UPDATE assignments SET type = 'quiz' WHERE type = '' OR type IS NULL");
$stmt->execute();
echo "Updated " . $stmt->rowCount() . " assignments with empty types to 'quiz'.\n";

// Kiểm tra lại "Đề mẫu toán"
$stmt = $pdo->prepare("SELECT * FROM assignments WHERE title LIKE '%Đề mẫu toán%'");
$stmt->execute();
$task = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($task);
?>
