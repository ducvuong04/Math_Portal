<?php
require_once 'includes/functions.php';
include 'includes/header.php';

$grade = isset($_GET['grade']) ? intval($_GET['grade']) : 12;
if ($grade !== 10 && $grade !== 11 && $grade !== 12) {
    $grade = 12;
}

$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : null;
$set_id = isset($_GET['set_id']) ? (int)$_GET['set_id'] : null;
$user_id = $_SESSION['user']['id'] ?? null;

$view = 'topics';
$error_msg = '';

if ($set_id) {
    $quiz_set = get_quiz_set($set_id);
    if (!$quiz_set) {
        redirect('quiz.php?grade=' . $grade);
    }

    // Check Availability
    $has_deadline = !empty($quiz_set['available_until']) && $quiz_set['available_until'] !== '0000-00-00 00:00:00';
    $is_expired = false;
    if ($has_deadline) {
        $now = new DateTime();
        $expiry = new DateTime($quiz_set['available_until']);
        if ($now > $expiry) {
            $is_expired = true;
            $error_msg = "Bộ đề này đã hết hạn từ ngày " . $expiry->format('d/m/Y H:i');
            $view = 'error';
        }
    }

    if (!$is_expired) {
        // Check Attempt Limits
        $attempts = get_user_attempts($user_id, $set_id);
        $has_max_attempts = !empty($quiz_set['max_attempts']) && $quiz_set['max_attempts'] > 0;
        if ($has_max_attempts && $attempts >= $quiz_set['max_attempts']) {
            $error_msg = "Bạn đã hết số lần làm bài cho bộ đề này (Tối đa: " . $quiz_set['max_attempts'] . " lần).";
            $view = 'error';
        } else {
            $view = 'confirm';
            $quizzes = get_quizzes_by_set($set_id);
        }
    }
} elseif ($topic_id) {
    $view = 'sets';
    $quiz_sets = get_quiz_sets_by_topic($topic_id);
} else {
    $chapters = get_chapters_with_topics($grade);
}
?>

