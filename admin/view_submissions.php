<?php
require_once '../includes/functions.php';

if (!is_teacher()) redirect('../index.php');

$assignment_id = $_GET['id'] ?? null;
if (!$assignment_id) redirect('manage_assignments.php');

// Fetch assignment details
$stmt = $pdo->prepare("SELECT * FROM assignments WHERE id = ?");
$stmt->execute([$assignment_id]);
$assignment = $stmt->fetch();

if (!$assignment) redirect('manage_assignments.php');

// Handle grading submission
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grade_submission'])) {
    $submission_id = $_POST['submission_id'] ?? null;
    $grade_score = $_POST['score'] ?? null;
    
    if ($submission_id !== null && $grade_score !== null) {
        $grade_score = floatval($grade_score);
        if ($grade_score >= 0 && $grade_score <= 10) {
            $update_stmt = $pdo->prepare("UPDATE assignment_submissions SET score = ? WHERE id = ?");
            $update_stmt->execute([$grade_score, $submission_id]);
            $message = ["type" => "success", "text" => "Đã lưu điểm thành công!"];
        } else {
            $message = ["type" => "error", "text" => "Điểm số phải nằm trong khoảng từ 0 đến 10!"];
        }
    }
}

// Fetch submissions (chỉ lấy lượt làm bài có điểm cao nhất của mỗi học sinh cho trắc nghiệm, và lượt mới nhất cho tự luận)
$orderBy = ($assignment['type'] === 'file') ? "s2.submitted_at DESC" : "s2.score DESC, s2.submitted_at DESC";
$stmt = $pdo->prepare("
    SELECT s.*, u.username 
    FROM assignment_submissions s 
    JOIN users u ON s.student_id = u.id 
    WHERE s.assignment_id = ?
      AND s.id = (
          SELECT s2.id 
          FROM assignment_submissions s2 
          WHERE s2.assignment_id = s.assignment_id 
            AND s2.student_id = s.student_id 
          ORDER BY $orderBy
          LIMIT 1
      )
    ORDER BY s.submitted_at DESC
");
$stmt->execute([$assignment_id]);
$submissions = $stmt->fetchAll();

include '../includes/header.php';
?>

<main class="container">
    <div style="padding: 2rem 0;">
        <a href="manage_assignments.php" style="color: var(--accent); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
            <span class="material-icons-round">arrow_back</span> Quản lý bài tập
        </a>
        <h1>Danh sách nộp bài</h1>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Bài tập: <strong><?php echo htmlspecialchars($assignment['title']); ?></strong></p>

        <?php if (!empty($message)): ?>
            <div style="padding: 1rem; border-radius: 8px; background: <?php echo $message['type'] === 'error' ? 'rgba(239, 68, 68, 0.1)' : 'rgba(16, 185, 129, 0.1)'; ?>; border: 1px solid <?php echo $message['type'] === 'error' ? '#ef4444' : '#10b981'; ?>; color: <?php echo $message['type'] === 'error' ? '#f87171' : '#34d399'; ?>; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <span class="material-icons-round"><?php echo $message['type'] === 'error' ? 'error_outline' : 'check_circle_outline'; ?></span>
                <span><?php echo htmlspecialchars($message['text']); ?></span>
            </div>
        <?php endif; ?>

        <div class="content-wrapper">
            <?php if (empty($submissions)): ?>
                <p style="text-align: center; padding: 2rem;">Chưa có học sinh nào nộp bài.</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid var(--glass-border);">
                            <th style="padding: 1rem;">Học sinh</th>
                            <th>Ngày nộp</th>
                            <th>Trạng thái</th>
                            <th>Bài làm</th>
                            <th>Điểm số / Chấm điểm</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $s): 
                            $is_late = strtotime($s['submitted_at']) > strtotime($assignment['deadline']);
                        ?>
                            <tr style="border-bottom: 1px solid var(--glass-border);">
                                <td style="padding: 1rem;"><strong><?php echo htmlspecialchars($s['username']); ?></strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($s['submitted_at'])); ?></td>
                                <td>
                                    <?php if ($is_late): ?>
                                        <span style="color: #ef4444; font-size: 0.85rem;">Nộp muộn</span>
                                    <?php else: ?>
                                        <span style="color: #10b981; font-size: 0.85rem;">Đúng hạn</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (strpos($s['file_path'], 'quiz_score:') === 0): 
                                        $parts = explode(':', $s['file_path']);
                                    ?>
                                        <span style="color: var(--text-muted); font-size: 0.9rem;">Trắc nghiệm (<?php echo $parts[2]; ?>)</span>
                                    <?php else: ?>
                                        <a href="../<?php echo htmlspecialchars($s['file_path']); ?>" download class="btn btn-outline" style="padding: 0.4rem 0.8rem; display: inline-flex; align-items: center; gap: 5px; width: fit-content; font-size: 0.85rem; border-radius: 6px;">
                                            <span class="material-icons-round" style="font-size: 16px;">download</span> Tải về bài làm
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (strpos($s['file_path'], 'quiz_score:') === 0): ?>
                                        <strong style="color: var(--accent); font-size: 1.1rem;"><?php echo number_format($s['score'], 1); ?> điểm</strong>
                                    <?php else: ?>
                                        <form method="POST" style="display: flex; align-items: center; gap: 8px; margin: 0;">
                                            <input type="hidden" name="grade_submission" value="1">
                                            <input type="hidden" name="submission_id" value="<?php echo $s['id']; ?>">
                                            <input type="number" name="score" min="0" max="10" step="0.1" 
                                                   value="<?php echo $s['score'] !== null ? $s['score'] : ''; ?>" 
                                                   placeholder="Chưa chấm" 
                                                   required
                                                   style="width: 100px; padding: 0.4rem; border-radius: 6px; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: var(--text); font-weight: 600; text-align: center;">
                                            <button type="submit" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; border-radius: 6px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                                <span class="material-icons-round" style="font-size: 16px;">check</span> Lưu
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
