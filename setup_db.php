<?php
require_once 'includes/db.php';

try {
    $sql = file_get_contents('data/math_portal_12.sql');
    
    // The SQL file contains multiple queries, we need to execute them
    $pdo->exec($sql);
    
    echo "<h2 style='color: #10b981;'>Thiết lập Database thành công!</h2>";
    echo "<p>Đã khởi tạo các bảng và dữ liệu từ file <strong>data/math_portal_12.sql</strong>.</p>";
    echo "<a href='index.php'>Quay lại Trang chủ</a>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: #ef4444;'>Lỗi thiết lập:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
