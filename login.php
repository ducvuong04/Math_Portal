<?php
require_once 'includes/functions.php';

if (is_logged_in())
    redirect('index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ];
        redirect('index.php');
    } else {
        $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
    }
}

include 'includes/header.php';
?>

<main class="container animate" style="max-width: 500px; padding: 6rem 0;">
    <div class="content-wrapper">
        <h2 style="text-align: center; margin-bottom: 2rem;">Đăng Nhập </h2>
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
            <button type="submit" class="btn btn-primary" style="width: 100%;">Vào học ngay</button>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>