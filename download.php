<?php
require_once 'includes/functions.php';

if (!is_logged_in()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}


$file = $_GET['file'] ?? '';
$name = $_GET['name'] ?? '';

if (empty($file) || empty($name)) {
    header('HTTP/1.1 400 Bad Request');
    exit('Missing parameters');
}

// Security: Prevent directory traversal
$realBase = realpath(__DIR__);
$realFile = realpath(__DIR__ . '/' . $file);

if ($realFile === false || strpos($realFile, $realBase) !== 0) {
    header('HTTP/1.1 404 Not Found');
    exit('File not found or invalid path');
}

if (!file_exists($realFile)) {
    header('HTTP/1.1 404 Not Found');
    exit('File not found');
}

// Aggressively sanitize filename for Windows: remove < > : " / \ | ? * and control characters
$name = preg_replace('/[\x00-\x1F\x7F<>:"\/\\\\|?*]/', '_', $name);
if (empty($name)) $name = 'downloaded_file.pdf';

// Determine Content-Type
$ext = strtolower(pathinfo($realFile, PATHINFO_EXTENSION));
$mime_types = [
    'pdf' => 'application/pdf',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'doc' => 'application/msword',
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'zip' => 'application/zip'
];
$contentType = $mime_types[$ext] ?? 'application/octet-stream';

// Set headers to force download
header('Content-Description: File Transfer');
header('Content-Type: ' . $contentType);

// Use standard filename attribute. Modern browsers handle UTF-8 perfectly well.
header('Content-Disposition: attachment; filename="' . $name . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($realFile));

// Clear ALL output buffers to avoid corrupted files
while (ob_get_level() > 0) {
    ob_end_clean();
}

readfile($realFile);
exit;
