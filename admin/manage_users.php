<?php
require_once '../includes/functions.php';
if (!is_teacher()) redirect('../login.php');

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

include '../includes/header.php';
?>

<main class="container" style="padding: 4rem 0;">
    <a href="dashboard.php" style="color: var(--accent); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 2rem;">
        <span class="material-icons-round">arrow_back</span> Dashboard
    </a>

    <h1>Hồ Sơ Đăng Nhập & Thành Viên</h1>
    <p style="color: var(--text-muted); margin-bottom: 3rem;">Danh sách toàn bộ người dùng đã đăng ký trên hệ thống.</p>

    <div class="content-wrapper" style="padding: 0; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: rgba(255,255,255,0.05); border-bottom: 1px solid var(--glass-border);">
                    <th style="padding: 1.5rem;">ID</th>
                    <th style="padding: 1.5rem;">Tên đăng nhập</th>
                    <th style="padding: 1.5rem;">Vai trò</th>
                    <th style="padding: 1.5rem;">Ngày tham gia</th>
                    <th style="padding: 1.5rem;">Trạng thái</th>
                    <th style="padding: 1.5rem; text-align: right;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                    <tr style="border-bottom: 1px solid var(--glass-border); transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 1.5rem; color: var(--text-muted);"><?php echo $u['id']; ?></td>
                        <td style="padding: 1.5rem; font-weight: 600;"><?php echo $u['username']; ?></td>
                        <td style="padding: 1.5rem;">
                            <span style="background: <?php echo ($u['role'] === 'teacher' ? 'rgba(245, 158, 11, 0.1)' : 'rgba(79, 70, 229, 0.1)'); ?>; 
                                         color: <?php echo ($u['role'] === 'teacher' ? 'var(--accent)' : 'var(--primary-light)'); ?>; 
                                         padding: 0.2rem 0.8rem; border-radius: 20px; font-size: 0.8rem;">
                                <?php echo ($u['role'] === 'teacher' ? 'Giáo viên' : 'Học sinh'); ?>
                            </span>
                        </td>
                        <td style="padding: 1.5rem; color: var(--text-muted); font-size: 0.9rem;"><?php echo $u['created_at']; ?></td>
                        <td style="padding: 1.5rem;">
                            <span style="color: #10b981; display: flex; align-items: center; gap: 0.3rem; font-size: 0.9rem;">
                                <span style="width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></span> Đang hoạt động
                            </span>
                        </td>
                        <td style="padding: 1.5rem; text-align: right;">
                            <?php if ($u['role'] === 'student'): ?>
                                <a href="user_profile.php?id=<?php echo $u['id']; ?>" class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.3rem; border-color: rgba(255,255,255,0.1); border-radius: 20px;">
                                    <span class="material-icons-round" style="font-size: 1.1rem; color: var(--accent);">visibility</span> Xem hồ sơ
                                </a>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 0.85rem;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
