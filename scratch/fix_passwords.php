<?php
require_once 'includes/db.php';

$teacher_hash = password_hash('teacher123', PASSWORD_DEFAULT);
$student_hash = password_hash('student123', PASSWORD_DEFAULT);

try {
    $pdo->prepare("UPDATE users SET password = ? WHERE username = 'teacher'")->execute([$teacher_hash]);
    $pdo->prepare("UPDATE users SET password = ? WHERE username = 'student'")->execute([$student_hash]);
    echo "Passwords updated successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