<main class="container">
    <div style="padding: 4rem 0 2rem; text-align: center;">
        <h1 style="font-size: 3rem;">Hệ Thống Luyện Tập Lớp <?php echo $grade; ?></h1>
        <p style="color: var(--text-muted);">Học tập - Rèn luyện - Thành công</p>
    </div>

    <?php if ($view === 'error'): ?>
        <div class="animate" style="max-width: 600px; margin: 0 auto; text-align: center;">
            <div class="content-wrapper" style="border-color: #ef4444;">
                <span class="material-icons-round" style="font-size: 4rem; color: #ef4444; margin-bottom: 1rem;">warning</span>
                <h2 style="margin-bottom: 1rem;">Không thể truy cập</h2>
                <p style="color: var(--text-muted); margin-bottom: 2rem;"><?php echo $error_msg; ?></p>
                <a href="quiz.php?grade=<?php echo $grade; ?>" class="btn btn-primary">Quay lại danh sách</a>
            </div>
        </div>

    <?php elseif ($view === 'topics'): ?>
        <!-- Step 1: Select Topic -->
        <div class="animate">
            <?php foreach ($chapters as $chapter): ?>
                <div style="margin-bottom: 3rem;">
                    <h2 style="color: var(--accent); display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                        <span class="material-icons-round"><?php echo $chapter['icon']; ?></span>
                        <?php echo $chapter['title']; ?>
                    </h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
                        <?php foreach ($chapter['topics'] as $topic): ?>
                            <a href="?grade=<?php echo $grade; ?>&topic_id=<?php echo $topic['id']; ?>" class="content-wrapper topic-card" style="text-decoration: none; display: block; height: 100%;">
                                <h3 style="margin-bottom: 1rem;"><?php echo $topic['title']; ?></h3>
                                <p style="color: var(--text-muted); font-size: 0.9rem;"><?php echo $topic['description']; ?></p>
                                <div style="margin-top: 1.5rem; display: flex; align-items: center; color: var(--primary);">
                                    <span>Chọn để luyện tập</span>
                                    <span class="material-icons-round">chevron_right</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php elseif ($view === 'sets'): ?>
        <!-- Step 2: Select Quiz Set -->
        <div class="animate" style="max-width: 800px; margin: 0 auto;">
            <div style="margin-bottom: 2rem;">
                <a href="quiz.php?grade=<?php echo $grade; ?>" style="color: var(--text-muted); text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="material-icons-round">arrow_back</span> Quay lại danh sách chủ đề
                </a>
            </div>
            <h2 style="margin-bottom: 2rem;">Chọn bộ đề luyện tập</h2>
            <?php if (empty($quiz_sets)): ?>
                <div class="content-wrapper" style="text-align: center; padding: 4rem;">
                    <p style="color: var(--text-muted);">Hiện chưa có bộ đề nào cho chủ đề này.</p>
                </div>
            <?php else: ?>
                <?php foreach ($quiz_sets as $set): 
                    $has_deadline = !empty($set['available_until']) && $set['available_until'] !== '0000-00-00 00:00:00';
                    $is_expired = false;
                    $remaining_text = "Không giới hạn";
                    
                    if ($has_deadline) {
                        $expiry = new DateTime($set['available_until']);
                        $now = new DateTime();
                        $diff = $now->diff($expiry);
                        $is_expired = $now > $expiry;
                        
                        $remaining_text = "";
                        if ($is_expired) {
                            $remaining_text = "Đã hết hạn";
                        } else {
                            if ($diff->d > 0) $remaining_text .= $diff->d . " ngày ";
                            if ($diff->h > 0) $remaining_text .= $diff->h . " giờ ";
                            if ($diff->d == 0 && $diff->i > 0) $remaining_text .= $diff->i . " phút";
                            if ($remaining_text == "") $remaining_text = "Sắp hết hạn";
                        }
                    }
                ?>
                    <a href="?grade=<?php echo $grade; ?>&set_id=<?php echo $set['id']; ?>" class="content-wrapper" style="text-decoration: none; display: block; margin-bottom: 1.5rem; transition: transform 0.2s; <?php echo $is_expired ? 'opacity: 0.6; pointer-events: none;' : ''; ?>">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="flex: 1;">
                                <h3 style="margin-bottom: 0.5rem;"><?php echo $set['title']; ?></h3>
                                <p style="color: var(--text-muted); font-size: 0.9rem;"><?php echo $set['description']; ?></p>
                                <div style="margin-top: 1rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; font-size: 0.85rem;">
                                    <div style="color: var(--accent); display: flex; align-items: center; gap: 0.4rem;">
                                        <span class="material-icons-round" style="font-size: 1.1rem;">timer</span>
                                        <?php echo ($set['time_limit'] > 0) ? $set['time_limit'] . ' phút' : 'Không giới hạn'; ?>
                                    </div>
                                    <div style="color: var(--primary); display: flex; align-items: center; gap: 0.4rem;">
                                        <span class="material-icons-round" style="font-size: 1.1rem;">history</span>
                                        Tối đa: <?php echo ($set['max_attempts'] > 0) ? $set['max_attempts'] . ' lần' : 'Không giới hạn'; ?>
                                    </div>
                                    <div style="color: <?php echo $is_expired ? '#ef4444' : '#10b981'; ?>; display: flex; align-items: center; gap: 0.4rem;">
                                        <span class="material-icons-round" style="font-size: 1.1rem;">event_available</span>
                                        Hết hạn: <?php echo $has_deadline ? $expiry->format('d/m/Y H:i') : 'Không giới hạn'; ?>
                                    </div>
                                    <div style="color: #f59e0b; display: flex; align-items: center; gap: 0.4rem; font-weight: bold;">
                                        <span class="material-icons-round" style="font-size: 1.1rem;">hourglass_empty</span>
                                        Còn lại: <?php echo $remaining_text; ?>
                                    </div>
                                </div>
                            </div>
                            <span class="btn <?php echo $is_expired ? 'btn-outline' : 'btn-primary'; ?>" style="margin-left: 2rem;">
                                <?php echo $is_expired ? 'Đã khóa' : 'Làm ngay'; ?>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    <?php elseif ($view === 'confirm'): ?>
        <!-- Step 3: Start Confirmation -->
        <div class="animate" style="max-width: 600px; margin: 0 auto; text-align: center;">
            <div class="content-wrapper">
                <h2 style="color: var(--accent); margin-bottom: 2rem;"><?php echo $quiz_set['title']; ?></h2>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                    <div class="content-wrapper" style="margin:0; padding: 1rem;">
                        <h4 style="color: var(--text-muted);">Số câu hỏi</h4>
                        <div style="font-size: 1.5rem; font-weight: bold;"><?php echo count($quizzes); ?></div>
                    </div>
                    <div class="content-wrapper" style="margin:0; padding: 1rem;">
                        <h4 style="color: var(--text-muted);">Thời gian</h4>
                        <div style="font-size: 1.5rem; font-weight: bold;"><?php echo ($quiz_set['time_limit'] > 0) ? $quiz_set['time_limit'] . ' phút' : 'Không giới hạn'; ?></div>
                    </div>
                </div>
                <div style="text-align: left; margin-bottom: 2rem; color: var(--text-muted); font-size: 0.9rem; line-height: 1.6;">
                    <p style="margin: 0.4rem 0;">• Bạn có tối đa <strong><?php echo ($quiz_set['max_attempts'] > 0) ? $quiz_set['max_attempts'] . ' lần làm bài' : 'không giới hạn lần làm bài'; ?></strong>. (Bạn đã làm: <?php echo $attempts; ?> lần)</p>
                    <?php if ($quiz_set['time_limit'] > 0): ?>
                        <p style="margin: 0.4rem 0;">• Khi nhấn nút bắt đầu, đồng hồ sẽ đếm ngược ngay lập tức.</p>
                        <p style="margin: 0.4rem 0;">• Nếu hết thời gian, hệ thống sẽ tự động thu bài.</p>
                    <?php else: ?>
                        <p style="margin: 0.4rem 0;">• Bộ đề này không giới hạn thời gian làm bài, bạn có thể hoàn thành thoải mái.</p>
                    <?php endif; ?>
                </div>
                <button onclick="startQuiz()" class="btn btn-primary" style="width: 100%; padding: 1.2rem; font-size: 1.2rem;">Bắt đầu làm bài</button>
                <a href="quiz.php?grade=<?php echo $grade; ?>" style="display: block; margin-top: 1.5rem; color: var(--text-muted); text-decoration: none;">Quay lại</a>
            </div>
        </div>

        <!-- Step 4: The Actual Quiz (Hidden until start) -->
        <div id="quiz-ui" style="display: none; max-width: 800px; margin: 0 auto;" class="animate">
            <div id="timer-box" style="position: sticky; top: 80px; z-index: 100; margin-bottom: 2rem;">
                <div class="content-wrapper" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: rgba(0,0,0,0.7); backdrop-filter: blur(20px); border-color: var(--accent);">
                    <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--accent);">
                        <span class="material-icons-round">timer</span>
                        <span id="countdown" style="font-size: 1.5rem; font-weight: bold; font-variant-numeric: tabular-nums;">00:00</span>
                    </div>
                    <button onclick="confirmSubmit()" class="btn btn-outline" style="border-color: #ef4444; color: #ef4444;">Nộp bài sớm</button>
                </div>
            </div>

            <div id="quiz-results" class="content-wrapper animate" style="display: none; text-align: center; border-color: var(--accent); margin-bottom: 2rem;">
                <h2 style="color: var(--accent); margin-bottom: 1rem;">Kết Quả</h2>
                <div style="font-size: 3rem; font-weight: bold; margin-bottom: 0.5rem;">
                    <span id="score-value">0</span> / <?php echo count($quizzes); ?>
                </div>
                <div id="time-display" style="color: var(--accent); font-weight: bold; margin-bottom: 1rem;"></div>
                <div id="status-msg" style="margin-bottom: 2rem; color: var(--text-muted);"></div>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button onclick="showReview()" class="btn btn-outline">Xem lại bài làm</button>
                    <a href="quiz.php?grade=<?php echo $grade; ?>" class="btn btn-primary">Hoàn tất</a>
                </div>
            </div>

            <div id="questions-list">
                <?php foreach ($quizzes as $index => $quiz): ?>
                    <div class="content-wrapper animate quiz-item" data-answer="<?php echo $quiz['answer']; ?>" style="margin-bottom: 2rem;">
                        <div style="margin-bottom: 1.5rem;">
                            <span style="background: var(--primary); color: white; padding: 0.2rem 0.8rem; border-radius: 20px; font-size: 0.8rem;">Câu <?php echo $index + 1; ?></span>
                        </div>
                        <h3 style="margin-bottom: 2rem; line-height: 1.6;"><?php echo $quiz['question']; ?></h3>
                        <div class="options">
                            <?php foreach ($quiz['options'] as $oIndex => $option): ?>
                                <div class="option" onclick="selectOption(<?php echo $index; ?>, <?php echo $oIndex; ?>, this)">
                                    <?php echo $option; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div style="text-align: center; margin-top: 4rem; padding-bottom: 4rem;">
                    <button id="submit-btn" onclick="submitQuiz()" class="btn btn-primary" style="padding: 1.2rem 4rem; font-size: 1.1rem;">Nộp bài & Kết thúc</button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<style>
