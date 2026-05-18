<?php
require_once '../includes/functions.php';
require_once '../includes/WordQuizImporter.php';
if (file_exists('../vendor/autoload.php')) {
    require_once '../vendor/autoload.php';
}
if (!is_teacher())
    redirect('../login.php');

$success = '';
$error = '';

// Handle Delete Question
if (isset($_GET['delete_quiz'])) {
    $id = $_GET['delete_quiz'];
    $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
    $stmt->execute([$id]);
    $success = "Xóa câu hỏi thành công!";
}

// Handle Delete Quiz Set
if (isset($_GET['delete_set'])) {
    $id = $_GET['delete_set'];
    $stmt = $pdo->prepare("DELETE FROM quiz_sets WHERE id = ?");
    $stmt->execute([$id]);
    $success = "Xóa bộ đề thành công!";
}

// Handle Add/Update Quiz Set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_set']) || isset($_POST['update_set']))) {
    $time_limit = ($_POST['time_limit'] === '') ? null : (int)$_POST['time_limit'];
    $max_attempts = ($_POST['max_attempts'] === '') ? null : (int)$_POST['max_attempts'];
    $available_until = ($_POST['available_until'] === '') ? null : $_POST['available_until'];

    if (isset($_POST['update_set'])) {
        $stmt = $pdo->prepare("UPDATE quiz_sets SET topic_id = ?, title = ?, description = ?, time_limit = ?, available_until = ?, max_attempts = ? WHERE id = ?");
        $stmt->execute([
            $_POST['topic_id'],
            $_POST['set_title'],
            $_POST['set_desc'],
            $time_limit,
            $available_until,
            $max_attempts,
            $_POST['set_id']
        ]);
        $success = "Cập nhật bộ đề thành công!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO quiz_sets (topic_id, title, description, time_limit, available_until, max_attempts) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['topic_id'],
            $_POST['set_title'],
            $_POST['set_desc'],
            $time_limit,
            $available_until,
            $max_attempts
        ]);
        $success = "Thêm bộ đề mới thành công!";
    }
}

// Fetch data for editing
$edit_set = null;
if (isset($_GET['edit_set'])) {
    $stmt = $pdo->prepare("SELECT * FROM quiz_sets WHERE id = ?");
    $stmt->execute([$_GET['edit_set']]);
    $edit_set = $stmt->fetch();
}

