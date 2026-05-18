<?php
require_once 'includes/functions.php';

if (!is_logged_in()) redirect('login.php');

$assignment_id = $_GET['id'] ?? null;
if (!$assignment_id) redirect('assignments.php');

$user_id = $_SESSION['user']['id'];

// Fetch assignment details
$stmt = $pdo->prepare("SELECT a.*, u.username as teacher_name FROM assignments a JOIN users u ON a.teacher_id = u.id WHERE a.id = ?");
$stmt->execute([$assignment_id]);
$assignment = $stmt->fetch();

if (!$assignment) redirect('assignments.php');

$deadline = new DateTime($assignment['deadline']);
$now = new DateTime();
$is_overdue = $now > $deadline;

// Fetch all submissions for this user
$subStmt = $pdo->prepare("SELECT * FROM assignment_submissions WHERE assignment_id = ? AND student_id = ? ORDER BY submitted_at DESC");
$subStmt->execute([$assignment_id, $user_id]);
$submissions = $subStmt->fetchAll();
$attempt_count = count($submissions);
$latest_submission = $submissions[0] ?? null;

$is_review = (isset($_GET['review']) && $_GET['review'] == '1' && $assignment['allow_review']);
$is_starting = (isset($_GET['start']) && $_GET['start'] == '1');

// Handle Quiz Submission
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    if ($is_overdue) {
        $message = ["type" => "error", "text" => "Đã quá hạn nộp bài!"];
    } elseif ($attempt_count >= $assignment['max_attempts']) {
        $message = ["type" => "error", "text" => "Bạn đã hết lượt làm bài!"];
    } else {
        $student_answers = $_POST['answers'] ?? [];
        $qStmt = $pdo->prepare("SELECT id, answer FROM quizzes WHERE assignment_id = ?");
        $qStmt->execute([$assignment_id]);
        $correct_questions = $qStmt->fetchAll();
        
        $correct_count = 0;
        $total_questions = count($correct_questions);
        
        foreach ($correct_questions as $cq) {
            if (isset($student_answers[$cq['id']]) && $student_answers[$cq['id']] == $cq['answer']) {
                $correct_count++;
            }
        }
        
        $score = ($total_questions > 0) ? round(($correct_count / $total_questions) * 10, 1) : 0;
        $answers_json = json_encode($student_answers);
        $path = "quiz_score:" . $score . ":" . $correct_count . "/" . $total_questions;

        $stmt = $pdo->prepare("INSERT INTO assignment_submissions (assignment_id, student_id, file_path, answers_json, score) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$assignment_id, $user_id, $path, $answers_json, $score]);
        
        redirect("assignment_details.php?id=$assignment_id&review=1");
    }
}

// Handle Essay File Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_file'])) {
    if ($is_overdue) {
        $message = ["type" => "error", "text" => "Đã quá hạn nộp bài!"];
    } elseif ($attempt_count >= $assignment['max_attempts']) {
        $message = ["type" => "error", "text" => "Bạn đã hết lượt làm bài!"];
    } elseif (!isset($_FILES['submission_file']) || $_FILES['submission_file']['error'] !== UPLOAD_ERR_OK) {
        $message = ["type" => "error", "text" => "Lỗi khi tải file lên. Vui lòng thử lại!"];
    } else {
        $file = $_FILES['submission_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['pdf', 'docx', 'doc', 'zip', 'rar', 'png', 'jpg', 'jpeg'];
        
        if (!in_array($ext, $allowed_exts)) {
            $message = ["type" => "error", "text" => "Định dạng file không được hỗ trợ! Chỉ cho phép: .pdf, .docx, .doc, .zip, .rar, hình ảnh."];
        } else {
            // Ensure target directory exists
            $target_dir = "uploads/assignments/submissions/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $filename = "sub_" . $user_id . "_" . $assignment_id . "_" . time() . "." . $ext;
            $dest_path = $target_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $dest_path)) {
                // Score is NULL for essay/file assignment submissions until graded by the teacher
                $score = null;
                $answers_json = null;
                
                $stmt = $pdo->prepare("INSERT INTO assignment_submissions (assignment_id, student_id, file_path, answers_json, score) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$assignment_id, $user_id, $dest_path, $answers_json, $score]);
                
                // Redirect to refresh page and show new submission in history
                redirect("assignment_details.php?id=$assignment_id");
            } else {
                $message = ["type" => "error", "text" => "Không thể lưu file bài làm!"];
            }
        }
    }
}

