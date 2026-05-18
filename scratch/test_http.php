<?php
$url = 'http://localhost/b%C3%A0i%20th%E1%BB%B1c%20h%C3%A0nh%204.2/download.php?file=' . urlencode('uploads/exams/questions/exam_q_1779042310.pdf') . '&name=' . urlencode('đề thi.pdf');
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'ignore_errors' => true // to get response even if it's 4xx or 5xx
    ]
]);
$response = file_get_contents($url, false, $context);
echo "Headers:\n";
print_r($http_response_header);
echo "\nContent Length (response body): " . strlen($response) . "\n";
if (strlen($response) < 500) {
    echo "Body:\n$response\n";
}
