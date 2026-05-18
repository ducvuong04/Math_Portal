<?php
require_once '../includes/functions.php';
if (!is_teacher()) redirect('../login.php');

$success = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM topics WHERE id = ?");
    $stmt->execute([$id]);
    $success = "Xóa lý thuyết thành công!";
}

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_topic'])) {
    $chapId = $_POST['chapter_id'];
    $stmt = $pdo->prepare("INSERT INTO topics (chapter_id, topic_id_str, title, description, theory, video_url) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $chapId,
        strtolower(str_replace(' ', '-', $_POST['title'])),
        $_POST['title'],
        $_POST['description'],
        $_POST['theory'],
        $_POST['video_url'] ?? ''
    ]);
    $topicId = $pdo->lastInsertId();
    
    $formulas = explode("\n", $_POST['formulas']);
    foreach ($formulas as $f) {
        if (trim($f)) {
            $stmt = $pdo->prepare("INSERT INTO formulas (topic_id, formula_text) VALUES (?, ?)");
            $stmt->execute([$topicId, trim($f)]);
        }
    }
    $success = "Thêm lý thuyết thành công!";
}

// Handle Save Video URL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_video'])) {
    $topicId = $_POST['update_video_topic_id'];
    $videoUrl = $_POST['video_url'];
    $stmt = $pdo->prepare("UPDATE topics SET video_url = ? WHERE id = ?");
    $stmt->execute([$videoUrl, $topicId]);
    $success = "Cập nhật video bài giảng thành công!";
}

$chapters = $pdo->query("SELECT * FROM chapters")->fetchAll();
$topics = $pdo->query("SELECT t.*, c.title as chapter_title, c.grade FROM topics t JOIN chapters c ON t.chapter_id = c.id ORDER BY c.grade ASC, c.id ASC, t.id ASC")->fetchAll();

include '../includes/header.php';
?>

