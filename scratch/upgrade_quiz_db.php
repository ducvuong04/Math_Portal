<?php
require_once __DIR__ . '/../includes/db.php';

try {
    // 1. Cập nhật bảng assignments: Thêm thời gian làm bài và số lần làm lại
    $pdo->exec("ALTER TABLE assignments ADD COLUMN time_limit INT DEFAULT 0 COMMENT 'Thời gian làm bài (phút), 0 là không giới hạn'");
    $pdo->exec("ALTER TABLE assignments ADD COLUMN max_attempts INT DEFAULT 1 COMMENT 'Số lần làm bài tối đa'");

    // 2. Cập nhật bảng assignment_submissions: Thêm cột lưu đáp án đã chọn và điểm số
    $pdo->exec("ALTER TABLE assignment_submissions ADD COLUMN answers_json TEXT COMMENT 'Lưu trữ đáp án học sinh đã chọn dạng JSON'");
    $pdo->exec("ALTER TABLE assignment_submissions ADD COLUMN score DECIMAL(4,2) DEFAULT NULL");
    
    echo "SUCCESS: Database upgraded for advanced assignment features.\n";
} catch (Exception $e) {
    echo "INFO: Some columns might already exist. Error: " . $e->getMessage() . "\n";
}
?>
