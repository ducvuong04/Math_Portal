-- --------------------------------------------------------
-- math_portal_12.sql
-- Full detailed curriculum from Vietjack
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `math_portal_12` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `math_portal_12`;

DROP TABLE IF EXISTS `formulas`;
DROP TABLE IF EXISTS `topics`;
DROP TABLE IF EXISTS `chapters`;
DROP TABLE IF EXISTS `quizzes`;
DROP TABLE IF EXISTS `tools`;
DROP TABLE IF EXISTS `users`;

-- Table structure for table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('teacher','student') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`username`, `password`, `role`) VALUES
('teacher', '$2y$10$mvqzt5ggb/Bda735835rWOeUwv1zILqNdcXkrmEVdK7KKmZyyAnfe', 'teacher'),
('student', '$2y$10$A70LId.la2stdasURIku5epQlnKhlUO88IP.KZ9fGpV.PJmNi.0pK', 'student');

-- Table structure for table `chapters`
CREATE TABLE `chapters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chapter_key` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `icon` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chapter_key` (`chapter_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `chapters` (`id`, `chapter_key`, `title`, `icon`) VALUES
(1, 'calculus_1', 'Chương 1: Ứng dụng đạo hàm để khảo sát hàm số', 'functions'),
(2, 'geometry_1', 'Chương 2: Vectơ và tọa độ trong không gian', 'architecture'),
(3, 'statistics', 'Chương 3: Các số đặc trưng đo mức độ phân tán', 'bar_chart'),
(4, 'calculus_2', 'Chương 4: Nguyên hàm và tích phân', 'calculate'),
(5, 'geometry_2', 'Chương 5: Phương pháp tọa độ trong không gian', 'explore'),
(6, 'probability', 'Chương 6: Xác suất có điều kiện', 'auto_graph');