<main class="container" style="padding: 4rem 0;">
    <a href="dashboard.php" style="color: var(--accent); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 2rem;">
        <span class="material-icons-round">arrow_back</span> Dashboard
    </a>

    <h1>Quản lý Lý thuyết</h1>

    <?php if($success): ?>
        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #10b981; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 3rem;">
        <section class="content-wrapper" style="margin: 0;">
            <h3>Thêm Bài Giảng</h3>
            <form method="POST">
                <div style="margin: 1rem 0;">
                    <label style="font-size: 0.8rem; color: var(--text-muted);">Chọn Chương</label>
                    <select name="chapter_id" class="btn btn-outline" style="width: 100%; text-align: left;">
                        <?php foreach($chapters as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo $c['title']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <input type="text" name="title" placeholder="Tiêu đề" required class="btn btn-outline" style="width: 100%; margin-bottom: 1rem;">
                <input type="text" name="description" placeholder="Mô tả" required class="btn btn-outline" style="width: 100%; margin-bottom: 1rem;">
                <input type="text" name="video_url" placeholder="Link video bài giảng (YouTube) - Không bắt buộc" class="btn btn-outline" style="width: 100%; margin-bottom: 1rem;">
                <textarea name="theory" placeholder="Nội dung" required class="btn btn-outline" style="width: 100%; min-height: 150px; margin-bottom: 1rem;"></textarea>
                <textarea name="formulas" placeholder="Công thức (mỗi dòng 1 cái)" required class="btn btn-outline" style="width: 100%; min-height: 100px; margin-bottom: 1rem;"></textarea>
                <button type="submit" name="add_topic" class="btn btn-primary" style="width: 100%;">Lưu bài giảng</button>
            </form>
        </section>

        <section>
            <h3>Danh Sách Bài Giảng</h3>
            
            <!-- Real-time Search and Grade Filters -->
            <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; align-items: center;">
                <div style="position: relative; flex-grow: 1; min-width: 250px;">
                    <span class="material-icons-round" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.2rem;">search</span>
                    <input type="text" id="theory-search" oninput="applyFilters()" placeholder="Tìm kiếm theo tên bài học hoặc chương..." class="btn btn-outline" style="width: 100%; text-align: left; padding-left: 2.8rem; background: rgba(0,0,0,0.25); height: 42px; border-radius: 8px;">
                </div>
                <div style="display: flex; gap: 0.4rem; background: rgba(255,255,255,0.03); padding: 0.3rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.08);" class="filter-pills">
                    <button onclick="filterGrade('all', this)" class="btn btn-primary filter-pill active" style="padding: 0.4rem 0.9rem; font-size: 0.82rem; height: 32px; border-radius: 6px;">Tất cả</button>
                    <button onclick="filterGrade('10', this)" class="btn btn-outline filter-pill" style="padding: 0.4rem 0.9rem; font-size: 0.82rem; height: 32px; border-radius: 6px; border-color: transparent; background: transparent;">Lớp 10</button>
                    <button onclick="filterGrade('11', this)" class="btn btn-outline filter-pill" style="padding: 0.4rem 0.9rem; font-size: 0.82rem; height: 32px; border-radius: 6px; border-color: transparent; background: transparent;">Lớp 11</button>
                    <button onclick="filterGrade('12', this)" class="btn btn-outline filter-pill" style="padding: 0.4rem 0.9rem; font-size: 0.82rem; height: 32px; border-radius: 6px; border-color: transparent; background: transparent;">Lớp 12</button>
                </div>
            </div>

            <!-- Theory Topics List -->
            <div id="theory-list">
                <?php if (empty($topics)): ?>
                    <p style="color: var(--text-muted); text-align: center; padding: 2rem;">Chưa có bài học nào.</p>
                <?php else: ?>
                    <?php foreach($topics as $t): ?>
                        <div class="topic-group-item" data-grade="<?php echo $t['grade']; ?>" data-title="<?php echo htmlspecialchars(strtolower($t['title'])); ?>" data-chapter="<?php echo htmlspecialchars(strtolower($t['chapter_title'])); ?>" style="margin-bottom: 1rem;">
                            <div class="content-wrapper" style="padding: 1.2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                                <div>
                                    <small style="color: var(--accent);"><?php echo $t['chapter_title']; ?></small>
                                    <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; margin: 0.2rem 0;">
                                        <h4 style="margin: 0;"><?php echo $t['title']; ?></h4>
                                        <span style="background: rgba(249, 115, 22, 0.15); color: #f97316; font-size: 0.72rem; font-weight: 600; padding: 0.15rem 0.45rem; border-radius: 6px; letter-spacing: 0.02em;">Lớp <?php echo $t['grade']; ?></span>
                                    </div>
                                    <?php if(!empty($t['video_url'])): ?>
                                        <small style="color: #10b981; display: inline-flex; align-items: center; gap: 0.25rem; font-weight: 500;">
                                            <span class="material-icons-round" style="font-size: 0.95rem;">videocam</span> Có video bài giảng
                                        </small>
                                    <?php else: ?>
                                        <small style="color: var(--text-muted); display: inline-flex; align-items: center; gap: 0.25rem;">
                                            <span class="material-icons-round" style="font-size: 0.95rem;">videocam_off</span> Chưa có video
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <button onclick="toggleVideoEdit(<?php echo $t['id']; ?>)" class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; display: flex; align-items: center; gap: 0.25rem; border-color: rgba(255,255,255,0.15); background: transparent;">
                                        <span class="material-icons-round" style="font-size: 1rem;">edit</span> Video
                                    </button>
                                    <a href="<?php echo BASE_URL; ?>admin/manage_theory.php?delete=<?php echo $t['id']; ?>" onclick="return confirm('Xóa?')" style="color: #ef4444; display: inline-flex; align-items: center; justify-content: center; width: 35px; height: 35px; border-radius: 50%; transition: background 0.3s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.1)'" onmouseout="this.style.background='transparent'">
                                        <span class="material-icons-round">delete</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Hidden Inline Video Edit Form -->
                            <div id="video-edit-<?php echo $t['id']; ?>" style="display: none; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 8px; padding: 1.2rem; margin-top: 0.5rem;">
                                <form method="POST">
                                    <input type="hidden" name="update_video_topic_id" value="<?php echo $t['id']; ?>">
                                    <label style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">Cập nhật link video YouTube:</label>
                                    <div style="display: flex; gap: 0.75rem;">
                                        <input type="text" name="video_url" value="<?php echo htmlspecialchars($t['video_url'] ?? ''); ?>" placeholder="Dán link video YouTube (ví dụ: https://www.youtube.com/watch?v=...)" class="btn btn-outline" style="flex-grow: 1; text-align: left; background: rgba(0,0,0,0.25); font-size: 0.85rem; padding: 0.5rem 1rem;">
                                        <button type="submit" name="save_video" class="btn btn-primary" style="padding: 0.5rem 1.2rem; font-size: 0.85rem;">Lưu</button>
                                        <button type="button" onclick="toggleVideoEdit(<?php echo $t['id']; ?>)" class="btn btn-outline" style="padding: 0.5rem 1.2rem; font-size: 0.85rem; border-color: transparent;">Hủy</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<script>
let currentSelectedGrade = 'all';

function toggleVideoEdit(id) {
    var el = document.getElementById('video-edit-' + id);
    if (el.style.display === 'none') {
        el.style.display = 'block';
    } else {
        el.style.display = 'none';
    }
}

function filterGrade(grade, btn) {
    currentSelectedGrade = grade;
    
    // Update active button styles
    document.querySelectorAll('.filter-pill').forEach(function(b) {
        b.classList.remove('btn-primary', 'active');
        b.classList.add('btn-outline');
        b.style.background = 'transparent';
        b.style.borderColor = 'transparent';
    });
    
    btn.classList.remove('btn-outline');
    btn.classList.add('btn-primary', 'active');
    btn.style.background = 'var(--primary)';
    btn.style.borderColor = 'var(--primary)';
    
    applyFilters();
}

function applyFilters() {
    const searchQuery = document.getElementById('theory-search').value.toLowerCase().trim();
    const items = document.querySelectorAll('.topic-group-item');
    
    items.forEach(function(item) {
        const itemGrade = item.getAttribute('data-grade');
        const itemTitle = item.getAttribute('data-title');
        const itemChapter = item.getAttribute('data-chapter');
        
        // Check grade filter
        const matchesGrade = (currentSelectedGrade === 'all' || itemGrade === currentSelectedGrade);
        
        // Check search filter
        const matchesSearch = (searchQuery === '' || itemTitle.includes(searchQuery) || itemChapter.includes(searchQuery));
        
        // Show or hide element
        if (matchesGrade && matchesSearch) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
