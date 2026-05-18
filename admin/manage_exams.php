<?php
require_once '../includes/functions.php';

if (!is_teacher()) redirect('../index.php');

$teacher_id = $_SESSION['user']['id'];

// Handle Deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("SELECT question_file, answer_file FROM exams WHERE id = ?");
    $stmt->execute([$id]);
    $exam = $stmt->fetch();
    if ($exam['question_file'] && file_exists("../".$exam['question_file'])) unlink("../".$exam['question_file']);
    if ($exam['answer_file'] && file_exists("../".$exam['answer_file'])) unlink("../".$exam['answer_file']);
    
    $pdo->prepare("DELETE FROM exams WHERE id = ?")->execute([$id]);
    redirect('manage_exams.php');
}

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $grade_category = $_POST['grade_category'] ?? '12';
    $category = $_POST['category'] ?? 'midterm';
    
    if ($grade_category === 'thpt') {
        $grade = 12; // THPT defaults to 12
    } else {
        $grade = intval($grade_category);
    }
    $success = true;
    
    $q_path = "";
    $orig_q = "";
    if (isset($_FILES['q_file']) && $_FILES['q_file']['error'] === 0) {
        $orig_q = $_FILES['q_file']['name'];
        $filename = "exam_q_" . time() . "." . pathinfo($orig_q, PATHINFO_EXTENSION);
        $target = "../uploads/exams/questions/" . $filename;
        if (move_uploaded_file($_FILES['q_file']['tmp_name'], $target)) {
            $q_path = "uploads/exams/questions/" . $filename;
        } else {
            $message = ["type" => "error", "text" => "Lỗi: Không thể di chuyển file đề thi vào thư mục lưu trữ."];
            $success = false;
        }
    } else {
        $message = ["type" => "error", "text" => "Lỗi: Vui lòng chọn file đề thi hợp lệ."];
        $success = false;
    }

    if ($success) {
        $a_path = "";
        $orig_a = "";
        if (isset($_FILES['a_file']) && $_FILES['a_file']['error'] === 0) {
            $orig_a = $_FILES['a_file']['name'];
            $filename = "exam_a_" . time() . "." . pathinfo($orig_a, PATHINFO_EXTENSION);
            $target_a = "../uploads/exams/answers/" . $filename;
            if (move_uploaded_file($_FILES['a_file']['tmp_name'], $target_a)) {
                $a_path = "uploads/exams/answers/" . $filename;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO exams (title, question_file, original_q_name, answer_file, original_a_name, teacher_id, grade, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $q_path, $orig_q, $a_path, $orig_a, $teacher_id, $grade, $category])) {
            $message = ["type" => "success", "text" => "Tải lên đề thi thành công!"];
        } else {
            $message = ["type" => "error", "text" => "Lỗi: Không thể lưu thông tin vào cơ sở dữ liệu."];
        }
    }
}

$exams = $pdo->query("SELECT * FROM exams ORDER BY created_at DESC")->fetchAll();

include '../includes/header.php';
?>

<?php if (isset($message)): ?>
    <div class="container" style="margin-top: 1rem;">
        <div class="animate" style="padding: 1.5rem; border-radius: 12px; background: <?php echo $message['type'] === 'success' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; border: 1px solid <?php echo $message['type'] === 'success' ? '#10b981' : '#ef4444'; ?>; color: <?php echo $message['type'] === 'success' ? '#10b981' : '#ef4444'; ?>; display: flex; align-items: center; gap: 10px;">
            <span class="material-icons-round"><?php echo $message['type'] === 'success' ? 'check_circle' : 'error'; ?></span>
            <span style="font-weight: 500;"><?php echo $message['text']; ?></span>
        </div>
    </div>
<?php endif; ?>

