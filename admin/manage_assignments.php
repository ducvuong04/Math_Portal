<?php
require_once '../includes/functions.php';
require_once '../includes/WordQuizImporter.php';

if (!is_teacher()) redirect('../index.php');

// Kiểm tra xem đã cài đặt PHPWord qua Composer chưa
$has_phpword = file_exists('../vendor/autoload.php');
if ($has_phpword) {
    require_once '../vendor/autoload.php';
}

$teacher_id = $_SESSION['user']['id'];

// Handle Deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // Delete files first
    $stmt = $pdo->prepare("SELECT file_path FROM assignments WHERE id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetchColumn();
    if ($file && file_exists("../".$file)) unlink("../".$file);
    
    $pdo->prepare("DELETE FROM assignments WHERE id = ?")->execute([$id]);
    redirect('manage_assignments.php');
}

// Handle Add/Edit
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $deadline = $_POST['deadline'];
    $type = $_POST['type'];
    $grade = isset($_POST['grade']) ? intval($_POST['grade']) : 12;
    $id = $_POST['id'] ?? null;

    $file_path = $_POST['existing_file'] ?? null;
    
    try {
        // Xử lý upload file (đề bài hoặc file word trắc nghiệm)
        if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
            $file = $_FILES['file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Nếu là loại trắc nghiệm từ Word, bắt buộc phải là .docx
            if ($type === 'word_quiz' && $ext !== 'docx') {
                throw new Exception("Vui lòng chỉ upload file định dạng .docx cho bài tập trắc nghiệm Word.");
            }

            $filename = "task_" . time() . "." . $ext;
            $new_path = "uploads/assignments/questions/" . $filename;
            if (move_uploaded_file($file['tmp_name'], "../" . $new_path)) {
                if ($file_path && file_exists("../".$file_path)) unlink("../".$file_path);
                $file_path = $new_path;
            }
        }

        // 1. Thêm hoặc cập nhật bài tập vào bảng assignments
        $time_limit = (int)($_POST['time_limit'] ?? 0);
        $max_attempts = (int)($_POST['max_attempts'] ?? 1);
        $allow_review = isset($_POST['allow_review']) ? 1 : 0;
        $show_answers = isset($_POST['show_answers']) ? 1 : 0;

        if ($id) {
            $stmt = $pdo->prepare("UPDATE assignments SET title = ?, description = ?, deadline = ?, type = ?, file_path = ?, time_limit = ?, max_attempts = ?, allow_review = ?, show_answers = ?, grade = ? WHERE id = ?");
            $stmt->execute([$title, $description, $deadline, $type, $file_path, $time_limit, $max_attempts, $allow_review, $show_answers, $grade, $id]);
            $assignment_id = $id;
        } else {
            $stmt = $pdo->prepare("INSERT INTO assignments (title, description, deadline, teacher_id, type, file_path, time_limit, max_attempts, allow_review, show_answers, grade) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $deadline, $teacher_id, $type, $file_path, $time_limit, $max_attempts, $allow_review, $show_answers, $grade]);
            $assignment_id = $pdo->lastInsertId();
        }

        // 2. Xử lý câu hỏi trắc nghiệm
        if ($type === 'quiz' && isset($_POST['questions'])) {
            if ($id) $pdo->prepare("DELETE FROM quizzes WHERE assignment_id = ?")->execute([$id]);
            
            foreach ($_POST['questions'] as $q) {
                if (!empty($q['text'])) {
                    $stmt = $pdo->prepare("INSERT INTO quizzes (assignment_id, question, opt_a, opt_b, opt_c, opt_d, answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$assignment_id, $q['text'], $q['a'], $q['b'], $q['c'], $q['d'], $q['correct']]);
                }
            }
            $message = ["type" => "success", "text" => "Đã lưu bài tập trắc nghiệm thủ công."];
        } 
        elseif ($type === 'word_quiz') {
            if (!$has_phpword) {
                throw new Exception("Hệ thống chưa cài đặt PHPWord. Vui lòng chạy 'composer require phpoffice/phpword' để sử dụng tính năng này.");
            }
            if ($file_path) {
                $importer = new WordQuizImporter($pdo);
                $count = $importer->import("../" . $file_path, $assignment_id);
                $message = ["type" => "success", "text" => "Thành công! Đã nhập $count câu hỏi từ file Word."];
            }
        } else {
            $message = ["type" => "success", "text" => "Đã lưu bài tập thành công."];
        }

    } catch (Exception $e) {
        $message = ["type" => "error", "text" => $e->getMessage()];
    }
}

// Fetch assignments và đếm số lượng bài nộp (chỉ đếm số học sinh duy nhất đã nộp bài)
$stmt = $pdo->query("SELECT a.*, (SELECT COUNT(DISTINCT student_id) FROM assignment_submissions WHERE assignment_id = a.id) as sub_count FROM assignments a ORDER BY created_at DESC");
$assignments = $stmt->fetchAll();

include '../includes/header.php';
?>

<style>
    .question-card {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        position: relative;
    }
    .question-card h4 { margin-bottom: 1rem; color: var(--accent); }
    .ans-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem; }
    .ans-grid input { background: rgba(255,255,255,0.05) !important; border: 1px solid var(--glass-border) !important; }
