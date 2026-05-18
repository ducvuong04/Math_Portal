<?php require_once __DIR__ . '/functions.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MathPortal 12 - Chinh Phục Kỳ Thi</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=1.4">
    <!-- Google Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <!-- KaTeX for Math Formulas -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js" onload="renderMathInElement(document.body, {delimiters: [{left: '$$', right: '$$', display: true}, {left: '$', right: '$', display: false}]});"></script>
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <script defer src="<?php echo BASE_URL; ?>assets/js/calculator-core.js"></script>
    <script defer src="<?php echo BASE_URL; ?>assets/js/floating-calc.js"></script>
</head>
<body>
    <nav>
        <div class="container nav-content">
            <a href="<?php echo BASE_URL; ?>index.php" class="nav-logo">
                <span class="material-icons-round">auto_awesome</span>
                Math<span>Portal</span>
            </a>
            <ul class="nav-links">
                <li><a href="<?php echo BASE_URL; ?>index.php">Trang chủ</a></li>
                <li class="has-dropdown">
                    <a href="#" class="dropdown-toggle" onclick="event.preventDefault();">Lý thuyết <span class="material-icons-round" style="font-size: 14px; transition: transform 0.3s; pointer-events: none;">expand_more</span></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo BASE_URL; ?>theory.php?grade=10">Toán lớp 10</a></li>
                        <li><a href="<?php echo BASE_URL; ?>theory.php?grade=11">Toán lớp 11</a></li>
                        <li><a href="<?php echo BASE_URL; ?>theory.php?grade=12">Toán lớp 12</a></li>
                    </ul>
                </li>
                <li><a href="<?php echo BASE_URL; ?>tools.php">Công cụ</a></li>
                <li class="has-dropdown">
                    <a href="#" class="dropdown-toggle" onclick="event.preventDefault();">Luyện tập <span class="material-icons-round" style="font-size: 14px; transition: transform 0.3s; pointer-events: none;">expand_more</span></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo BASE_URL; ?>quiz.php?grade=10">Toán lớp 10</a></li>
                        <li><a href="<?php echo BASE_URL; ?>quiz.php?grade=11">Toán lớp 11</a></li>
                        <li><a href="<?php echo BASE_URL; ?>quiz.php?grade=12">Toán lớp 12</a></li>
                    </ul>
                </li>
                <li class="has-dropdown">
                    <a href="#" class="dropdown-toggle" onclick="event.preventDefault();">Bài tập <span class="material-icons-round" style="font-size: 14px; transition: transform 0.3s; pointer-events: none;">expand_more</span></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo BASE_URL; ?>assignments.php?grade=10">Toán lớp 10</a></li>
                        <li><a href="<?php echo BASE_URL; ?>assignments.php?grade=11">Toán lớp 11</a></li>
                        <li><a href="<?php echo BASE_URL; ?>assignments.php?grade=12">Toán lớp 12</a></li>
                    </ul>
                </li>
                <li class="has-dropdown">
                    <a href="#" class="dropdown-toggle" onclick="event.preventDefault();">Đề thi <span class="material-icons-round" style="font-size: 14px; transition: transform 0.3s; pointer-events: none;">expand_more</span></a>
                    <ul class="dropdown-menu">
                        <li class="has-submenu">
                            <a href="<?php echo BASE_URL; ?>exams.php?grade=10">Toán lớp 10 &raquo;</a>
                            <ul class="submenu">
                                <li><a href="<?php echo BASE_URL; ?>exams.php?grade=10&tab=midterm">Đề thi giữa kì</a></li>
                                <li><a href="<?php echo BASE_URL; ?>exams.php?grade=10&tab=final">Đề thi cuối kì</a></li>
                            </ul>
                        </li>
                        <li class="has-submenu">
                            <a href="<?php echo BASE_URL; ?>exams.php?grade=11">Toán lớp 11 &raquo;</a>
                            <ul class="submenu">
                                <li><a href="<?php echo BASE_URL; ?>exams.php?grade=11&tab=midterm">Đề thi giữa kì</a></li>
                                <li><a href="<?php echo BASE_URL; ?>exams.php?grade=11&tab=final">Đề thi cuối kì</a></li>
                            </ul>
                        </li>
                        <li class="has-submenu">
                            <a href="<?php echo BASE_URL; ?>exams.php?grade=12">Toán lớp 12 &raquo;</a>
                            <ul class="submenu">
                                <li><a href="<?php echo BASE_URL; ?>exams.php?grade=12&tab=midterm">Đề thi giữa kì</a></li>
                                <li><a href="<?php echo BASE_URL; ?>exams.php?grade=12&tab=final">Đề thi cuối kì</a></li>
                            </ul>
                        </li>
                        <li class="has-submenu">
                            <a href="<?php echo BASE_URL; ?>exams.php?grade=thpt">Đề thi THPT &raquo;</a>
                            <ul class="submenu">
                                <li><a href="<?php echo BASE_URL; ?>exams.php?grade=thpt&tab=thpt_mock">Đề thi thử THPT</a></li>
                                <li><a href="<?php echo BASE_URL; ?>exams.php?grade=thpt&tab=thpt_official">Đề chính thức</a></li>
                                <li><a href="<?php echo BASE_URL; ?>exams.php?grade=thpt&tab=thpt_dgnl">Đánh giá năng lực</a></li>
                                <li><a href="<?php echo BASE_URL; ?>exams.php?grade=thpt&tab=thpt_prep">Ôn thi THPT</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li><a href="<?php echo BASE_URL; ?>calculator.php">Máy tính Casio</a></li>
                <?php if(is_teacher()): ?>
                    <li><a href="<?php echo BASE_URL; ?>admin/dashboard.php" style="color: var(--accent);">Quản trị</a></li>
                <?php endif; ?>
            </ul>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <?php if(is_logged_in()): ?>
                    <span style="font-size: 0.9rem; color: var(--text-muted);">Chào, <strong><?php echo $_SESSION['user']['username']; ?></strong></span>
                    <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-outline" style="padding: 0.5rem 1rem;">Đăng xuất</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-outline">Đăng nhập</a>
                    <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-primary">Đăng ký</a>
                <?php endif; ?>

                <!-- Theme Toggle -->
                <div class="theme-switch-wrapper" style="margin-right: 0; margin-left: 1rem;">
                    <label class="theme-switch" for="checkbox">
                        <input type="checkbox" id="checkbox" />
                        <div class="slider">
                            <span class="material-icons-round moon">dark_mode</span>
                            <span class="material-icons-round sun">light_mode</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </nav>

    <!-- Custom Modal System -->
    <style>
    /* Nested Submenu Styles */
    .has-submenu { position: relative; }
    .submenu {
        position: absolute; top: 0; left: 100%; min-width: 180px;
        background: var(--nav-bg); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border); border-radius: 12px;
        padding: 0.5rem 0; list-style: none; display: flex; flex-direction: column; gap: 0.2rem;
        box-shadow: var(--shadow); opacity: 0; visibility: hidden;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.1); transform: translateX(-10px); z-index: 1002;
    }
    .has-submenu:hover .submenu {
        opacity: 1; visibility: visible; transform: translateX(0);
    }
    .submenu a {
        padding: 0.6rem 1.2rem; display: block; font-size: 0.85rem; color: var(--text-muted);
        text-align: left; text-decoration: none; border-radius: 6px; margin: 0 0.4rem; transition: all 0.2s ease;
    }
    .submenu a:hover {
        color: var(--accent) !important; background: var(--glass-bg);
    }
    .has-submenu > a { display: flex; justify-content: space-between; align-items: center; }
    
    .custom-modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(5px);
        display: flex; align-items: center; justify-content: center;
        z-index: 9999; opacity: 0; visibility: hidden; transition: all 0.3s ease;
    }
    .custom-modal-overlay.active {
        opacity: 1; visibility: visible;
    }
    .custom-modal {
        background: var(--glass-bg); border: 1px solid var(--glass-border);
        border-radius: 15px; padding: 2rem; width: 90%; max-width: 400px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3); transform: translateY(-20px);
        transition: all 0.3s ease;
    }
    .custom-modal-overlay.active .custom-modal {
        transform: translateY(0);
    }
    .custom-modal-header { font-size: 1.2rem; font-weight: 600; margin-bottom: 1rem; color: var(--primary); }
    .custom-modal-body { margin-bottom: 1.5rem; color: var(--text); line-height: 1.5; }
    .custom-modal-footer { display: flex; justify-content: flex-end; gap: 1rem; }
    </style>
    
    <div class="custom-modal-overlay" id="custom-modal-overlay">
        <div class="custom-modal">
            <div class="custom-modal-header" id="custom-modal-title">Thông báo</div>
            <div class="custom-modal-body" id="custom-modal-message"></div>
            <div class="custom-modal-footer" id="custom-modal-footer"></div>
        </div>
    </div>

    <script>
    const CustomModal = {
        show: function(title, message, buttons) {
            return new Promise((resolve) => {
                document.getElementById('custom-modal-title').innerText = title;
                document.getElementById('custom-modal-message').innerHTML = message;
                const footer = document.getElementById('custom-modal-footer');
                footer.innerHTML = '';
                
                buttons.forEach(btn => {
                    const buttonEl = document.createElement('button');
                    buttonEl.className = 'btn ' + (btn.class || 'btn-outline');
                    buttonEl.innerText = btn.text;
                    buttonEl.onclick = () => {
                        this.hide();
                        resolve(btn.value);
                    };
                    footer.appendChild(buttonEl);
                });

                document.getElementById('custom-modal-overlay').classList.add('active');
            });
        },
        hide: function() {
            document.getElementById('custom-modal-overlay').classList.remove('active');
        },
        alert: function(message, title = 'Thông báo') {
            return this.show(title, message, [{ text: 'OK', class: 'btn-primary', value: true }]);
        },
        confirm: function(message, title = 'Xác nhận') {
            return this.show(title, message, [
                { text: 'Hủy', class: 'btn-outline', value: false },
                { text: 'Đồng ý', class: 'btn-primary', value: true }
            ]);
        }
    };
    </script>
