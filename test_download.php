<?php
$_GET['file'] = 'uploads/exams/questions/exam_q_1779042310.pdf';
$_GET['name'] = 'đề thi thpt 2026.pdf';
$_SESSION['user'] = ['id' => 1, 'role' => 'student']; // Mock login

// inline download.php logic:
$file = $_GET['file'];
$name = $_GET['name'];
$realBase = realpath(__DIR__);
$realFile = realpath(__DIR__ . '/' . $file);

echo "Base: $realBase\n";
echo "File: " . __DIR__ . '/' . $file . "\n";
echo "RealFile: " . ($realFile === false ? "FALSE" : $realFile) . "\n";
echo "Exists: " . (file_exists(__DIR__ . '/' . $file) ? "YES" : "NO") . "\n";

if ($realFile === false || strpos($realFile, $realBase) !== 0) {
    echo "FAILED SECURITY CHECK\n";
} else {
    echo "SECURITY CHECK PASSED\n";
}