</style>

<main class="container">
    <?php if (isset($message) && !empty($message)): ?>
        <div class="animate" style="margin-top: 1rem; padding: 1.5rem; border-radius: 12px; background: <?php echo $message['type'] === 'success' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; border: 1px solid <?php echo $message['type'] === 'success' ? '#10b981' : '#ef4444'; ?>; color: <?php echo $message['type'] === 'success' ? '#10b981' : '#ef4444'; ?>; display: flex; align-items: center; gap: 10px;">
            <span class="material-icons-round"><?php echo $message['type'] === 'success' ? 'check_circle' : 'error'; ?></span>
            <span style="font-weight: 500;"><?php echo $message['text']; ?></span>
        </div>
    <?php endif; ?>

    <div style="padding: 2rem 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Quản lý Bài tập</h1>
            <button onclick="showAddForm()" class="btn btn-primary">
                + Thêm bài tập mới
            </button>
        </div>

        <!-- Add/Edit Form -->
        <div id="assignment-form" class="content-wrapper" style="display: none; margin-bottom: 2rem; border-top: 4px solid var(--primary);">
            <h3 id="form-title" style="margin-bottom: 1.5rem;">Thêm bài tập mới</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="task-id">
                <input type="hidden" name="existing_file" id="task-existing-file">
                
                <div style="display: grid; grid-template-columns: 1fr 200px 150px; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label>Tiêu đề:</label>
                        <input type="text" name="title" id="task-title" required style="width: 100%;">
                    </div>
                    <div>
                        <label>Loại bài tập:</label>
                        <select name="type" id="task-type" style="width: 100%;" onchange="toggleQuizSection(this.value)">
                            <option value="file">Tự luận / Upload file</option>
                            <option value="quiz">Trắc nghiệm Online (Thủ công)</option>
                            <option value="word_quiz">Trắc nghiệm từ file Word (.docx)</option>
                        </select>
                        <p id="word-note" style="display: none; font-size: 0.8rem; color: var(--accent); margin-top: 5px; opacity: 0.8;">
                            * File Word mẫu: Câu 1: [Câu hỏi] A. [Đáp án] B. ... Đáp án: A
                        </p>
                    </div>
                    <div>
                        <label>Khối lớp:</label>
                        <select name="grade" id="task-grade" style="width: 100%;">
                            <option value="12">Toán 12</option>
                            <option value="11">Toán 11</option>
                            <option value="10">Toán 10</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label>Mô tả / Hướng dẫn:</label>
                    <textarea name="description" id="task-desc" style="width: 100%; height: 80px;"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label>Hạn nộp (Deadline):</label>
                        <input type="datetime-local" name="deadline" id="task-deadline" required style="width: 100%;">
                    </div>
                    <div>
                        <label>Thời gian làm bài (Phút):</label>
                        <input type="number" name="time_limit" id="task-time-limit" value="0" min="0" style="width: 100%;" placeholder="0 = Không giới hạn">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label>Số lần làm lại tối đa:</label>
                        <input type="number" name="max_attempts" id="task-max-attempts" value="1" min="1" style="width: 100%;">
                    </div>
                    <div id="file-upload-section">
                        <label>File đề bài / Word trắc nghiệm:</label>
                        <input type="file" name="file" style="width: 100%; background: transparent !important; color: var(--text-main) !important;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; background: rgba(255,255,255,0.03); padding: 1rem; border-radius: 10px; border: 1px solid var(--glass-border);">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.9rem;">
                        <input type="checkbox" name="allow_review" id="task-allow-review" value="1" checked>
                        Cho phép học sinh xem lại bài làm
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.9rem;">
                        <input type="checkbox" name="show_answers" id="task-show-answers" value="1" checked>
                        Cho phép học sinh xem đáp án đúng
                    </label>
                </div>

                <!-- Quiz Questions Section -->
                <div id="quiz-questions-section" style="display: none; margin-bottom: 2rem; padding-top: 1rem; border-top: 1px dashed var(--glass-border);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h3>Danh sách câu hỏi</h3>
                        <button type="button" onclick="addQuestion()" class="btn btn-outline" style="border-color: var(--accent); color: var(--accent);">
                            <span class="material-icons-round">add</span> Thêm câu hỏi
                        </button>
                    </div>
                    <div id="questions-container">
                        <!-- Questions will be added here -->
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1.5rem; border-top: 1px solid var(--glass-border);">
                    <button type="button" onclick="hideForm()" class="btn btn-outline">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary" style="min-width: 150px;">Lưu bài tập</button>
                </div>
            </form>
        </div>

        <div class="content-wrapper">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 1px solid var(--glass-border);">
                        <th style="padding: 1rem;">Bài tập</th>
                        <th style="padding: 1rem;">Loại</th>
                        <th style="padding: 1rem;">Hạn nộp</th>
                        <th style="padding: 1rem;">Đã nộp</th>
                        <th style="padding: 1rem;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments as $a): ?>
                        <tr style="border-bottom: 1px solid var(--glass-border);">
                            <td style="padding: 1rem;">
                                <strong><?php echo htmlspecialchars($a['title']); ?></strong>
                                <p style="font-size: 0.8rem; color: var(--text-muted);">
                                    <?php echo date('d/m/Y', strtotime($a['created_at'])); ?> 
                                    | <span style="color: var(--accent);"><?php echo $a['time_limit'] > 0 ? $a['time_limit'].'p' : 'Ko giới hạn'; ?></span>
                                    | Lượt làm: <?php echo $a['max_attempts']; ?>
                                    | <strong style="color: var(--primary);">Lớp <?php echo $a['grade']; ?></strong>
                                </p>
                            </td>
                            <td style="padding: 1rem;">
                                <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; background: <?php echo $a['type'] === 'file' ? 'rgba(79, 70, 229, 0.1)' : 'rgba(16, 185, 129, 0.1)'; ?>; color: <?php echo $a['type'] === 'file' ? 'var(--primary)' : '#10b981'; ?>;">
                                    <span class="material-icons-round" style="font-size: 14px;"><?php echo $a['type'] === 'file' ? 'description' : 'quiz'; ?></span>
                                    <?php echo $a['type'] === 'file' ? 'Tự luận' : 'Trắc nghiệm'; ?>
                                </span>
                            </td>
                            <td style="padding: 1rem;"><?php echo date('d/m/Y H:i', strtotime($a['deadline'])); ?></td>
                            <td style="padding: 1rem;">
                                <a href="view_submissions.php?id=<?php echo $a['id']; ?>" style="color: var(--accent); text-decoration: none; font-weight: 600;">
                                    <?php echo $a['sub_count']; ?> bài nộp
                                </a>
                            </td>
                            <td style="padding: 1rem;">
                                <div style="display: flex; gap: 0.5rem;">
                                    <button onclick='editTask(<?php echo json_encode($a); ?>)' class="btn btn-outline" style="padding: 0.4rem; border-color: var(--primary); color: var(--primary);"><span class="material-icons-round" style="font-size: 18px;">edit</span></button>
                                    <a href="?delete=<?php echo $a['id']; ?>" class="btn btn-outline" style="padding: 0.4rem; border-color: #ef4444; color: #ef4444;" onclick="return confirm('Xóa bài tập này?')"><span class="material-icons-round" style="font-size: 18px;">delete</span></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
