<?php
require_once '../includes/functions.php';
if (!is_teacher()) redirect('../login.php');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('manage_users.php');
}

$student_id = (int)$_GET['id'];

// 1. Fetch Student User details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'student'");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    redirect('manage_users.php');
}

// 2. Fetch Assignments stats
$sub_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM assignment_submissions WHERE student_id = ?");
$sub_count_stmt->execute([$student_id]);
$completed_assignments_count = $sub_count_stmt->fetchColumn();

$avg_score_stmt = $pdo->prepare("SELECT AVG(score) FROM assignment_submissions WHERE student_id = ? AND score IS NOT NULL");
$avg_score_stmt->execute([$student_id]);
$avg_assignment_score = $avg_score_stmt->fetchColumn();
$avg_assignment_score = $avg_assignment_score !== null ? round($avg_assignment_score, 2) : null;

// 3. Fetch Assignment Submissions list
$submissions_stmt = $pdo->prepare("
    SELECT s.*, a.title, a.type as assignment_type, a.deadline 
    FROM assignment_submissions s
    JOIN assignments a ON s.assignment_id = a.id
    WHERE s.student_id = ?
    ORDER BY s.submitted_at DESC
");
$submissions_stmt->execute([$student_id]);
$submissions = $submissions_stmt->fetchAll();

// 4. Fetch Practice stats
$practice_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM quiz_attempts WHERE user_id = ?");
$practice_count_stmt->execute([$student_id]);
$completed_practices_count = $practice_count_stmt->fetchColumn();

$avg_practice_stmt = $pdo->prepare("
    SELECT AVG((qa.score / qa.total_questions) * 10) 
    FROM quiz_attempts qa 
    WHERE qa.user_id = ? AND qa.total_questions > 0
");
$avg_practice_stmt->execute([$student_id]);
$avg_practice_score = $avg_practice_stmt->fetchColumn();
$avg_practice_score = $avg_practice_score !== null ? round($avg_practice_score, 2) : null;

// 5. Fetch Practice attempts list
$practices_stmt = $pdo->prepare("
    SELECT qa.*, qs.title as set_title, t.title as topic_title
    FROM quiz_attempts qa
    JOIN quiz_sets qs ON qa.quiz_set_id = qs.id
    JOIN topics t ON qs.topic_id = t.id
    WHERE qa.user_id = ?
    ORDER BY qa.completed_at DESC
");
$practices_stmt->execute([$student_id]);
$practices = $practices_stmt->fetchAll();

// Avatar dynamic initial & background gradient generator
$initials = strtoupper(substr($student['username'], 0, 2));
$gradients = [
    'linear-gradient(135deg, #4f46e5, #06b6d4)',
    'linear-gradient(135deg, #ec4899, #8b5cf6)',
    'linear-gradient(135deg, #f59e0b, #e11d48)',
    'linear-gradient(135deg, #10b981, #3b82f6)'
];
$gradient = $gradients[($student_id % count($gradients))];

include '../includes/header.php';
?>

<main class="container" style="padding: 4rem 0;">
    <a href="manage_users.php" style="color: var(--accent); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 2rem;">
        <span class="material-icons-round">arrow_back</span> Hồ Sơ Đăng Nhập & Thành Viên
    </a>

    <!-- Profile Header Section -->
    <div class="content-wrapper" style="display: flex; align-items: center; gap: 2rem; padding: 2rem; margin-bottom: 3rem; flex-wrap: wrap; border-color: var(--glass-border);">
        <div style="width: 80px; height: 80px; border-radius: 50%; background: <?php echo $gradient; ?>; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; font-weight: bold; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
            <?php echo $initials; ?>
        </div>
        <div style="flex: 1; min-width: 250px;">
            <h1 style="margin: 0 0 0.5rem 0; font-size: 2rem; color: var(--text);"><?php echo htmlspecialchars($student['username']); ?></h1>
            <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; font-size: 0.9rem; color: var(--text-muted);">
                <span style="display: flex; align-items: center; gap: 0.3rem;">
                    <span class="material-icons-round" style="font-size: 1.1rem; color: var(--accent);">calendar_today</span>
                    Ngày tham gia: <?php echo date('d/m/Y H:i', strtotime($student['created_at'])); ?>
                </span>
                <span style="display: flex; align-items: center; gap: 0.3rem;">
                    <span class="material-icons-round" style="font-size: 1.1rem; color: #10b981;">check_circle</span>
                    Trạng thái: Đang hoạt động
                </span>
            </div>
        </div>
        <span style="background: rgba(79, 70, 229, 0.15); color: var(--primary-light); padding: 0.4rem 1.2rem; border-radius: 20px; font-size: 0.9rem; font-weight: 600; border: 1px solid rgba(79, 70, 229, 0.3);">
            🎓 Học sinh
        </span>
    </div>

    <!-- Performance Dashboard metrics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <!-- Card 1 -->
        <div class="content-wrapper" style="padding: 1.5rem; border-color: var(--glass-border); position: relative; overflow: hidden; display: flex; align-items: center; gap: 1.2rem;">
            <div style="background: rgba(79, 70, 229, 0.1); padding: 0.8rem; border-radius: 12px; color: var(--primary-light);">
                <span class="material-icons-round" style="font-size: 2rem;">assignment</span>
            </div>
            <div>
                <span style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.2rem;">Bài tập đã nộp</span>
                <strong style="font-size: 1.8rem; color: var(--text);"><?php echo $completed_assignments_count; ?></strong>
            </div>
        </div>
        <!-- Card 2 -->
        <div class="content-wrapper" style="padding: 1.5rem; border-color: var(--glass-border); position: relative; overflow: hidden; display: flex; align-items: center; gap: 1.2rem;">
            <div style="background: rgba(16, 185, 129, 0.1); padding: 0.8rem; border-radius: 12px; color: #10b981;">
                <span class="material-icons-round" style="font-size: 2rem;">auto_awesome</span>
            </div>
            <div>
                <span style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.2rem;">Điểm trung bình bài tập</span>
                <strong style="font-size: 1.8rem; color: #10b981;">
                    <?php echo $avg_assignment_score !== null ? $avg_assignment_score . '<span style="font-size:1rem; color: var(--text-muted);">/10</span>' : '<span style="font-size:1rem; color:var(--text-muted);">Chưa có</span>'; ?>
                </strong>
            </div>
        </div>
        <!-- Card 3 -->
        <div class="content-wrapper" style="padding: 1.5rem; border-color: var(--glass-border); position: relative; overflow: hidden; display: flex; align-items: center; gap: 1.2rem;">
            <div style="background: rgba(245, 158, 11, 0.1); padding: 0.8rem; border-radius: 12px; color: var(--accent);">
                <span class="material-icons-round" style="font-size: 2rem;">psychology</span>
            </div>
            <div>
                <span style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.2rem;">Lượt luyện tập</span>
                <strong style="font-size: 1.8rem; color: var(--text);"><?php echo $completed_practices_count; ?></strong>
            </div>
        </div>
        <!-- Card 4 -->
        <div class="content-wrapper" style="padding: 1.5rem; border-color: var(--glass-border); position: relative; overflow: hidden; display: flex; align-items: center; gap: 1.2rem;">
            <div style="background: rgba(6, 182, 212, 0.1); padding: 0.8rem; border-radius: 12px; color: #06b6d4;">
                <span class="material-icons-round" style="font-size: 2rem;">speed</span>
            </div>
            <div>
                <span style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.2rem;">Điểm trung bình luyện tập</span>
                <strong style="font-size: 1.8rem; color: #06b6d4;">
                    <?php echo $avg_practice_score !== null ? $avg_practice_score . '<span style="font-size:1rem; color: var(--text-muted);">/10</span>' : '<span style="font-size:1rem; color:var(--text-muted);">Chưa có</span>'; ?>
                </strong>
            </div>
        </div>
    </div>

    <!-- Reports Section -->
    <div class="content-wrapper" style="padding: 0; overflow: hidden; border-color: var(--glass-border);">
        <div style="display: flex; border-bottom: 1px solid var(--glass-border); background: rgba(255,255,255,0.02);">
            <button onclick="switchReportTab('homework')" id="tab-homework" style="flex: 1; padding: 1.2rem; background: none; border: none; border-bottom: 3px solid var(--accent); color: var(--accent); font-weight: bold; cursor: pointer; font-size: 1.1rem; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; transition: all 0.2s;">
                <span class="material-icons-round">assignment</span> Báo Cáo Bài Tập Về Nhà
            </button>
            <button onclick="switchReportTab('practice')" id="tab-practice" style="flex: 1; padding: 1.2rem; background: none; border: none; border-bottom: 3px solid transparent; color: var(--text-muted); font-weight: normal; cursor: pointer; font-size: 1.1rem; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; transition: all 0.2s;">
                <span class="material-icons-round">history</span> Lịch Sử Luyện Tập Tự Do
            </button>
        </div>

        <!-- 1. HOMEWORK REPORT CONTAINER -->
        <div id="container-homework" style="display: block; padding: 1.5rem;">
            <?php if (empty($submissions)): ?>
                <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                    <span class="material-icons-round" style="font-size: 3rem; margin-bottom: 1rem; color: rgba(255,255,255,0.1);">inbox</span>
                    <p>Chọc sinh này chưa nộp bài tập nào.</p>
                </div>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--glass-border); color: var(--text-muted);">
                            <th style="padding: 1rem 1.5rem;">Tên bài tập</th>
                            <th style="padding: 1rem 1.5rem;">Loại bài tập</th>
                            <th style="padding: 1rem 1.5rem;">Thời gian nộp</th>
                            <th style="padding: 1rem 1.5rem;">Điểm số</th>
                            <th style="padding: 1rem 1.5rem; text-align: right;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $sub): 
                            $type_lbl = 'Trắc nghiệm online';
                            if ($sub['assignment_type'] === 'file') $type_lbl = 'Tải file tự luận';
                            elseif ($sub['assignment_type'] === 'word_quiz') $type_lbl = 'Trắc nghiệm từ file Word';

                            // Determine score badge
                            $score = $sub['score'];
                            $badge_style = 'background: rgba(255,255,255,0.05); color: var(--text-muted);';
                            if ($score !== null) {
                                if ($score >= 8.0) {
                                    $badge_style = 'background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);';
                                } elseif ($score >= 5.0) {
                                    $badge_style = 'background: rgba(245, 158, 11, 0.15); color: var(--accent); border: 1px solid rgba(245, 158, 11, 0.3);';
                                } else {
                                    $badge_style = 'background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3);';
                                }
                            }
                        ?>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.01)'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 1.2rem 1.5rem; font-weight: 500; color: var(--text);"><?php echo htmlspecialchars($sub['title']); ?></td>
                                <td style="padding: 1.2rem 1.5rem; color: var(--text-muted); font-size: 0.9rem;"><?php echo $type_lbl; ?></td>
                                <td style="padding: 1.2rem 1.5rem; color: var(--text-muted); font-size: 0.9rem;"><?php echo date('d/m/Y H:i', strtotime($sub['submitted_at'])); ?></td>
                                <td style="padding: 1.2rem 1.5rem;">
                                    <span style="<?php echo $badge_style; ?> padding: 0.3rem 0.8rem; border-radius: 12px; font-weight: bold; font-size: 0.85rem;">
                                        <?php echo $score !== null ? $score . ' / 10' : 'Chờ chấm'; ?>
                                    </span>
                                </td>
                                <td style="padding: 1.2rem 1.5rem; text-align: right;">
                                    <?php if ($sub['assignment_type'] === 'file'): ?>
                                        <a href="../<?php echo htmlspecialchars($sub['file_path']); ?>" target="_blank" class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; border-color: rgba(255,255,255,0.1); border-radius: 6px; display: inline-flex; align-items: center; gap: 0.2rem;">
                                            <span class="material-icons-round" style="font-size: 1rem; color: var(--accent);">download</span> Tải bài làm
                                        </a>
                                    <?php elseif (!empty($sub['answers_json'])): ?>
                                        <button onclick='viewDetails(<?php echo $sub['id']; ?>, <?php echo json_encode(htmlspecialchars($sub['title'])); ?>)' class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; border-color: rgba(255,255,255,0.1); border-radius: 6px; display: inline-flex; align-items: center; gap: 0.2rem;">
                                            <span class="material-icons-round" style="font-size: 1rem; color: var(--accent);">analytics</span> Xem chi tiết đáp án
                                        </button>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- 2. PRACTICE REPORT CONTAINER -->
        <div id="container-practice" style="display: none; padding: 1.5rem;">
            <?php if (empty($practices)): ?>
                <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                    <span class="material-icons-round" style="font-size: 3rem; margin-bottom: 1rem; color: rgba(255,255,255,0.1);">inbox</span>
                    <p>Học sinh này chưa tham gia luyện tập tự do.</p>
                </div>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--glass-border); color: var(--text-muted);">
                            <th style="padding: 1rem 1.5rem;">Bộ đề luyện tập</th>
                            <th style="padding: 1rem 1.5rem;">Chương/Bài</th>
                            <th style="padding: 1rem 1.5rem;">Thời gian làm bài</th>
                            <th style="padding: 1rem 1.5rem;">Thời lượng</th>
                            <th style="padding: 1rem 1.5rem;">Kết quả</th>
                            <th style="padding: 1rem 1.5rem;">Tỷ lệ đạt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($practices as $p): 
                            $correct = $p['score'];
                            $total = $p['total_questions'];
                            $pct = $total > 0 ? round(($correct / $total) * 100) : 0;
                            
                            // Format time spent
                            $minutes = floor($p['time_spent'] / 60);
                            $seconds = $p['time_spent'] % 60;
                            $time_spent_lbl = sprintf('%d phút %02d giây', $minutes, $seconds);

                            // Determine color for rate
                            $rate_color = '#ef4444';
                            if ($pct >= 80) $rate_color = '#10b981';
                            elseif ($pct >= 50) $rate_color = 'var(--accent)';
                        ?>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.01)'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 1.2rem 1.5rem; font-weight: 500; color: var(--text);"><?php echo htmlspecialchars($p['set_title']); ?></td>
                                <td style="padding: 1.2rem 1.5rem; color: var(--text-muted); font-size: 0.9rem;"><?php echo htmlspecialchars($p['topic_title']); ?></td>
                                <td style="padding: 1.2rem 1.5rem; color: var(--text-muted); font-size: 0.9rem;"><?php echo date('d/m/Y H:i', strtotime($p['completed_at'])); ?></td>
                                <td style="padding: 1.2rem 1.5rem; color: var(--text-muted); font-size: 0.9rem;"><?php echo $time_spent_lbl; ?></td>
                                <td style="padding: 1.2rem 1.5rem; font-weight: 600; color: var(--text);">
                                    <span style="color: <?php echo $rate_color; ?>;"><?php echo $correct; ?></span> / <?php echo $total; ?> câu
                                </td>
                                <td style="padding: 1.2rem 1.5rem;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; min-width: 120px;">
                                        <div style="flex: 1; height: 6px; background: rgba(255,255,255,0.05); border-radius: 3px; overflow: hidden;">
                                            <div style="height: 100%; width: <?php echo $pct; ?>%; background: <?php echo $rate_color; ?>; border-radius: 3px;"></div>
                                        </div>
                                        <span style="font-size: 0.85rem; font-weight: bold; color: <?php echo $rate_color; ?>;"><?php echo $pct; ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Details Modal overlay for Quiz JSON Answers View -->
