<?php
require_once __DIR__ . '/../includes/db.php';

$stmt = $pdo->query("SELECT id, title, theory FROM topics");
$all_links = [];
while ($row = $stmt->fetch()) {
    $theory = $row['theory'];
    if (empty($theory)) continue;
    
    // Simple regex to find <a> tags
    if (preg_match_all('/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $theory, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $all_links[] = [
                'topic_id' => $row['id'],
                'topic_title' => $row['title'],
                'href' => $match[1],
                'text' => strip_tags($match[2])
            ];
        }
    }
}

echo "Total links found: " . count($all_links) . "\n";
// Print first 50 links
$count = 0;
foreach ($all_links as $link) {
    $count++;
    echo "$count. [Topic ID {$link['topic_id']} - {$link['topic_title']}] Text: \"{$link['text']}\" -> Href: \"{$link['href']}\"\n";
    if ($count >= 100) {
        echo "... truncated ...\n";
        break;
    }
}
?>