.topic-card { border: 1px solid var(--glass-border); transition: all 0.3s ease; }
.topic-card:hover { border-color: var(--accent); transform: translateY(-5px); background: rgba(255, 255, 255, 0.05); }
.option.selected { border-color: var(--accent); background: rgba(245, 158, 11, 0.1); }
</style>

<script>
    let userAnswers = {};
    let timerInterval;
    let totalTime = <?php echo ($quiz_set['time_limit'] ?? 0) * 60; ?>;
    let timeRemaining = totalTime;
    let isUnlimited = (totalTime === 0);
    let timeElapsed = 0;

    function startQuiz() {
        document.querySelector('.content-wrapper').parentElement.style.display = 'none'; // Hide confirm box
        document.getElementById('quiz-ui').style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        timerInterval = setInterval(() => {
            if (isUnlimited) {
                timeElapsed++;
                updateTimerDisplay();
            } else {
                timeRemaining--;
                updateTimerDisplay();
                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    submitQuiz(true);
                }
            }
        }, 1000);
        updateTimerDisplay();
    }

    function updateTimerDisplay() {
        const timeToUse = isUnlimited ? timeElapsed : timeRemaining;
        const mins = Math.floor(timeToUse / 60).toString().padStart(2, '0');
        const secs = (timeToUse % 60).toString().padStart(2, '0');
        const display = document.getElementById('countdown');
        if (display) {
            display.innerText = `${mins}:${secs}`;
            if (!isUnlimited && timeRemaining < 60) {
                display.style.color = '#ef4444';
            }
        }
    }

    function selectOption(qIndex, oIndex, element) {
        if (element.parentElement.style.pointerEvents === 'none') return;
        const parent = element.parentElement;
        parent.querySelectorAll('.option').forEach(opt => opt.classList.remove('selected'));
        element.classList.add('selected');
        userAnswers[qIndex] = oIndex;
    }

    function confirmSubmit() {
        if(confirm('Bạn có chắc chắn muốn nộp bài sớm không?')) submitQuiz();
    }

    function showReview() {
        document.getElementById('questions-list').style.display = 'block';
        document.getElementById('quiz-results').scrollIntoView({ behavior: 'smooth' });
    }

    function submitQuiz(auto = false) {
        clearInterval(timerInterval);
        const questions = document.querySelectorAll('.quiz-item');
        let score = 0;
        let timeSpent = isUnlimited ? timeElapsed : (totalTime - timeRemaining);

        questions.forEach((q, index) => {
            const correct = parseInt(q.getAttribute('data-answer'));
            const selected = userAnswers[index];
            const options = q.querySelectorAll('.option');
            options.forEach((opt, oIdx) => {
                opt.style.pointerEvents = 'none';
                if (oIdx === correct) {
                    opt.style.borderColor = '#10b981';
                    opt.style.background = 'rgba(16, 185, 129, 0.1)';
                }
                if (selected === oIdx && selected !== correct) {
                    opt.style.borderColor = '#ef4444';
                    opt.style.background = 'rgba(239, 68, 68, 0.1)';
                }
            });
            if (selected === correct) score++;
        });

        // Save results to DB
        const formData = new FormData();
        formData.append('set_id', '<?php echo $set_id; ?>');
        formData.append('score', score);
        formData.append('total', questions.length);
        formData.append('time_spent', timeSpent);

        fetch('record_score.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (!data.success) alert(data.message);
        });

        const m = Math.floor(timeSpent / 60).toString().padStart(2, '0');
        const s = (timeSpent % 60).toString().padStart(2, '0');

        document.getElementById('questions-list').style.display = 'none';
        document.getElementById('submit-btn').style.display = 'none';
        document.getElementById('timer-box').style.display = 'none';
        document.getElementById('quiz-results').style.display = 'block';
        document.getElementById('score-value').innerText = score;
        document.getElementById('time-display').innerText = `Thời gian hoàn thành: ${m}:${s}`;
        document.getElementById('status-msg').innerText = auto ? 'Hết thời gian! Bài làm đã được tự động nộp.' : 'Bạn đã hoàn thành bài làm.';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>

<?php include 'includes/footer.php'; ?>