<div class="custom-modal-overlay" id="details-modal-overlay">
    <div class="custom-modal" style="max-width: 600px; width: 90%; background: #12131a; border-color: rgba(255,255,255,0.08);">
        <div class="custom-modal-header" id="details-modal-title" style="color: var(--accent); border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.8rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <span>Chi tiết đáp án</span>
            <button onclick="closeDetails()" style="background:none; border:none; color: var(--text-muted); cursor:pointer; display:inline-flex; align-items:center;">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div class="custom-modal-body" id="details-modal-body" style="max-height: 400px; overflow-y: auto; padding-right: 0.5rem;">
            <div style="display: flex; justify-content: center; padding: 2rem;">
                <span class="material-icons-round" style="font-size: 3rem; color: var(--accent);" class="spin">sync</span>
            </div>
        </div>
        <div class="custom-modal-footer" style="border-top: 1px solid rgba(255,255,255,0.05); padding-top: 1rem; margin-top: 1.5rem;">
            <button onclick="closeDetails()" class="btn btn-primary">Đóng</button>
        </div>
    </div>
</div>

<script>
function switchReportTab(tab) {
    const tabHomework = document.getElementById('tab-homework');
    const tabPractice = document.getElementById('tab-practice');
    const containerHomework = document.getElementById('container-homework');
    const containerPractice = document.getElementById('container-practice');
    
    if (tab === 'homework') {
        containerHomework.style.display = 'block';
        containerPractice.style.display = 'none';
        
        tabHomework.style.color = 'var(--accent)';
        tabHomework.style.borderBottomColor = 'var(--accent)';
        tabHomework.style.fontWeight = 'bold';
        
        tabPractice.style.color = 'var(--text-muted)';
        tabPractice.style.borderBottomColor = 'transparent';
        tabPractice.style.fontWeight = 'normal';
    } else {
        containerHomework.style.display = 'none';
        containerPractice.style.display = 'block';
        
        tabPractice.style.color = 'var(--accent)';
        tabPractice.style.borderBottomColor = 'var(--accent)';
        tabPractice.style.fontWeight = 'bold';
        
        tabHomework.style.color = 'var(--text-muted)';
        tabHomework.style.borderBottomColor = 'transparent';
        tabHomework.style.fontWeight = 'normal';
    }
}