// Bây giờ mới gọi giao diện
include 'includes/header.php';

// Fetch questions
$questions = [];
if (in_array($assignment['type'], ['quiz', 'word_quiz'])) {
    $qStmt = $pdo->prepare("SELECT * FROM quizzes WHERE assignment_id = ? ORDER BY id ASC");
    $qStmt->execute([$assignment_id]);
    $questions = $qStmt->fetchAll();
}

$review_sub_id = $_GET['sub_id'] ?? ($latest_submission['id'] ?? null);
$review_answers = [];
if ($is_review && $review_sub_id) {
    $rSubStmt = $pdo->prepare("SELECT answers_json FROM assignment_submissions WHERE id = ?");
    $rSubStmt->execute([$review_sub_id]);
    $answers_raw = $rSubStmt->fetchColumn();
    if ($answers_raw) $review_answers = json_decode($answers_raw, true);
}
?>

<main class="container">
    <div style="padding: 2rem 0;">
        <a href="assignments.php" style="color: var(--accent); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem;">
            <span class="material-icons-round">arrow_back</span> Danh sách bài tập
        </a>

        <div class="grid" style="grid-template-columns: 1fr 350px; align-items: start; gap: 2rem;">
            <div class="content-wrapper">
                <h1 style="margin-bottom: 1rem; font-size: 2.5rem;"><?php echo htmlspecialchars($assignment['title']); ?></h1>
                
                <?php if (!empty($message)): ?>
                    <div style="padding: 1rem; border-radius: 8px; background: <?php echo $message['type'] === 'error' ? 'rgba(239, 68, 68, 0.1)' : 'rgba(16, 185, 129, 0.1)'; ?>; border: 1px solid <?php echo $message['type'] === 'error' ? '#ef4444' : '#10b981'; ?>; color: <?php echo $message['type'] === 'error' ? '#f87171' : '#34d399'; ?>; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                        <span class="material-icons-round"><?php echo $message['type'] === 'error' ? 'error_outline' : 'check_circle_outline'; ?></span>
                        <span><?php echo htmlspecialchars($message['text']); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!$is_starting && !$is_review): ?>
                    <div style="color: var(--text-muted); margin-bottom: 2rem; line-height: 1.6; font-size: 1.1rem;">
                        <?php echo nl2br(htmlspecialchars($assignment['description'])); ?>
                    </div>
                    
                    <div class="card" style="padding: 2rem; border-left: 4px solid var(--primary);">
                        <h3 style="margin-bottom: 1rem;">Thông tin bài tập</h3>
                        <ul style="list-style: none; padding: 0;">
                            <li style="margin-bottom: 10px; display: flex; gap: 10px;">
                                <span class="material-icons-round" style="color: var(--accent);">timer</span> 
                                Thời gian làm bài: <strong><?php echo $assignment['time_limit'] > 0 ? $assignment['time_limit'] . " phút" : "Không giới hạn"; ?></strong>
                            </li>
                            <li style="margin-bottom: 10px; display: flex; gap: 10px;">
                                <span class="material-icons-round" style="color: var(--accent);">replay</span> 
                                Số lượt làm tối đa: <strong><?php echo $assignment['max_attempts']; ?></strong>
                            </li>
                        </ul>

                        <?php if ($assignment['type'] === 'file' && !empty($assignment['file_path'])): ?>
                            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--glass-border);">
                                <h4 style="margin-bottom: 0.8rem; display: flex; align-items: center; gap: 8px;">
                                    <span class="material-icons-round" style="color: var(--accent);">download_for_offline</span> Đề bài đính kèm
                                </h4>
                                <?php 
                                    $a_ext = pathinfo($assignment['file_path'], PATHINFO_EXTENSION);
                                    $a_name = $assignment['title'] . '.' . $a_ext;
                                ?>
                                <div style="display: flex; gap: 10px;">
                                    <a href="<?php echo htmlspecialchars($assignment['file_path']); ?>" target="_blank" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 8px; padding: 0.8rem 1.2rem; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.95rem;">
                                        <span class="material-icons-round">visibility</span> Xem đề bài
                                    </a>
                                    <a href="download.php?file=<?php echo urlencode($assignment['file_path']); ?>&name=<?php echo urlencode($a_name); ?>" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; padding: 0.8rem 1.2rem; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.95rem;">
                                        <span class="material-icons-round">file_download</span> Tải đề bài về máy
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (in_array($assignment['type'], ['quiz', 'word_quiz'])): ?>
                            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                                <?php if (!$is_overdue && $attempt_count < $assignment['max_attempts']): ?>
                                    <a href="?id=<?php echo $assignment_id; ?>&start=1" class="btn btn-primary" style="flex: 1; padding: 1rem;">
                                        <?php echo $attempt_count > 0 ? 'Làm lại bài tập' : 'Bắt đầu làm bài'; ?>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($attempt_count > 0 && $assignment['allow_review']): ?>
                                    <a href="?id=<?php echo $assignment_id; ?>&review=1" class="btn btn-outline" style="flex: 1; padding: 1rem;">
                                        Xem lại kết quả
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($assignment['type'] === 'file'): ?>
                        <?php if ($attempt_count >= $assignment['max_attempts']): ?>
                            <div class="card" style="margin-top: 2rem; padding: 2rem; border-left: 4px solid #ef4444; background: rgba(239, 68, 68, 0.03);">
                                <h3 style="margin-bottom: 0.5rem; color: #ef4444; display: flex; align-items: center; gap: 8px;">
                                    <span class="material-icons-round">error_outline</span> Đã hết lượt nộp bài
                                </h3>
                                <p style="color: var(--text-muted); margin: 0;">Bạn đã nộp bài đủ số lần quy định tối đa cho bài tập này (<?php echo $assignment['max_attempts']; ?> lần).</p>
                            </div>
                        <?php elseif ($is_overdue): ?>
                            <div class="card" style="margin-top: 2rem; padding: 2rem; border-left: 4px solid #ef4444; background: rgba(239, 68, 68, 0.03);">
                                <h3 style="margin-bottom: 0.5rem; color: #ef4444; display: flex; align-items: center; gap: 8px;">
                                    <span class="material-icons-round">lock</span> Đã quá hạn nộp bài
                                </h3>
                                <p style="color: var(--text-muted); margin: 0;">Hạn chót nộp bài đã trôi qua. Bạn không thể nộp hoặc chỉnh sửa bài làm nữa.</p>
                            </div>
                        <?php else: ?>
                            <div class="card" style="margin-top: 2rem; padding: 2rem; border-left: 4px solid var(--accent); background: rgba(245, 158, 11, 0.01);">
                                <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px;">
                                    <span class="material-icons-round">upload_file</span> Nộp bài tự luận
                                </h3>
                                
                                <form method="POST" enctype="multipart/form-data" id="essay-upload-form" style="display: grid; gap: 1.5rem;">
                                    <input type="hidden" name="submit_file" value="1">
                                    
                                    <div class="file-drop-area" id="drop-area" style="border: 2px dashed var(--glass-border); border-radius: 12px; padding: 2.5rem; text-align: center; cursor: pointer; transition: all 0.3s; background: rgba(255,255,255,0.01);" onmouseover="this.style.borderColor='var(--accent)'; this.style.background='rgba(255,255,255,0.02)';" onmouseout="this.style.borderColor='var(--glass-border)'; this.style.background='rgba(255,255,255,0.01)';">
                                        <span class="material-icons-round" style="font-size: 3rem; color: var(--accent); margin-bottom: 1rem;">cloud_upload</span>
                                        <p style="margin: 0 0 0.5rem 0; font-weight: 600; font-size: 1.1rem;">Kéo thả hoặc Click để chọn file bài làm</p>
                                        <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">Hỗ trợ: PDF, DOCX, ZIP, RAR, JPG, PNG (Tối đa 20MB)</p>
                                        <input type="file" name="submission_file" id="submission-file" required style="display: none;" onchange="handleFileSelected(this)">
                                    </div>
                                    
                                    <div id="file-info" style="display: none; align-items: center; gap: 10px; padding: 1rem; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 8px;">
                                        <span class="material-icons-round" style="color: var(--accent);">insert_drive_file</span>
                                        <div style="flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <strong id="file-name" style="font-size: 0.95rem; color: var(--text);">filename.pdf</strong>
                                            <span id="file-size" style="display: block; font-size: 0.8rem; color: var(--text-muted);">1.2 MB</span>
                                        </div>
                                        <button type="button" onclick="clearFile()" style="background: none; border: none; color: #ef4444; cursor: pointer; display: flex; align-items: center; padding: 4px;">
                                            <span class="material-icons-round">delete</span>
                                        </button>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary" style="padding: 1rem; font-size: 1.1rem; font-weight: 600; width: 100%;">
                                        Xác nhận nộp bài
                                    </button>
                                </form>
                            </div>
                            
                            <script>
                            const dropArea = document.getElementById('drop-area');
                            const fileInput = document.getElementById('submission-file');
                            const fileInfo = document.getElementById('file-info');
                            const fileName = document.getElementById('file-name');
                            const fileSize = document.getElementById('file-size');
                            
                            dropArea.addEventListener('click', () => fileInput.click());
                            
                            dropArea.addEventListener('dragover', (e) => {
                                e.preventDefault();
                                dropArea.style.borderColor = 'var(--accent)';
                                dropArea.style.background = 'rgba(255,255,255,0.03)';
                            });
                            
                            dropArea.addEventListener('dragleave', () => {
                                dropArea.style.borderColor = 'var(--glass-border)';
                                dropArea.style.background = 'rgba(255,255,255,0.01)';
                            });
                            
                            dropArea.addEventListener('drop', (e) => {
                                e.preventDefault();
                                dropArea.style.borderColor = 'var(--glass-border)';
                                dropArea.style.background = 'rgba(255,255,255,0.01)';
                                
                                if (e.dataTransfer.files.length) {
                                    fileInput.files = e.dataTransfer.files;
                                    handleFileSelected(fileInput);
                                }
                            });
                            
                            function handleFileSelected(input) {
                                if (input.files.length) {
                                    const file = input.files[0];
                                    fileName.innerText = file.name;
                                    
                                    // Format size
                                    const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                                    fileSize.innerText = sizeMB + ' MB';
                                    
                                    fileInfo.style.display = 'flex';
                                }
                            }
                            
                            function clearFile() {
                                fileInput.value = '';
                                fileInfo.style.display = 'none';
                            }
                            </script>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($is_starting || $is_review): ?>
                    <form method="POST" id="quiz-form" onsubmit="return handleFormSubmit(event)">
                        <input type="hidden" name="submit_quiz" value="1">
                        <div style="margin-top: 1rem;">
                            <?php if ($is_review && empty($review_answers)): ?>
                                <div style="padding: 1rem; background: rgba(251, 191, 36, 0.1); border: 1px solid #fbbf24; border-radius: 10px; color: #fbbf24; margin-bottom: 1.5rem; font-size: 0.9rem; display: flex; align-items: center; gap: 10px;">
                                    <span class="material-icons-round">info</span>
                                    Bài làm này không lưu chi tiết đáp án đã chọn (Dữ liệu cũ). Hệ thống hiển thị đáp án đúng để bạn tham khảo.
                                </div>
                            <?php endif; ?>
                            
                            <?php 
                            $displayCount = 1;
                            foreach ($questions as $index => $q): 
                                if (empty(trim($q['question'])) || empty(trim($q['opt_a']))) continue;

                                $student_choice = isset($review_answers[$q['id']]) ? (string)$review_answers[$q['id']] : null;
                                $is_correct = ($is_review && $student_choice !== null && $student_choice == (string)$q['answer']);
                            ?>
                                    <div class="card animate question-item" data-id="<?php echo $q['id']; ?>" style="margin-bottom: 1.5rem; padding: 1.5rem; border-left: 4px solid <?php 
                                        if ($is_review) {
                                            echo ($student_choice === null) ? '#94a3b8' : ($is_correct ? '#10b981' : '#ef4444');
                                        } else {
                                            echo 'transparent';
                                        }
                                    ?>;">
                                        <h4 style="margin-bottom: 1.5rem;">
                                            Câu <?php echo $displayCount++; ?>: <?php echo htmlspecialchars($q['question']); ?>
                                            <?php if ($is_review): ?>
                                                <?php if ($student_choice === null): ?>
                                                    <span style="color: #94a3b8; font-size: 0.9rem; margin-left: 10px;">(Chưa trả lời)</span>
                                                <?php elseif ($is_correct): ?>
                                                    <span style="color: #10b981; font-size: 0.9rem; margin-left: 10px;">(Đúng)</span>
                                                <?php else: ?>
                                                    <span style="color: #ef4444; font-size: 0.9rem; margin-left: 10px;">(Sai<?php echo $assignment['show_answers'] ? ' - Đáp án đúng: ' . chr(65 + $q['answer']) : ''; ?>)</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </h4>
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                            <?php 
                                            $options = [$q['opt_a'], $q['opt_b'], $q['opt_c'], $q['opt_d']];
                                            $labels = ['A', 'B', 'C', 'D'];
                                            foreach ($options as $oi => $opt): 
                                                $style = "";
                                                if ($is_review) {
                                                    if ($assignment['show_answers'] && $oi == $q['answer']) {
                                                        $style = "background: rgba(16, 185, 129, 0.1); border-color: #10b981; border-width: 2px;";
                                                    }
                                                    if ($student_choice !== null && $oi == (int)$student_choice && $oi != $q['answer']) {
                                                        $style = "background: rgba(239, 68, 68, 0.1); border-color: #ef4444; border-width: 2px;";
                                                    }
                                                    if ($student_choice !== null && $oi == (int)$student_choice && $oi == $q['answer']) {
                                                        $style = "background: rgba(16, 185, 129, 0.1); border-color: #10b981; border-width: 2px;";
                                                    }
                                                }
                                            ?>
                                                <label style="display: flex; align-items: center; gap: 10px; padding: 1.2rem; border: 2px solid <?php 
                                                    if ($is_review && $student_choice !== null && $oi == (int)$student_choice) {
                                                        echo $is_correct ? '#10b981' : '#ef4444';
                                                    } else {
                                                        echo 'var(--glass-border)';
                                                    }
                                                ?>; border-radius: 10px; cursor: <?php echo $is_review ? 'default' : 'pointer'; ?>; transition: all 0.2s; <?php echo $style; ?>">
                                                    <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="<?php echo $oi; ?>" 
                                                        <?php echo ($is_review && $student_choice !== null && (int)$student_choice === $oi) ? 'checked' : ''; ?>
                                                        <?php echo $is_review ? 'disabled' : ''; ?>>
                                                    <div style="flex: 1;">
                                                        <span><strong><?php echo $labels[$oi]; ?>.</strong> <?php echo htmlspecialchars($opt); ?></span>
                                                    </div>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                            <?php if ($is_starting): ?>
                                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.2rem; font-size: 1.1rem; margin-top: 1rem;">
                                    Hoàn thành & Nộp bài
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

