<?php
require_once '../includes/functions.php';
if (!is_teacher()) redirect('../login.php');

$content = get_all_content();
$chaptersCount = count($content['chapters']);
$quizzesCount = count($content['quizzes']);
$toolsCount = $pdo->query("SELECT COUNT(*) FROM tools")->fetchColumn();
$usersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

include '../includes/header.php';
?>

<main class="container" style="padding: 4rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;">
        <div>
            <h1 style="font-size: 2.5rem;">Bảng Quản Trị Giáo Viên</h1>
            <p style="color: var(--text-muted);">Quản lý toàn bộ hệ thống MathPortal 12 Pro.</p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <a href="manage_users.php" class="btn btn-outline" style="display: flex; align-items: center; gap: 0.5rem;">
                <span class="material-icons-round">group</span> Hồ sơ người dùng
            </a>
            <a href="../index.php" class="btn btn-primary">Xem trang chủ</a>
        </div>
    </div>

    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
        <!-- Manage Theory -->
        <div class="card animate">
            <div class="card-icon"><span class="material-icons-round">auto_stories</span></div>
            <h3>Quản lý Lý thuyết</h3>
            <p>Biên soạn và cập nhật nội dung bài giảng lý thuyết.</p>
            <div style="font-size: 0.8rem; color: var(--accent); margin-bottom: 1.5rem;">Số chương: <?php echo $chaptersCount; ?></div>
            <a href="manage_theory.php" class="btn btn-primary" style="width: 100%; text-align: center;">Cập nhật</a>
        </div>

        <!-- Manage Quizzes -->
        <div class="card animate">
            <div class="card-icon"><span class="material-icons-round">fact_check</span></div>
            <h3>Quản lý Câu hỏi</h3>
            <p>Quản lý ngân hàng câu hỏi trắc nghiệm cho học sinh.</p>
            <div style="font-size: 0.8rem; color: var(--accent); margin-bottom: 1.5rem;">Số câu hỏi: <?php echo $quizzesCount; ?></div>
            <a href="manage_quiz.php" class="btn btn-primary" style="width: 100%; text-align: center;">Cập nhật</a>
        </div>

        <!-- Manage Tools -->
        <div class="card animate">
            <div class="card-icon"><span class="material-icons-round">construction</span></div>
            <h3>Quản lý Công cụ</h3>
            <p>Thêm mới hoặc sửa các công cụ giải toán trực tuyến.</p>
            <div style="font-size: 0.8rem; color: var(--accent); margin-bottom: 1.5rem;">Số công cụ: <?php echo $toolsCount; ?></div>
            <a href="manage_tools.php" class="btn btn-primary" style="width: 100%; text-align: center;">Cập nhật</a>
        </div>

        <!-- System Users -->
        <div class="card animate">
            <div class="card-icon"><span class="material-icons-round">badge</span></div>
            <h3>Hồ sơ Thành viên</h3>
            <p>Xem danh sách học sinh và lịch sử tham gia hệ thống.</p>
            <div style="font-size: 0.8rem; color: var(--accent); margin-bottom: 1.5rem;">Tổng số: <?php echo $usersCount; ?> thành viên</div>
            <a href="manage_users.php" class="btn btn-outline" style="width: 100%; text-align: center;">Xem hồ sơ</a>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