function viewDetails(submissionId, title) {
    document.getElementById('details-modal-title').firstElementChild.innerText = 'Đáp án: ' + decodeURIComponent(title);
    document.getElementById('details-modal-overlay').classList.add('active');
    
    // Fetch submission answers details asynchronously
    fetch('get_submission_details.php?id=' + submissionId)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let html = '<div style="display:grid; gap: 1rem;">';
                data.details.forEach((q, idx) => {
                    const isCorrect = q.selected === q.correct;
                    const optLetters = ['A', 'B', 'C', 'D'];
                    const selectedLetter = optLetters[q.selected] || 'Chưa trả lời';
                    const correctLetter = optLetters[q.correct];
                    
                    html += `
                        <div style="padding: 1rem; border-radius: 8px; background: rgba(255,255,255,0.02); border: 1px solid ${isCorrect ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)'};">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 0.5rem;">
                                <strong style="color: var(--primary); font-size:0.9rem;">Câu ${idx + 1}</strong>
                                <span style="font-size:0.8rem; font-weight:600; display:inline-flex; align-items:center; gap: 0.2rem; color: ${isCorrect ? '#10b981' : '#ef4444'}">
                                    <span class="material-icons-round" style="font-size:1rem;">${isCorrect ? 'check_circle' : 'cancel'}</span>
                                    ${isCorrect ? 'Đúng' : 'Sai'}
                                </span>
                            </div>
                            <p style="margin: 0 0 0.8rem 0; font-size:0.95rem;">${q.question}</p>
                            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.4rem; font-size: 0.85rem; color: var(--text-muted);">
                                <div><strong>A.</strong> ${q.opt_a}</div>
                                <div><strong>B.</strong> ${q.opt_b}</div>
                                <div><strong>C.</strong> ${q.opt_c}</div>
                                <div><strong>D.</strong> ${q.opt_d}</div>
                            </div>
                            <div style="margin-top:0.8rem; font-size:0.85rem; display:flex; gap: 1rem; border-top: 1px solid rgba(255,255,255,0.03); padding-top:0.6rem;">
                                <span>Đã chọn: <strong style="color: ${isCorrect ? '#10b981' : '#ef4444'}">${selectedLetter}</strong></span>
                                <span>Đáp án đúng: <strong style="color: #10b981">${correctLetter}</strong></span>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                document.getElementById('details-modal-body').innerHTML = html;
            } else {
                document.getElementById('details-modal-body').innerHTML = `<p style="color: #ef4444; text-align:center;">Lỗi: ${data.message}</p>`;
            }
        })
        .catch(err => {
            document.getElementById('details-modal-body').innerHTML = `<p style="color: #ef4444; text-align:center;">Lỗi kết nối máy chủ.</p>`;
        });
}

function closeDetails() {
    document.getElementById('details-modal-overlay').classList.remove('active');
    setTimeout(() => {
        document.getElementById('details-modal-body').innerHTML = `
            <div style="display: flex; justify-content: center; padding: 2rem;">
                <span class="material-icons-round spin" style="font-size: 3rem; color: var(--accent);">sync</span>
            </div>
        `;
    }, 300);
}
</script>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.spin {
    animation: spin 1s linear infinite;
}
</style>

<?php include '../includes/footer.php'; ?>
