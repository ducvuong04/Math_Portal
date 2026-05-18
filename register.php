<?php
require_once 'includes/functions.php';

if (is_logged_in())
    redirect('index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, $role]);
        $user_id = $pdo->lastInsertId();
        $_SESSION['user'] = [
            'id' => $user_id,
            'username' => $username,
            'role' => $role
        ];
        redirect('index.php');
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "Tên đăng nhập đã tồn tại!";
        } else {
            $error = "Lỗi hệ thống: " . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<main class="container animate" style="max-width: 500px; padding: 6rem 0;">
    <div class="content-wrapper">
        <h2 style="text-align: center; margin-bottom: 2rem;">Đăng Ký Thành Viên </h2>
        <?php if ($error): ?>
            <p style="color: #ef4444; margin-bottom: 1rem; text-align: center;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">Tên đăng nhập</label>
                <input type="text" name="username" required class="btn btn-outline"
                    style="width: 100%; text-align: left; background: rgba(0,0,0,0.2);">
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">Mật khẩu</label>
                <input type="password" name="password" required class="btn btn-outline"
                    style="width: 100%; text-align: left; background: rgba(0,0,0,0.2);">
            </div>
            <div style="margin-bottom: 2rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">Vai trò</label>
                <select name="role" class="btn btn-outline"
                    style="width: 100%; text-align: left; background: rgba(0,0,0,0.2);">
                    <option value="student">Học sinh</option>
                    <option value="teacher">Giáo viên (Quản trị)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Đăng ký ngay</button>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>