// Handle Add Question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_quiz'])) {
    $stmt = $pdo->prepare("INSERT INTO quizzes (question, opt_a, opt_b, opt_c, opt_d, answer, quiz_set_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['question'],
        $_POST['opt0'],
        $_POST['opt1'],
        $_POST['opt2'],
        $_POST['opt3'],
        (int) $_POST['answer'],
        $_POST['quiz_set_id']
    ]);
    $success = "Thêm câu hỏi mới thành công!";
}
// Handle Import Question from Word File
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_word_quiz'])) {
    $quiz_set_id = (int)$_POST['quiz_set_id'];
    
    try {
        if (!isset($_FILES['quiz_word_file']) || $_FILES['quiz_word_file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Lỗi khi tải file lên.");
        }
        
        $file_name = $_FILES['quiz_word_file']['name'];
        $tmp_name = $_FILES['quiz_word_file']['tmp_name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if ($ext !== 'docx') {
            throw new Exception("Vui lòng chọn file định dạng .docx.");
        }
        
        // Define directory to save file
        $uploads_dir = '../uploads';
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0777, true);
        }
        
        $dest_path = $uploads_dir . '/' . time() . '_' . $file_name;
        if (move_uploaded_file($tmp_name, $dest_path)) {
            $importer = new WordQuizImporter($pdo);
            $count = $importer->import_to_quiz_set($dest_path, $quiz_set_id);
            $success = "Thành công! Đã nhập $count câu hỏi từ file Word vào bộ đề.";
        } else {
            throw new Exception("Không thể lưu file tải lên.");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$quizzes = $pdo->query("SELECT q.*, s.title as set_title FROM quizzes q LEFT JOIN quiz_sets s ON q.quiz_set_id = s.id ORDER BY q.id DESC")->fetchAll();
$quiz_sets = $pdo->query("SELECT s.*, t.title as topic_title FROM quiz_sets s JOIN topics t ON s.topic_id = t.id ORDER BY s.id DESC")->fetchAll();
$topics = $pdo->query("SELECT id, title FROM topics")->fetchAll();

$quizzes_by_set = [];
foreach ($quizzes as $q) {
    $set_id = $q['quiz_set_id'] ?? 0;
    $set_title = $q['set_title'] ?? 'Chưa phân loại';
    if (!isset($quizzes_by_set[$set_id])) {
        $quizzes_by_set[$set_id] = [
            'title' => $set_title,
            'questions' => []
        ];
    }
    $quizzes_by_set[$set_id]['questions'][] = $q;
}

include '../includes/header.php';
?>

<main class="container" style="padding: 4rem 0;">
    <a href="dashboard.php"
        style="color: var(--accent); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 2rem;">
        <span class="material-icons-round">arrow_back</span> Dashboard
    </a>

    <h1>Quản lý Luyện tập</h1>

    <?php if ($success): ?>
        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #10b981; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- Manage Quiz Sets -->
    <section style="margin-bottom: 4rem;">
        <h2 style="margin-bottom: 2rem; color: var(--accent);">1. Quản lý Bộ đề (Quiz Sets)</h2>
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
            <div class="content-wrapper">
                <h3><?php echo $edit_set ? 'Sửa Bộ Đề' : 'Thêm Bộ Đề Mới'; ?></h3>
                <form method="POST" action="manage_quiz.php">
                    <?php if ($edit_set): ?>
                        <input type="hidden" name="set_id" value="<?php echo $edit_set['id']; ?>">
                    <?php endif; ?>
                    
                    <label style="display: block; margin: 1rem 0 0.5rem; color: var(--text-muted);">Chủ đề</label>
                    <select name="topic_id" class="btn btn-outline" style="width: 100%;">
                        <?php foreach($topics as $t): ?>
                            <option value="<?php echo $t['id']; ?>" <?php echo ($edit_set && $edit_set['topic_id'] == $t['id']) ? 'selected' : ''; ?>>
                                <?php echo $t['title']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label style="display: block; margin: 1rem 0 0.5rem; color: var(--text-muted);">Tiêu đề bộ đề</label>
                    <input type="text" name="set_title" value="<?php echo $edit_set['title'] ?? ''; ?>" required class="btn btn-outline" style="width: 100%;">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label style="display: block; margin: 1rem 0 0.5rem; color: var(--text-muted);">Thời gian (phút)</label>
                            <input type="number" name="time_limit" value="<?php echo isset($edit_set['time_limit']) ? $edit_set['time_limit'] : ''; ?>" class="btn btn-outline" style="width: 100%;" placeholder="Không giới hạn">
                        </div>
                        <div>
                            <label style="display: block; margin: 1rem 0 0.5rem; color: var(--text-muted);">Số lần làm tối đa</label>
                            <input type="number" name="max_attempts" value="<?php echo isset($edit_set['max_attempts']) ? $edit_set['max_attempts'] : ''; ?>" class="btn btn-outline" style="width: 100%;" placeholder="Không giới hạn">
                        </div>
                    </div>

                    <label style="display: block; margin: 1rem 0 0.5rem; color: var(--text-muted);">Hết hạn vào lúc</label>
                    <input type="datetime-local" name="available_until" value="<?php echo ($edit_set && !empty($edit_set['available_until'])) ? date('Y-m-d\TH:i', strtotime($edit_set['available_until'])) : ''; ?>" class="btn btn-outline" style="width: 100%;" placeholder="Không giới hạn">

                    <label style="display: block; margin: 1rem 0 0.5rem; color: var(--text-muted);">Mô tả</label>
                    <textarea name="set_desc" class="btn btn-outline" style="width: 100%; text-align: left;"><?php echo $edit_set['description'] ?? ''; ?></textarea>
                    
                    <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                        <button type="submit" name="<?php echo $edit_set ? 'update_set' : 'add_set'; ?>" class="btn btn-primary" style="flex: 1;">
                            <?php echo $edit_set ? 'Cập nhật' : 'Lưu Bộ Đề'; ?>
                        </button>
                        <?php if ($edit_set): ?>
                            <a href="manage_quiz.php" class="btn btn-outline">Hủy</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            <div>
                <h3>Danh sách Bộ Đề</h3>
                <div style="display: grid; gap: 1rem;">
                    <?php foreach($quiz_sets as $s): ?>
                        <div class="content-wrapper" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-color: <?php echo ($edit_set && $edit_set['id'] == $s['id']) ? 'var(--accent)' : 'var(--glass-border)'; ?>;">
                            <div style="font-size: 0.9rem;">
                                <strong style="color: var(--accent);"><?php echo $s['topic_title']; ?></strong>: 
                                <?php echo $s['title']; ?>
                                <div style="color: var(--text-muted); font-size: 0.8rem; margin-top: 0.3rem;">
                                    ⏰ <?php echo ($s['time_limit'] > 0) ? $s['time_limit'] . 'p' : 'Ko giới hạn'; ?> | 
                                    🔄 Tối đa <?php echo ($s['max_attempts'] > 0) ? $s['max_attempts'] . ' lần' : 'Không giới hạn'; ?> | 
                                    📅 Hết hạn: <?php echo !empty($s['available_until']) ? date('d/m H:i', strtotime($s['available_until'])) : 'Không giới hạn'; ?>
                                </div>
                            </div>
                            <div style="display: flex; gap: 1rem;">
                                <a href="?edit_set=<?php echo $s['id']; ?>" style="color: var(--primary);"><span class="material-icons-round">edit</span></a>
                                <a href="?delete_set=<?php echo $s['id']; ?>" onclick="return confirm('Xóa bộ đề sẽ xóa tất cả câu hỏi bên trong?')" style="color: #ef4444;"><span class="material-icons-round">delete</span></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <hr style="border: 0; border-top: 1px solid var(--glass-border); margin-bottom: 4rem;">

    <!-- Manage Questions -->
    <section>
        <h2 style="margin-bottom: 2rem; color: var(--accent);">2. Quản lý Câu hỏi</h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
            <section class="content-wrapper" style="margin: 0; padding: 2rem;">
                <div style="display: flex; border-bottom: 1px solid var(--glass-border); margin-bottom: 1.5rem;">
                    <button type="button" onclick="switchQuestionTab('manual')" id="tab-btn-manual" style="flex: 1; padding: 0.8rem; background: none; border: none; border-bottom: 2px solid var(--accent); color: var(--accent); font-weight: bold; cursor: pointer; font-size: 1rem; transition: all 0.2s;">
                        📝 Thêm Thủ Công
                    </button>
                    <button type="button" onclick="switchQuestionTab('word')" id="tab-btn-word" style="flex: 1; padding: 0.8rem; background: none; border: none; border-bottom: 2px solid transparent; color: var(--text-muted); font-weight: normal; cursor: pointer; font-size: 1rem; transition: all 0.2s;">
                        📄 Nhập Từ Word
                    </button>
                </div>

                <!-- Manual Add Form -->
                <div id="form-container-manual" style="display: block;">
                    <form method="POST">
                        <label style="display: block; margin: 0 0 0.5rem; color: var(--text-muted);">Thuộc Bộ Đề</label>
                        <select name="quiz_set_id" class="btn btn-outline" style="width: 100%; margin-bottom: 1rem; text-align: left;">
                            <?php foreach($quiz_sets as $s): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['title']); ?> (<?php echo htmlspecialchars($s['topic_title']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        
                        <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">Câu hỏi</label>
                        <textarea name="question" required class="btn btn-outline" style="width: 100%; text-align: left; min-height: 80px; margin-bottom: 1rem;"></textarea>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <input type="text" name="opt0" placeholder="Đáp án A" required class="btn btn-outline">
                            <input type="text" name="opt1" placeholder="Đáp án B" required class="btn btn-outline">
                            <input type="text" name="opt2" placeholder="Đáp án C" required class="btn btn-outline">
                            <input type="text" name="opt3" placeholder="Đáp án D" required class="btn btn-outline">
                        </div>

                        <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">Đáp án đúng</label>
                        <select name="answer" class="btn btn-outline" style="width: 100%; margin-bottom: 1.5rem; text-align: left;">
                            <option value="0">A</option>
                            <option value="1">B</option>
                            <option value="2">C</option>
                            <option value="3">D</option>
                        </select>
                        <button type="submit" name="add_quiz" class="btn btn-primary" style="width: 100%;">Lưu Câu Hỏi</button>
                    </form>
                </div>

                <!-- Word Importer Form -->
                <div id="form-container-word" style="display: none;">
                    <form method="POST" enctype="multipart/form-data">
                        <label style="display: block; margin: 0 0 0.5rem; color: var(--text-muted);">Chọn Bộ Đề Cần Nhập</label>
                        <select name="quiz_set_id" class="btn btn-outline" style="width: 100%; margin-bottom: 1rem; text-align: left;">
                            <?php foreach($quiz_sets as $s): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['title']); ?> (<?php echo htmlspecialchars($s['topic_title']); ?>)</option>
                            <?php endforeach; ?>
                        </select>

                        <div style="background: rgba(255, 255, 255, 0.02); border: 1px dashed var(--glass-border); padding: 1.5rem; border-radius: 8px; text-align: center; margin-bottom: 1.5rem;">
                            <span class="material-icons-round" style="font-size: 3rem; color: var(--accent); margin-bottom: 0.5rem;">cloud_upload</span>
                            <h4 style="margin: 0 0 0.5rem; color: var(--text);">Chọn file .docx</h4>
                            <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0 0 1rem 0;">Hỗ trợ định dạng câu hỏi trắc nghiệm Word tiêu chuẩn</p>
                            <input type="file" name="quiz_word_file" required accept=".docx" class="btn btn-outline" style="max-width: 100%;">
                        </div>

                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.5; background: rgba(0,0,0,0.1); padding: 1rem; border-radius: 6px; text-align: left;">
                            <strong>💡 Định dạng file Word mẫu:</strong>
                            <p style="margin: 0.3rem 0 0 0;">Câu 1: Câu hỏi của bạn...</p>
                            <p style="margin: 0;">A. Lựa chọn A</p>
                            <p style="margin: 0;">B. Lựa chọn B</p>
                            <p style="margin: 0;">C. Lựa chọn C</p>
                            <p style="margin: 0;">D. Lựa chọn D</p>
                            <p style="margin: 0; color: var(--accent); font-weight: bold;">Đáp án: A</p>
                        </div>

                        <button type="submit" name="import_word_quiz" class="btn btn-primary" style="width: 100%;">Tải lên & Nhập Câu Hỏi</button>
                    </form>
                </div>
            </section>

            <section>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                    <h3 style="margin: 0;">Danh Sách Câu Hỏi</h3>
                    <input type="text" id="question-search" placeholder="🔍 Tìm kiếm câu hỏi..." class="btn btn-outline" style="width: 250px; padding: 0.5rem 1rem; font-size: 0.9rem; text-align: left;">
                </div>
                
                <div style="display: grid; gap: 1rem;">
                    <?php if (empty($quizzes_by_set)): ?>
                        <div class="content-wrapper" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                            Chưa có câu hỏi nào được thêm.
                        </div>
                    <?php else: ?>
                        <?php foreach ($quizzes_by_set as $set_id => $group): ?>
                            <div class="content-wrapper q-set-accordion" style="padding: 0; overflow: hidden; border-color: var(--glass-border);">
                                <!-- Accordion Header -->
                                <div class="accordion-header" onclick="toggleAccordion(this)" style="display: flex; justify-content: space-between; align-items: center; padding: 1.2rem; cursor: pointer; background: rgba(255,255,255,0.02); transition: background 0.2s;">
                                    <div style="display: flex; align-items: center; gap: 0.8rem;">
                                        <span class="material-icons-round accordion-icon" style="transition: transform 0.2s; color: var(--text-muted);">expand_more</span>
                                        <div>
                                            <strong style="color: var(--accent); font-size: 1rem;"><?php echo htmlspecialchars($group['title']); ?></strong>
                                            <span style="font-size: 0.8rem; color: var(--text-muted); margin-left: 0.5rem;">(<?php echo count($group['questions']); ?> câu hỏi)</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Accordion Body -->
                                <div class="accordion-body" style="display: none; padding: 1.2rem; border-top: 1px solid var(--glass-border); background: rgba(0,0,0,0.15);">
                                    <div style="display: grid; gap: 1rem;">
                                        <?php foreach ($group['questions'] as $idx => $q): ?>
                                            <div class="q-row-item" data-text="<?php echo htmlspecialchars(strtolower($q['question'] . ' ' . $q['opt_a'] . ' ' . $q['opt_b'] . ' ' . $q['opt_c'] . ' ' . $q['opt_d'])); ?>" style="padding: 1rem; background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; position: relative;">
                                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 2rem; margin-bottom: 0.8rem;">
                                                    <div style="font-weight: bold; color: var(--primary); font-size: 0.9rem;">
                                                        Câu <?php echo $idx + 1; ?>:
                                                    </div>
                                                    <a href="?delete_quiz=<?php echo $q['id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa câu hỏi này?')" style="color: #ef4444; transition: opacity 0.2s;" class="hover-opacity">
                                                        <span class="material-icons-round" style="font-size: 1.2rem;">delete</span>
                                                    </a>
                                                </div>
                                                
                                                <p style="margin: 0 0 1rem 0; font-size: 0.95rem; line-height: 1.5; color: var(--text);"><?php echo htmlspecialchars($q['question']); ?></p>
                                                
                                                <!-- Options layout -->
                                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.5rem; font-size: 0.85rem;">
                                                    <div style="padding: 0.4rem 0.8rem; border-radius: 6px; <?php echo (int)$q['answer'] === 0 ? 'background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #10b981; font-weight: 500;' : 'background: rgba(255,255,255,0.02); border: 1px solid transparent; color: var(--text-muted);'; ?>">
                                                        <strong>A.</strong> <?php echo htmlspecialchars($q['opt_a']); ?>
                                                    </div>
                                                    <div style="padding: 0.4rem 0.8rem; border-radius: 6px; <?php echo (int)$q['answer'] === 1 ? 'background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #10b981; font-weight: 500;' : 'background: rgba(255,255,255,0.02); border: 1px solid transparent; color: var(--text-muted);'; ?>">
                                                        <strong>B.</strong> <?php echo htmlspecialchars($q['opt_b']); ?>
                                                    </div>
                                                    <div style="padding: 0.4rem 0.8rem; border-radius: 6px; <?php echo (int)$q['answer'] === 2 ? 'background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #10b981; font-weight: 500;' : 'background: rgba(255,255,255,0.02); border: 1px solid transparent; color: var(--text-muted);'; ?>">
                                                        <strong>C.</strong> <?php echo htmlspecialchars($q['opt_c']); ?>
                                                    </div>
                                                    <div style="padding: 0.4rem 0.8rem; border-radius: 6px; <?php echo (int)$q['answer'] === 3 ? 'background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #10b981; font-weight: 500;' : 'background: rgba(255,255,255,0.02); border: 1px solid transparent; color: var(--text-muted);'; ?>">
                                                        <strong>D.</strong> <?php echo htmlspecialchars($q['opt_d']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </section>
</main>


<script>
function switchQuestionTab(tab) {
    const manualBtn = document.getElementById('tab-btn-manual');
    const wordBtn = document.getElementById('tab-btn-word');
    const manualForm = document.getElementById('form-container-manual');
    const wordForm = document.getElementById('form-container-word');
    
    if (tab === 'manual') {
        manualForm.style.display = 'block';
        wordForm.style.display = 'none';
        
        manualBtn.style.color = 'var(--accent)';
        manualBtn.style.borderBottomColor = 'var(--accent)';
        manualBtn.style.fontWeight = 'bold';
        
        wordBtn.style.color = 'var(--text-muted)';
        wordBtn.style.borderBottomColor = 'transparent';
        wordBtn.style.fontWeight = 'normal';
    } else {
        manualForm.style.display = 'none';
        wordForm.style.display = 'block';
        
        wordBtn.style.color = 'var(--accent)';
        wordBtn.style.borderBottomColor = 'var(--accent)';
        wordBtn.style.fontWeight = 'bold';
        
        manualBtn.style.color = 'var(--text-muted)';
        manualBtn.style.borderBottomColor = 'transparent';
        manualBtn.style.fontWeight = 'normal';
    }
}

function toggleAccordion(header) {
    const body = header.nextElementSibling;
    const icon = header.querySelector('.accordion-icon');
    const allAccordions = document.querySelectorAll('.q-set-accordion');
    
    if (body.style.display === 'none' || body.style.display === '') {
        body.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
        header.style.background = 'rgba(255,255,255,0.05)';
    } else {
        body.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
        header.style.background = 'rgba(255,255,255,0.02)';
    }
}

// Client-side real-time question search
document.getElementById('question-search')?.addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase().trim();
    const accordions = document.querySelectorAll('.q-set-accordion');
    
    accordions.forEach(acc => {
        const body = acc.querySelector('.accordion-body');
        const header = acc.querySelector('.accordion-header');
        const icon = acc.querySelector('.accordion-icon');
        const items = body.querySelectorAll('.q-row-item');
        let visibleCount = 0;
        
        items.forEach(item => {
            const text = item.getAttribute('data-text');
            if (text.includes(query)) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
        
        // If searching, auto-expand accordions containing results, collapse empty ones
        if (query !== '') {
            if (visibleCount > 0) {
                acc.style.display = 'block';
                body.style.display = 'block';
                icon.style.transform = 'rotate(180deg)';
                header.style.background = 'rgba(255,255,255,0.05)';
            } else {
                acc.style.display = 'none';
            }
        } else {
            // Restore default collapsed state
            acc.style.display = 'block';
            body.style.display = 'none';
            icon.style.transform = 'rotate(0deg)';
            header.style.background = 'rgba(255,255,255,0.02)';
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>