let qCount = 0;

function showAddForm() {
    hideForm();
    document.getElementById('assignment-form').style.display = 'block';
    document.getElementById('form-title').innerText = 'Thêm bài tập mới';
    document.getElementById('task-id').value = '';
    document.getElementById('task-existing-file').value = '';
    document.getElementById('task-grade').value = '12';
    
    // Smooth scroll to form
    setTimeout(() => {
        document.getElementById('assignment-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
}

function hideForm() {
    document.getElementById('assignment-form').style.display = 'none';
    document.getElementById('questions-container').innerHTML = '';
    qCount = 0;
}

function toggleQuizSection(val) {
    const quizSec = document.getElementById('quiz-questions-section');
    const fileSec = document.getElementById('file-upload-section');
    const wordNote = document.getElementById('word-note');
    
    if (val === 'quiz') {
        quizSec.style.display = 'block';
        fileSec.style.display = 'none';
        wordNote.style.display = 'none';
        if (qCount === 0) addQuestion();
    } else if (val === 'word_quiz') {
        quizSec.style.display = 'none';
        fileSec.style.display = 'block';
        wordNote.style.display = 'block';
    } else {
        quizSec.style.display = 'none';
        fileSec.style.display = 'block';
        wordNote.style.display = 'none';
    }
}

function addQuestion(data = null) {
    const container = document.getElementById('questions-container');
    const div = document.createElement('div');
    div.className = 'question-card animate';
    div.innerHTML = `
        <button type="button" onclick="this.parentElement.remove()" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; color: #ef4444; cursor: pointer;">
            <span class="material-icons-round">delete</span>
        </button>
        <h4>Câu hỏi ${qCount + 1}</h4>
        <textarea name="questions[${qCount}][text]" placeholder="Nhập nội dung câu hỏi..." required style="width: 100%; height: 80px; margin-bottom: 1rem;">${data ? data.question : ''}</textarea>
        <div class="ans-grid">
            <input type="text" name="questions[${qCount}][a]" value="${data ? data.opt_a : ''}" placeholder="Đáp án A" required>
            <input type="text" name="questions[${qCount}][b]" value="${data ? data.opt_b : ''}" placeholder="Đáp án B" required>
            <input type="text" name="questions[${qCount}][c]" value="${data ? data.opt_c : ''}" placeholder="Đáp án C" required>
            <input type="text" name="questions[${qCount}][d]" value="${data ? data.opt_d : ''}" placeholder="Đáp án D" required>
        </div>
        <div style="margin-top: 1.5rem;">
            <label>Đáp án đúng:</label>
            <select name="questions[${qCount}][correct]" style="width: 150px; margin-left: 1rem;">
                <option value="0" ${data && data.answer == 0 ? 'selected' : ''}>A</option>
                <option value="1" ${data && data.answer == 1 ? 'selected' : ''}>B</option>
                <option value="2" ${data && data.answer == 2 ? 'selected' : ''}>C</option>
                <option value="3" ${data && data.answer == 3 ? 'selected' : ''}>D</option>
            </select>
        </div>
    `;
    container.appendChild(div);
    qCount++;
}

async function editTask(task) {
    showAddForm();
    document.getElementById('form-title').innerText = 'Chỉnh sửa bài tập';
    document.getElementById('task-id').value = task.id;
    document.getElementById('task-title').value = task.title;
    document.getElementById('task-desc').value = task.description;
    document.getElementById('task-deadline').value = task.deadline.replace(' ', 'T').slice(0, 16);
    document.getElementById('task-type').value = task.type;
    document.getElementById('task-grade').value = task.grade || 12;
    document.getElementById('task-time-limit').value = task.time_limit || 0;
    document.getElementById('task-max-attempts').value = task.max_attempts || 1;
    document.getElementById('task-allow-review').checked = (task.allow_review == 1);
    document.getElementById('task-show-answers').checked = (task.show_answers == 1);
    document.getElementById('task-existing-file').value = task.file_path;
    
    toggleQuizSection(task.type);
    
    if (task.type === 'quiz') {
        document.getElementById('questions-container').innerHTML = '<p style="text-align: center; padding: 1rem;">Đang tải câu hỏi...</p>';
        try {
            const response = await fetch(`get_assignment_questions.php?id=${task.id}`);
            const questions = await response.json();
            document.getElementById('questions-container').innerHTML = '';
            qCount = 0;
            questions.forEach(q => addQuestion(q));
        } catch (e) {
            console.error(e);
            document.getElementById('questions-container').innerHTML = '<p style="color: #ef4444;">Lỗi tải câu hỏi.</p>';
        }
    }
}
</script>

<?php include '../includes/footer.php'; ?>