<main class="container">
    <div style="padding: 2rem 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Quản lý Đề thi</h1>
        </div>

        <div class="grid" style="grid-template-columns: 350px 1fr; align-items: start; gap: 2rem;">
            <div class="content-wrapper">
                <h3>Tải lên đề thi mới</h3>
                <form method="POST" enctype="multipart/form-data" style="margin-top: 1.5rem; display: grid; gap: 1rem;">
                    <div>
                        <label>Tiêu đề đề thi:</label>
                        <input type="text" name="title" required style="width: 100%;">
                    </div>
                    <div>
                        <label>Khối lớp / Kì thi:</label>
                        <select name="grade_category" id="grade_category_select" style="width: 100%;" onchange="updateCategoryOptions()">
                            <option value="10">Toán 10</option>
                            <option value="11">Toán 11</option>
                            <option value="12">Toán 12</option>
                            <option value="thpt">Kỳ thi THPT Quốc Gia</option>
                        </select>
                    </div>
                    <div>
                        <label>Phân loại đề thi:</label>
                        <select name="category" id="category_select" style="width: 100%;">
                            <!-- Options will be populated by JS -->
                        </select>
                    </div>
                    <div>
                        <label>File đề thi (Câu hỏi):</label>
                        <input type="file" name="q_file" required style="width: 100%; background: transparent !important; color: var(--text-main) !important;">
                    </div>
                    <div>
                        <label>File đáp án (nếu có):</label>
                        <input type="file" name="a_file" style="width: 100%; background: transparent !important; color: var(--text-main) !important;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem;">Tải lên</button>
                </form>
            </div>

            <div class="content-wrapper">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid var(--glass-border);">
                            <th style="padding: 1rem;">Đề thi</th>
                            <th>Câu hỏi</th>
                            <th>Đáp án</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exams as $e): ?>
                            <tr style="border-bottom: 1px solid var(--glass-border);">
                                <td style="padding: 1rem;">
                                    <strong><?php echo htmlspecialchars($e['title']); ?></strong>
                                    <p style="font-size: 0.8rem; opacity: 0.7;">
                                        <?php echo date('d/m/Y', strtotime($e['created_at'])); ?>
                                        | <strong style="color: var(--primary);">
                                            <?php echo (strpos($e['category'], 'thpt_') === 0) ? 'Kỳ thi THPT' : 'Lớp ' . $e['grade']; ?>
                                        </strong>
                                        | <span style="color: var(--accent);">
                                            <?php
                                            $cat_labels = [
                                                'midterm' => 'Giữa kì', 'final' => 'Cuối kì',
                                                'thpt_mock' => 'Thi thử THPT', 'thpt_official' => 'THPT Chính thức',
                                                'thpt_dgnl' => 'Đánh giá năng lực', 'thpt_prep' => 'Ôn thi THPT'
                                            ];
                                            echo $cat_labels[$e['category']] ?? 'Khác';
                                            ?>
                                        </span>
                                    </p>
                                </td>
                                <td>
                                    <?php 
                                        $q_ext = pathinfo($e['question_file'], PATHINFO_EXTENSION);
                                        $q_name = $e['original_q_name'] ?: $e['title'];
                                        if (pathinfo($q_name, PATHINFO_EXTENSION) !== $q_ext) {
                                            $q_name .= '.' . $q_ext;
                                        }
                                    ?>
                                    <div style="display: flex; flex-direction: column; gap: 8px;">
                                        <a href="../<?php echo htmlspecialchars($e['question_file']); ?>" target="_blank" style="color: var(--text-main); display: flex; align-items: center; gap: 5px; text-decoration: none; font-size: 0.9rem;">
                                            <span class="material-icons-round" style="font-size: 16px;">visibility</span> Xem trước
                                        </a>
                                        <a href="../download.php?file=<?php echo urlencode($e['question_file']); ?>&name=<?php echo urlencode($q_name); ?>" style="color: var(--primary); display: flex; align-items: center; gap: 5px; text-decoration: none; font-size: 0.9rem;">
                                            <span class="material-icons-round" style="font-size: 16px;">download</span> Tải về
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($e['answer_file']): ?>
                                        <?php 
                                            $a_ext = pathinfo($e['answer_file'], PATHINFO_EXTENSION);
                                            $a_name = $e['original_a_name'] ?: 'Dap-an-' . $e['title'];
                                            if (pathinfo($a_name, PATHINFO_EXTENSION) !== $a_ext) {
                                                $a_name .= '.' . $a_ext;
                                            }
                                        ?>
                                        <div style="display: flex; flex-direction: column; gap: 8px;">
                                            <a href="../<?php echo htmlspecialchars($e['answer_file']); ?>" target="_blank" style="color: var(--text-main); display: flex; align-items: center; gap: 5px; text-decoration: none; font-size: 0.9rem;">
                                                <span class="material-icons-round" style="font-size: 16px;">visibility</span> Xem đáp án
                                            </a>
                                            <a href="../download.php?file=<?php echo urlencode($e['answer_file']); ?>&name=<?php echo urlencode($a_name); ?>" style="color: var(--accent); display: flex; align-items: center; gap: 5px; text-decoration: none; font-size: 0.9rem;">
                                                <span class="material-icons-round" style="font-size: 16px;">download</span> Tải đáp án
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 0.9rem;">Trống</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?delete=<?php echo $e['id']; ?>" class="btn btn-outline" style="color: #ef4444; padding: 0.4rem; border-color: #ef4444;" onclick="return confirm('Xóa đề thi này?')">
                                        <span class="material-icons-round" style="font-size: 18px;">delete</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

<script>
function updateCategoryOptions() {
    const gc = document.getElementById('grade_category_select').value;
    const cat = document.getElementById('category_select');
    cat.innerHTML = '';
    
    if (gc === 'thpt') {
        cat.innerHTML += '<option value="thpt_mock">Đề thi thử THPT</option>';
        cat.innerHTML += '<option value="thpt_official">Đề THPT chính thức</option>';
        cat.innerHTML += '<option value="thpt_dgnl">Đề thi đánh giá năng lực</option>';
        cat.innerHTML += '<option value="thpt_prep">Tài liệu ôn thi THPT</option>';
    } else {
        cat.innerHTML += '<option value="midterm">Đề thi giữa kì</option>';
        cat.innerHTML += '<option value="final">Đề thi cuối kì</option>';
    }
}
document.addEventListener('DOMContentLoaded', updateCategoryOptions);
</script>
