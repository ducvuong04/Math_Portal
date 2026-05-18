<?php
require_once '../includes/functions.php';
if (!is_teacher()) redirect('../login.php');

$success = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM tools WHERE id = ?");
    $stmt->execute([$id]);
    $success = "Xóa công cụ thành công!";
}

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tool'])) {
    $stmt = $pdo->prepare("INSERT INTO tools (name, description, link, icon) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'],
        $_POST['description'],
        $_POST['link'],
        $_POST['icon']
    ]);
    $success = "Thêm công cụ mới thành công!";
}

$tools = $pdo->query("SELECT * FROM tools")->fetchAll();

include '../includes/header.php';
?>

<main class="container" style="padding: 4rem 0;">
    <a href="dashboard.php" style="color: var(--accent); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 2rem;">
        <span class="material-icons-round">arrow_back</span> Dashboard
    </a>

    <h1>Quản lý Công cụ Giải toán</h1>

    <?php if($success): ?>
        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #10b981; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
        <section class="content-wrapper" style="margin: 0;">
            <h3>Thêm Công Cụ</h3>
            <form method="POST">
                <div style="margin: 1rem 0;">
                    <label style="font-size: 0.8rem; color: var(--text-muted);">Tên công cụ</label>
                    <input type="text" name="name" required class="btn btn-outline" style="width: 100%; text-align: left;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="font-size: 0.8rem; color: var(--text-muted);">Mô tả</label>
                    <textarea name="description" required class="btn btn-outline" style="width: 100%; text-align: left; min-height: 80px;"></textarea>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="font-size: 0.8rem; color: var(--text-muted);">Đường dẫn (URL)</label>
                    <input type="text" name="link" required class="btn btn-outline" style="width: 100%; text-align: left;" placeholder="tools.php#id">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="font-size: 0.8rem; color: var(--text-muted);">Icon (Material Icons name)</label>
                    <input type="text" name="icon" required class="btn btn-outline" style="width: 100%; text-align: left;" placeholder="calculate">
                </div>
                <button type="submit" name="add_tool" class="btn btn-primary" style="width: 100%;">Lưu công cụ</button>
            </form>
        </section>

        <section>
            <h3>Danh Sách Công Cụ</h3>
            <?php foreach($tools as $t): ?>
                <div class="content-wrapper" style="padding: 1.5rem; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 0.5rem;">
                            <span class="material-icons-round" style="color: var(--accent);"><?php echo $t['icon']; ?></span>
                            <strong style="font-size: 1.1rem;"><?php echo $t['name']; ?></strong>
                        </div>
                        <p style="font-size: 0.8rem; color: var(--text-muted);"><?php echo $t['description']; ?></p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>admin/manage_tools.php?delete=<?php echo $t['id']; ?>" onclick="return confirm('Xóa?')" style="color: #ef4444; display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; transition: background 0.3s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.1)'" onmouseout="this.style.background='transparent'">
                        <span class="material-icons-round">delete</span>
                    </a>
                </div>
            <?php endforeach; ?>
        </section>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