-- Table structure for table `topics`
CREATE TABLE `topics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chapter_id` int(11) NOT NULL,
  `topic_id_str` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `theory` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chapter_id` (`chapter_id`),
  CONSTRAINT `topics_ibfk_1` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `topics` (`id`, `chapter_id`, `topic_id_str`, `title`, `description`, `theory`) VALUES
-- Chapter 1
(1, 1, 'don-dieu-cuc-tri', 'Tính đơn điệu và cực trị', 'Khảo sát chiều biến thiên và các điểm cực trị.', '### 1. Tính đơn điệu\n* **Định nghĩa:** Hàm số $f(x)$ đồng biến trên $K$ nếu $\\forall x_1 < x_2 \\in K \\Rightarrow f(x_1) < f(x_2)$. Nghịch biến nếu $f(x_1) > f(x_2)$.\n* **Điều kiện đủ:** Nếu $f\'(x) > 0$ trên $K$ thì hàm số đồng biến. Nếu $f\'(x) < 0$ trên $K$ thì hàm số nghịch biến.\n\n### 2. Cực trị\n* **Định nghĩa:** $x_0$ là điểm cực đại nếu $f(x) < f(x_0)$ trong lân cận $x_0$. Điểm cực tiểu nếu $f(x) > f(x_0)$.\n* **Cách tìm:** Lập bảng biến thiên. Nếu $f\'(x)$ đổi dấu từ $(+)$ sang $(-)$ qua $x_0$ thì $x_0$ là cực đại. Ngược lại là cực tiểu.'),
(2, 1, 'max-min', 'Giá trị lớn nhất và nhỏ nhất', 'Tìm GTLN và GTNN trên đoạn [a, b].', '### 1. Định nghĩa\n* $M = \\max_D f(x)$ nếu $f(x) \\le M, \\forall x \\in D$.\n* $m = \\min_D f(x)$ nếu $f(x) \\ge m, \\forall x \\in D$.\n\n### 2. Cách tìm trên đoạn [a, b]\n1. Tính $f\'(x)$, tìm nghiệm $x_i \\in (a, b)$.\n2. Tính $f(a), f(b)$ và $f(x_i)$.\n3. Chọn số lớn nhất là GTLN, nhỏ nhất là GTNN.'),
(3, 1, 'tiem-can', 'Đường tiệm cận', 'Tiệm cận đứng, ngang và xiên.', '### 1. Tiệm cận ngang\n$y = y_0$ nếu $\\lim_{x \\to \\pm\\infty} f(x) = y_0$.\n\n### 2. Tiệm cận đứng\n$x = x_0$ nếu $\\lim_{x \\to x_0} f(x) = \\pm\\infty$.\n\n### 3. Tiệm cận xiên\n$y = ax + b$ ($a \\ne 0$) nếu $\\lim_{x \\to \\pm\\infty} [f(x) - (ax+b)] = 0$.'),
(4, 1, 'khao-sat-do-thi', 'Khảo sát và vẽ đồ thị hàm số', 'Quy trình 3 bước khảo sát hàm số.', '1. Tìm tập xác định.\n2. Khảo sát biến thiên (đạo hàm, cực trị, tiệm cận, bảng biến thiên).\n3. Vẽ đồ thị (điểm đặc biệt, tâm đối xứng, trục đối xứng).'),

-- Chapter 2
(5, 2, 'vector-3d', 'Vectơ trong không gian', 'Định nghĩa và phép toán vectơ.', '### 1. Định nghĩa\nVectơ trong không gian là một đoạn thẳng có hướng. Các khái niệm độ dài, cùng phương, cùng hướng, bằng nhau tương tự như trong mặt phẳng.\n\n### 2. Phép toán\n* Quy tắc hình hộp: $\\vec{AC\'} = \\vec{AB} + \\vec{AD} + \\vec{AA\'}$.\n* Tích vô hướng: $\\vec{a}\\cdot\\vec{b} = |\\vec{a}|\\cdot|\\vec{b}|\\cdot\\cos(\\vec{a}, \\vec{b})$.'),
(6, 2, 'he-toa-do-3d', 'Hệ tọa độ Oxyz', 'Hệ trục tọa độ và tọa độ vectơ.', '### 1. Hệ tọa độ Oxyz\nBa trục Ox, Oy, Oz vuông góc từng đôi một tại O. Các vectơ đơn vị là $\\vec{i}, \\vec{j}, \\vec{k}$.\n\n### 2. Tọa độ vectơ\n$\\vec{u} = x\\vec{i} + y\\vec{j} + z\\vec{k} \\Leftrightarrow \\vec{u}=(x; y; z)$.'),
(7, 2, 'bieu-thuc-toa-do', 'Biểu thức tọa độ phép toán', 'Các công thức tính toán tọa độ.', 'Cho $\\vec{a}=(a_1, a_2, a_3)$ và $\\vec{b}=(b_1, b_2, b_3)$:\n* $\\vec{a} \\pm \\vec{b} = (a_1 \\pm b_1, a_2 \\pm b_2, a_3 \\pm b_3)$.\n* $k\\vec{a} = (ka_1, ka_2, ka_3)$.\n* $\\vec{a}\\cdot\\vec{b} = a_1b_1 + a_2b_2 + a_3b_3$.'),

-- Chapter 3
(8, 3, 'khoang-bien-thien', 'Khoảng biến thiên và tứ phân vị', 'Đo độ phân tán của mẫu số liệu ghép nhóm.', '### 1. Khoảng biến thiên (R)\n$R = a_{k+1} - a_1$ (Đầu mút phải nhóm cuối trừ đầu mút trái nhóm đầu).\n\n### 2. Khoảng tứ phân vị ($\\Delta_Q$)\n$\\Delta_Q = Q_3 - Q_1$. Số liệu càng phân tán thì $\\Delta_Q$ càng lớn.'),
(9, 3, 'phuong-sai-do-lech', 'Phương sai và độ lệch chuẩn', 'Công thức tính phương sai mẫu ghép nhóm.', '### 1. Phương sai ($s^2$)\n$s^2 = \\frac{1}{n} \\sum m_i(x_i - \\bar{x})^2$.\nTrong đó $x_i$ là giá trị đại diện của nhóm.\n\n### 2. Độ lệch chuẩn (s)\n$s = \\sqrt{s^2}$. Dùng để đo mức độ phân tán quanh số trung bình.'),

-- Chapter 4
(10, 4, 'nguyen-ham', 'Nguyên hàm', 'Họ các nguyên hàm và tính chất.', '### 1. Định nghĩa\n$F(x)$ là nguyên hàm của $f(x)$ nếu $F\'(x) = f(x)$.\nHọ nguyên hàm: $\\int f(x) dx = F(x) + C$.\n\n### 2. Tính chất\n* $\\int kf(x) dx = k \\int f(x) dx$.\n* $\\int [f(x) \\pm g(x)] dx = \\int f(x) dx \\pm \\int g(x) dx$.'),
(11, 4, 'tich-phan', 'Tích phân', 'Công thức Newton-Leibniz và tính chất.', '### 1. Định nghĩa\n$\\int_a^b f(x) dx = F(b) - F(a)$.\n\n### 2. Tính chất\n* $\\int_a^b f(x) dx = -\\int_b^a f(x) dx$.\n* $\\int_a^b f(x) dx = \\int_a^c f(x) dx + \\int_c^b f(x) dx$.'),
(12, 4, 'ung-dung-tich-phan', 'Ứng dụng tích phân', 'Tính diện tích hình phẳng và thể tích vật thể.', '### 1. Diện tích (S)\n$S = \\int_a^b |f(x) - g(x)| dx$.\n\n### 2. Thể tích (V)\nVật thể quay quanh Ox: $V = \\pi \\int_a^b f^2(x) dx$.'),

-- Chapter 5
(13, 5, 'phuong-trinh-mat-phang', 'Phương trình mặt phẳng', 'Vectơ pháp tuyến và phương trình tổng quát.', '### 1. Vectơ pháp tuyến\n$\\vec{n}$ vuông góc với mặt phẳng $(P)$.\n\n### 2. Phương trình\nMặt phẳng qua $M_0(x_0, y_0, z_0)$ có VTPT $\\vec{n}=(A, B, C)$:\n$A(x-x_0) + B(y-y_0) + C(z-z_0) = 0 \\Leftrightarrow Ax + By + Cz + D = 0$.'),
(14, 5, 'phuong-trinh-duong-thang', 'Phương trình đường thẳng', 'Vectơ chỉ phương, PT tham số và chính tắc.', '### 1. Phương trình tham số\n$x = x_0 + at, y = y_0 + bt, z = z_0 + ct$.\n\n### 2. Phương trình chính tắc\n$\\frac{x-x_0}{a} = \\frac{y-y_0}{b} = \\frac{z-z_0}{c}$ ($abc \\ne 0$).'),
(15, 5, 'phuong-trinh-mat-cau', 'Phương trình mặt cầu', 'Dạng chính tắc và dạng tổng quát.', '### 1. Dạng chính tắc\n$(x-a)^2 + (y-b)^2 + (z-c)^2 = R^2$.\n\n### 2. Dạng tổng quát\n$x^2 + y^2 + z^2 - 2ax - 2by - 2cz + d = 0$.\nĐiều kiện: $a^2 + b^2 + c^2 - d > 0$ và $R = \\sqrt{a^2+b^2+c^2-d}$.'),

-- Chapter 6
(16, 6, 'xac-suat-dieu-kien', 'Xác suất có điều kiện', 'Định nghĩa và tính chất xác suất có điều kiện.', '### 1. Định nghĩa\n$P(A|B) = \\frac{P(A \\cap B)}{P(B)}$ (với $P(B) > 0$).\n\n### 2. Công thức nhân\n$P(A \\cap B) = P(B)\\cdot P(A|B) = P(A)\\cdot P(B|A)$.'),
(17, 6, 'bayes', 'Xác suất toàn phần và Bayes', 'Các công thức tính xác suất nâng cao.', '### 1. Xác suất toàn phần\n$P(B) = P(A)\\cdot P(B|A) + P(\\bar{A})\\cdot P(B|\\bar{A})$.\n\n### 2. Công thức Bayes\n$P(A|B) = \\frac{P(A)\\cdot P(B|A)}{P(B)}$.');

-- Table structure for table `formulas`
CREATE TABLE `formulas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) NOT NULL,
  `formula_text` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `topic_id` (`topic_id`),
  CONSTRAINT `formulas_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `formulas` (`topic_id`, `formula_text`) VALUES
(1, 'Đồng biến: $f\'(x) \\ge 0$. Nghịch biến: $f\'(x) \\le 0$.'),
(2, 'Max: $M = \\max \\{f(a), f(b), f(x_i)\\}$.'),
(3, 'TCN: $y = \\lim_{x \\to \\pm\\infty} f(x)$.'),
(5, '$\\vec{a}\\cdot\\vec{b} = |\\vec{a}|\\cdot|\\vec{b}|\\cos(\\theta)$.'),
(7, '$|\\vec{u}| = \\sqrt{x^2+y^2+z^2}$.'),
(10, '$\\int x^n dx = \\frac{x^{n+1}}{n+1} + C$.'),
(11, '$\\int_a^b f(x)dx = F(b) - F(a)$.'),
(13, '$Ax + By + Cz + D = 0$.'),
(15, '$R = \\sqrt{a^2+b^2+c^2-d}$.'),
(16, '$P(A|B) = \\frac{P(A \\cap B)}{P(B)}$.');

-- Table structure for table `quizzes`
CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` text NOT NULL,
  `opt_a` varchar(255) NOT NULL,
  `opt_b` varchar(255) NOT NULL,
  `opt_c` varchar(255) NOT NULL,
  `opt_d` varchar(255) NOT NULL,
  `answer` int(11) NOT NULL COMMENT '0:A, 1:B, 2:C, 3:D',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `quizzes` (`question`, `opt_a`, `opt_b`, `opt_c`, `opt_d`, `answer`) VALUES
('Hàm số $y=f(x)$ đồng biến trên K khi nào?', 'f\'(x) > 0', 'f\'(x) < 0', 'f\'(x) = 0', 'f(x) = 0', 0),
('Tiệm cận ngang của đồ thị hàm số $y=\\frac{x+1}{x-1}$ là?', 'y=1', 'y=-1', 'x=1', 'x=-1', 0),
('Nguyên hàm của $f(x)=x$ là?', 'x^2/2 + C', 'x^2 + C', '1 + C', '2x + C', 0);

-- Table structure for table `tools`
CREATE TABLE `tools` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `link` varchar(255) NOT NULL,
  `icon` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tools` (`name`, `description`, `link`, `icon`) VALUES
('Máy tính Casio', 'Giả lập máy tính bỏ túi online.', 'https://www.desmos.com/scientific', 'calculate'),
('Vẽ Đồ Thị', 'Công cụ vẽ đồ thị hàm số 2D/3D.', 'https://www.geogebra.org/graphing', 'show_chart'),
('Giải Bài Tập', 'Giải toán thông minh với AI.', 'https://www.wolframalpha.com/', 'psychology');
