<?php
require_once 'includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

include 'includes/header.php';

$user_id = $_SESSION['user']['id'];
$is_teacher = is_teacher();

$grade = isset($_GET['grade']) ? intval($_GET['grade']) : 12;
if ($grade !== 10 && $grade !== 11 && $grade !== 12) {
    $grade = 12;
}

// Fetch assignments filtered by grade
$stmt = $pdo->prepare("SELECT a.*, u.username as teacher_name FROM assignments a JOIN users u ON a.teacher_id = u.id WHERE a.grade = ? ORDER BY a.created_at DESC");
$stmt->execute([$grade]);
$assignments = $stmt->fetchAll();
?>

<main class="container">
    <section class="hero animate" style="padding: 4rem 0 2rem;">
        <h1>Bài tập về nhà Lớp <?php echo $grade; ?></h1>
        <p>Danh sách bài tập cần hoàn thành. Hãy chú ý thời hạn nộp bài!</p>
        <?php if ($is_teacher): ?>
            <a href="admin/manage_assignments.php" class="btn btn-primary">
                <span class="material-icons-round">settings</span> Quản lý bài tập
            </a>
        <?php endif; ?>
    </section>

    <div class="grid">
        <?php if (empty($assignments)): ?>
            <div class="card animate" style="grid-column: 1/-1; text-align: center;">
                <p>Hiện chưa có bài tập nào được giao.</p>
            </div>
        <?php else: ?>
            <?php foreach ($assignments as $a): 
                $deadline = new DateTime($a['deadline']);
                $now = new DateTime();
                $is_overdue = $now > $deadline;
                
                // Check if student has submitted
                $subStmt = $pdo->prepare("SELECT * FROM assignment_submissions WHERE assignment_id = ? AND student_id = ?");
                $subStmt->execute([$a['id'], $user_id]);
                $submission = $subStmt->fetch();
            ?>
                <div class="card animate">
                    <div class="card-icon" style="background: <?php echo in_array($a['type'], ['quiz', 'word_quiz']) ? 'rgba(16, 185, 129, 0.1)' : 'rgba(79, 70, 229, 0.1)'; ?>;">
                        <span class="material-icons-round"><?php echo in_array($a['type'], ['quiz', 'word_quiz']) ? 'quiz' : 'description'; ?></span>
                    </div>
                    <h3><?php echo htmlspecialchars($a['title']); ?></h3>
                    <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                        Loại: <strong><?php echo in_array($a['type'], ['quiz', 'word_quiz']) ? 'Trắc nghiệm' : 'Tự luận/File'; ?></strong>
                    </p>
                    <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1rem;">
                        Hạn nộp: <span style="color: <?php echo $is_overdue ? '#ef4444' : 'var(--accent)'; ?>;">
                            <?php echo $deadline->format('d/m/Y H:i'); ?>
                        </span>
                    </p>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <?php if ($submission): ?>
                            <span style="color: #10b981; font-size: 0.85rem; display: flex; align-items: center; gap: 4px;">
                                <span class="material-icons-round" style="font-size: 16px;">check_circle</span> Đã nộp bài
                            </span>
                        <?php elseif ($is_overdue): ?>
                            <span style="color: #ef4444; font-size: 0.85rem; display: flex; align-items: center; gap: 4px;">
                                <span class="material-icons-round" style="font-size: 16px;">error</span> Quá hạn
                            </span>
                        <?php else: ?>
                            <span style="color: var(--accent); font-size: 0.85rem; display: flex; align-items: center; gap: 4px;">
                                <span class="material-icons-round" style="font-size: 16px;">schedule</span> Đang chờ nộp
                            </span>
                        <?php endif; ?>
                    </div>

                    <a href="assignment_details.php?id=<?php echo $a['id']; ?>" class="btn btn-outline" style="width: 100%; text-align: center;">
                        Chi tiết
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