<script>
async function handleFormSubmit(e) {
    e.preventDefault();

    const form = document.getElementById('quiz-form');
    const formData = new FormData(form);
    
    // Kiểm tra xem đã làm hết câu hỏi chưa bằng FormData (chính xác 100%)
    const questions = document.querySelectorAll('.question-item');
    let unansweredCount = 0;
    
    questions.forEach(q => {
        const qId = q.getAttribute('data-id');
        if (!formData.has(`answers[${qId}]`)) {
            unansweredCount++;
        }
    });

    if (unansweredCount > 0) {
        await CustomModal.alert(`Bạn còn ${unansweredCount} câu chưa trả lời. Hãy hoàn thành tất cả trước khi nộp bài!`, 'Chưa hoàn thành');
        return false;
    }
    
    const confirmed = await CustomModal.confirm('Bạn có chắc chắn muốn kết thúc và nộp bài làm này không?', 'Xác nhận nộp bài');
    if (confirmed) {
        // Tạm thời vô hiệu hóa onsubmit để tránh lặp vô tận, sau đó submit form
        form.onsubmit = null;
        form.submit();
    }
    return false;
}
</script>

            <div class="content-wrapper" style="position: sticky; top: 100px;">
                <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                    <span class="material-icons-round" style="color: var(--primary);">timer</span>
                    <?php echo ($is_starting && $assignment['time_limit'] > 0) ? 'Thời gian làm bài' : 'Thông tin thời gian'; ?>
                </h3>
                
                <div style="margin-bottom: 1.5rem; padding: 1.2rem; background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px solid var(--glass-border);">
                    <?php if ($is_starting && $assignment['time_limit'] > 0): ?>
                        <div id="quiz-timer" style="text-align: center;">
                            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 10px;">Thời gian còn lại:</p>
                            <div id="countdown" style="font-size: 2.5rem; font-weight: 800; color: var(--accent); font-family: monospace;">
                                <?php echo sprintf("%02d:00", $assignment['time_limit']); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">Hạn chót:</p>
                        <p style="font-weight: 700; font-size: 1.1rem; color: var(--accent);">
                            <?php echo $deadline->format('d/m/Y H:i'); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if ($attempt_count > 0): ?>
                    <div style="margin-top: 2rem;">
                        <h4 style="margin-bottom: 1rem;">Lượt làm bài</h4>
                        <?php foreach ($submissions as $idx => $s): 
                            $att_num = $attempt_count - $idx;
                            $is_current_review = ($is_review && $review_sub_id == $s['id']);
                        ?>
                            <div style="padding: 1rem; border-radius: 10px; background: <?php echo $is_current_review ? 'rgba(79, 70, 229, 0.1)' : 'rgba(255,255,255,0.02)'; ?>; border: 1px solid <?php echo $is_current_review ? 'var(--primary)' : 'var(--glass-border)'; ?>; margin-bottom: 0.8rem; font-size: 0.9rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                    <span style="font-weight: 600;">Lần <?php echo $att_num; ?>:</span>
                                    <strong style="color: var(--accent); font-size: 1.1rem;">
                                        <?php echo $s['score'] !== null ? number_format($s['score'], 1) . ' điểm' : 'Chờ chấm'; ?>
                                    </strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 0.75rem; color: var(--text-muted);"><?php echo date('d/m/Y H:i', strtotime($s['submitted_at'])); ?></span>
                                    <?php if ($assignment['type'] === 'file'): ?>
                                        <a href="<?php echo htmlspecialchars($s['file_path']); ?>" download
                                           style="color: var(--primary); text-decoration: none; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                            <span class="material-icons-round" style="font-size: 1rem;">file_download</span> Tải bài làm
                                        </a>
                                    <?php elseif ($assignment['allow_review']): ?>
                                        <a href="?id=<?php echo $assignment_id; ?>&review=1&sub_id=<?php echo $s['id']; ?>" 
                                           style="color: var(--primary); text-decoration: none; font-size: 0.8rem; font-weight: 600;">
                                            Xem kết quả
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php if ($is_starting && $assignment['time_limit'] > 0): ?>
<script>
let timeLimit = <?php echo $assignment['time_limit'] * 60; ?>;
const timerDisplay = document.getElementById('countdown');
const quizForm = document.getElementById('quiz-form');

const timer = setInterval(() => {
    timeLimit--;
    let mins = Math.floor(timeLimit / 60);
    let secs = timeLimit % 60;
    
    timerDisplay.innerHTML = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    if (timeLimit <= 60) timerDisplay.style.color = '#ef4444';
    if (timeLimit <= 0) {
        clearInterval(timer);
        
        // Khóa tất cả các lựa chọn để học sinh không thể chọn thêm (giao diện)
        document.querySelectorAll('input[type="radio"]').forEach(el => el.disabled = true);
        
        // Hiển thị thông báo (không cần await)
        CustomModal.alert('Đã hết thời gian làm bài! Hệ thống sẽ tự động nộp bài của bạn ngay bây giờ...', 'Hết giờ!');
        
        // Tự động submit sau 2 giây
        setTimeout(() => {
            // CỰC KỲ QUAN TRỌNG: Mở khóa lại các input trước khi submit
            // Nếu input bị disabled, trình duyệt sẽ KHÔNG GỬI đáp án lên server!
            document.querySelectorAll('input[type="radio"]').forEach(el => el.disabled = false);
            
            quizForm.onsubmit = null; // Vô hiệu hóa validate
            quizForm.submit();
        }, 2000);
    }
}, 1000);
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
