<?php
session_start();
define('BASE_URL', '/bài thực hành 4.2/');
require_once __DIR__ . '/db.php';

function is_logged_in() {
    if (isset($_SESSION['user']) && !is_array($_SESSION['user'])) {
        unset($_SESSION['user']); // Auto-fix corrupted session
        return false;
    }
    return isset($_SESSION['user']);
}

function is_teacher() {
    return isset($_SESSION['user']) && is_array($_SESSION['user']) && $_SESSION['user']['role'] === 'teacher';
}

function redirect($path) {
    header("Location: $path");
    exit();
}

// Fetch all chapters with their topics (optional grade filter)
function get_chapters_with_topics($grade = null) {
    global $pdo;
    $chapters = [];
    if ($grade !== null) {
        $chapStmt = $pdo->prepare("SELECT * FROM chapters WHERE grade = ?");
        $chapStmt->execute([$grade]);
    } else {
        $chapStmt = $pdo->query("SELECT * FROM chapters");
    }
    while ($chap = $chapStmt->fetch()) {
        $chapter_id = $chap['id'];
        $chapters[$chapter_id] = [
            'title' => $chap['title'],
            'icon' => $chap['icon'],
            'topics' => []
        ];
        
        $topStmt = $pdo->prepare("SELECT * FROM topics WHERE chapter_id = ?");
        $topStmt->execute([$chapter_id]);
        while ($topic = $topStmt->fetch()) {
            $chapters[$chapter_id]['topics'][] = [
                'id' => $topic['id'],
                'title' => $topic['title'],
                'description' => $topic['description']
            ];
        }
    }
    return $chapters;
}

// Fetch quiz sets for a specific topic
function get_quiz_sets_by_topic($topic_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM quiz_sets WHERE topic_id = ?");
    $stmt->execute([$topic_id]);
    return $stmt->fetchAll();
}

// Get specific quiz set details
function get_quiz_set($set_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM quiz_sets WHERE id = ?");
    $stmt->execute([$set_id]);
    return $stmt->fetch();
}

// Count how many times a user has attempted a quiz set
function get_user_attempts($user_id, $set_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM quiz_attempts WHERE user_id = ? AND quiz_set_id = ?");
    $stmt->execute([$user_id, $set_id]);
    return (int)$stmt->fetchColumn();
}

// Fetch questions for a specific quiz set
function get_quizzes_by_set($set_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE quiz_set_id = ?");
    $stmt->execute([$set_id]);
    $quizzes = [];
    while ($q = $stmt->fetch()) {
        $quizzes[] = [
            'question' => $q['question'],
            'options' => [$q['opt_a'], $q['opt_b'], $q['opt_c'], $q['opt_d']],
            'answer' => $q['answer']
        ];
    }
    return $quizzes;
}

// Legacy function - kept for compatibility but updated to use the new logic
// Legacy function - kept for compatibility but updated to use the new logic
function get_all_content($grade = null) {
    global $pdo;
    $chapters = [];
    if ($grade !== null) {
        $chapStmt = $pdo->prepare("SELECT * FROM chapters WHERE grade = ?");
        $chapStmt->execute([$grade]);
    } else {
        $chapStmt = $pdo->query("SELECT * FROM chapters");
    }
    while ($chap = $chapStmt->fetch()) {
        $key = $chap['chapter_key'];
        $chapters[$key] = [
            'title' => $chap['title'],
            'icon' => $chap['icon'],
            'topics' => []
        ];
        
        $topStmt = $pdo->prepare("SELECT * FROM topics WHERE chapter_id = ?");
        $topStmt->execute([$chap['id']]);
        while ($topic = $topStmt->fetch()) {
            $formStmt = $pdo->prepare("SELECT formula_text FROM formulas WHERE topic_id = ?");
            $formStmt->execute([$topic['id']]);
            $formulas = $formStmt->fetchAll(PDO::FETCH_COLUMN);
            
            $chapters[$key]['topics'][] = [
                'id' => $topic['topic_id_str'],
                'title' => $topic['title'],
                'description' => $topic['description'],
                'theory' => $topic['theory'],
                'video_url' => $topic['video_url'] ?? '',
                'formulas' => $formulas
            ];
        }
    }
    
    // Default to first set if available for legacy support
    $quizStmt = $pdo->query("SELECT * FROM quizzes LIMIT 20");
    $quizzes = [];
    while ($q = $quizStmt->fetch()) {
        $quizzes[] = [
            'question' => $q['question'],
            'options' => [$q['opt_a'], $q['opt_b'], $q['opt_c'], $q['opt_d']],
            'answer' => $q['answer']
        ];
    }
    
    return ['chapters' => $chapters, 'quizzes' => $quizzes];
}

function parse_markdown($text) {
    if (strpos($text, '<div') !== false || strpos($text, '<p') !== false || strpos($text, '<img') !== false) {
        return $text;
    }
    // 1. Convert headers
    $text = preg_replace('/###\s+(.*?)(?=\r?\n|$)/', '<h3 style="margin-top: 1.5rem; margin-bottom: 0.8rem; color: var(--text-main); font-weight: 600; font-size: 1.25rem;">$1</h3>', $text);
    $text = preg_replace('/##\s+(.*?)(?=\r?\n|$)/', '<h2 style="margin-top: 1.8rem; margin-bottom: 1rem; color: var(--text-main); font-weight: 600; font-size: 1.5rem;">$1</h2>', $text);
    $text = preg_replace('/#\s+(.*?)(?=\r?\n|$)/', '<h1 style="margin-top: 2rem; margin-bottom: 1.2rem; color: var(--text-main); font-weight: 700; font-size: 1.8rem;">$1</h1>', $text);

    // 2. Convert bold text
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);

    // 3. Convert lists
    $text = preg_replace('/^\s*[\*\-]\s+(.*?)(?=\r?\n|$)/m', '<li style="margin-left: 1.2rem; list-style-type: disc; margin-bottom: 0.6rem; color: var(--text-main); line-height: 1.6;">$1</li>', $text);

    // 4. Convert newlines to breaks, but clean up breaks after headers/lists
    $text = nl2br($text);
    $text = preg_replace('/<\/h3><br\s*\/?>/', '</h3>', $text);
    $text = preg_replace('/<\/h2><br\s*\/?>/', '</h2>', $text);
    $text = preg_replace('/<\/h1><br\s*\/?>/', '</h1>', $text);
    $text = preg_replace('/<\/li><br\s*\/?>/', '</li>', $text);

    return $text;
}
