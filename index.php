<?php
require_once 'includes/functions.php';

// Fetch chapters for each grade level
$chapters10 = get_all_content(10)['chapters'];
$chapters11 = get_all_content(11)['chapters'];
$chapters12 = get_all_content(12)['chapters'];

include 'includes/header.php';
?>

<main class="container">
    <section class="hero animate">
        <h1>Chinh Phục Toán THPT</h1>
        <p>Hệ thống học tập thông minh, tra cứu công thức nhanh chóng và luyện tập hiệu quả cho khối THPT.</p>
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <a href="theory.php?grade=12" id="hero-action-btn" class="btn btn-primary">Khám phá Lý thuyết 12</a>
            <a href="calculator.php" class="btn btn-outline">Mở Máy tính Casio</a>
        </div>
    </section>

    <!-- Premium Grade Tab Selector -->
    <div class="grade-tabs-container animate" style="animation-delay: 0.1s;">
        <button class="grade-tab active" onclick="switchGrade(12)">Toán Lớp 12</button>
        <button class="grade-tab" onclick="switchGrade(11)">Toán Lớp 11</button>
        <button class="grade-tab" onclick="switchGrade(10)">Toán Lớp 10</button>
    </div>

    <!-- Grade 12 Content Section -->
    <div id="grade-12-content" class="grade-content active">
        <div class="grid">
            <?php foreach ($chapters12 as $key => $chapter): ?>
                <div class="card animate">
                    <div class="card-icon">
                        <span class="material-icons-round"><?php echo $chapter['icon']; ?></span>
                    </div>
                    <h3><?php echo $chapter['title']; ?></h3>
                    <p>Khám phá các chuyên đề lý thuyết nâng cao lớp 12.</p>
                    <ul style="list-style: none; margin-bottom: 1.5rem; color: var(--text-main);">
                        <?php foreach (array_slice($chapter['topics'], 0, 3) as $topic): ?>
                            <li style="margin-bottom: 0.5rem; font-size: 0.9rem; opacity: 0.9;">
                                <span style="color: var(--accent);">•</span> <?php echo $topic['title']; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="theory.php?grade=12&chapter=<?php echo $key; ?>" class="btn btn-outline"
                        style="width: 100%; text-align: center;">Xem chi tiết</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Grade 11 Content Section -->
    <div id="grade-11-content" class="grade-content">
        <div class="grid">
            <?php if (empty($chapters11)): ?>
                <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                    <p style="color: var(--text-muted);">Nội dung lý thuyết Lớp 11 đang được cập nhật.</p>
                </div>
            <?php else: ?>
                <?php foreach ($chapters11 as $key => $chapter): ?>
                    <div class="card animate">
                        <div class="card-icon">
                            <span class="material-icons-round"><?php echo $chapter['icon']; ?></span>
                        </div>
                        <h3><?php echo $chapter['title']; ?></h3>
                        <p>Tìm hiểu các khái niệm, công thức trọng tâm lớp 11.</p>
                        <ul style="list-style: none; margin-bottom: 1.5rem; color: var(--text-main);">
                            <?php foreach (array_slice($chapter['topics'], 0, 3) as $topic): ?>
                                <li style="margin-bottom: 0.5rem; font-size: 0.9rem; opacity: 0.9;">
                                    <span style="color: var(--accent);">•</span> <?php echo $topic['title']; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="theory.php?grade=11&chapter=<?php echo $key; ?>" class="btn btn-outline"
                            style="width: 100%; text-align: center;">Xem chi tiết</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Grade 10 Content Section -->
    <div id="grade-10-content" class="grade-content">
        <div class="grid">
            <?php if (empty($chapters10)): ?>
                <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                    <p style="color: var(--text-muted);">Nội dung lý thuyết Lớp 10 đang được cập nhật.</p>
                </div>
            <?php else: ?>
                <?php foreach ($chapters10 as $key => $chapter): ?>
                    <div class="card animate">
                        <div class="card-icon">
                            <span class="material-icons-round"><?php echo $chapter['icon']; ?></span>
                        </div>
                        <h3><?php echo $chapter['title']; ?></h3>
                        <p>Nền tảng kiến thức và công thức toán học lớp 10.</p>
                        <ul style="list-style: none; margin-bottom: 1.5rem; color: var(--text-main);">
                            <?php foreach (array_slice($chapter['topics'], 0, 3) as $topic): ?>
                                <li style="margin-bottom: 0.5rem; font-size: 0.9rem; opacity: 0.9;">
                                    <span style="color: var(--accent);">•</span> <?php echo $topic['title']; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="theory.php?grade=10&chapter=<?php echo $key; ?>" class="btn btn-outline"
                            style="width: 100%; text-align: center;">Xem chi tiết</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Shared Utilities Section -->
    <div style="margin-top: 4rem;">
        <div class="card animate" style="display: flex; flex-direction: row; gap: 2rem; align-items: center; justify-content: space-between; flex-wrap: wrap;">
            <div style="display: flex; gap: 1.5rem; align-items: center;">
                <div class="card-icon" style="background: var(--accent); color: white; margin-bottom: 0;">
                    <span class="material-icons-round">calculate</span>
                </div>
                <div>
                    <h3 style="margin-bottom: 0.2rem;">Giả lập Máy tính fx-580VN X</h3>
                    <p style="margin-bottom: 0; color: var(--text-muted);">Sử dụng máy tính Casio ảo trực tuyến hỗ trợ tính toán công thức cực mạnh.</p>
                </div>
            </div>
            <a href="calculator.php" class="btn btn-primary">Mở Máy tính</a>
        </div>
    </div>
</main>

<style>
.grade-content {
    display: none;
}
.grade-content.active {
    display: block;
}
</style>

<script>
function switchGrade(grade) {
    // Hide all grade content sections
    document.querySelectorAll('.grade-content').forEach(el => {
        el.classList.remove('active');
    });
    
    // Deactivate all tab buttons
    document.querySelectorAll('.grade-tab').forEach(el => {
        el.classList.remove('active');
    });
    
    // Show selected grade section
    const activeSection = document.getElementById('grade-' + grade + '-content');
    if (activeSection) {
        activeSection.classList.add('active');
    }
    
    // Activate clicked tab button
    event.currentTarget.classList.add('active');
    
    // Update hero action button URL
    const heroBtn = document.getElementById('hero-action-btn');
    if (heroBtn) {
        heroBtn.href = 'theory.php?grade=' + grade;
        heroBtn.textContent = 'Khám phá Lý thuyết ' + grade;
    }
}
</script>

<?php include 'includes/footer.php'; ?>