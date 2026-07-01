================================================================================
      XÂY DỰNG HỆ THỐNG HỖ TRỢ HỌC TOÁN THPT - MathPortal
      Môn: Lập trình Web  |  Nền tảng: PHP + MySQL (XAMPP)
================================================================================

I. GIỚI THIỆU DỰ ÁN
--------------------
MathPortal là một cổng học tập Toán THPT trực tuyến (E-Learning), hỗ trợ đầy
đủ chương trình Toán lớp 10, 11 và 12. Hệ thống cung cấp các tính năng:

  - Xem lý thuyết theo chương/bài với công thức toán LaTeX (KaTeX)
  - Luyện tập trắc nghiệm (quiz) theo từng lớp
  - Kho bài tập (giao bài qua file Word .docx, học sinh nộp bài online)
  - Kho đề thi (giữa kỳ, cuối kỳ, THPT Quốc gia) dạng PDF với chức năng
    xem trước và tải về
  - Giả lập máy tính Casio fx-580VN X trực tuyến (tích hợp ngay trên web)
  - Công cụ học tập bên ngoài (vẽ đồ thị, giải toán AI, ...)
  - Phân quyền hệ thống: Giáo viên (Teacher) và Học sinh (Student)
  - Giao diện hỗ trợ Dark Mode / Light Mode

================================================================================
II. PHÂN HỆ CHỨC NĂNG
================================================================================

[ PHÍA HỌC SINH - STUDENT ]
  - Đăng ký / Đăng nhập tài khoản
  - Xem lý thuyết theo lớp (10/11/12) và theo chương, bài
    (nội dung có hỗ trợ render công thức LaTeX với KaTeX)
  - Luyện quiz trắc nghiệm (theo lớp, theo chương)
  - Xem và làm bài tập do giáo viên giao (gồm bài tập thường và bài tập
    Word quiz - đọc câu hỏi trực tiếp từ file .docx)
  - Nộp bài tập online, theo dõi trạng thái: Đang chờ / Đã nộp / Quá hạn
  - Xem kho đề thi theo lớp: đề giữa kỳ, cuối kỳ, đề thi thử THPT,
    đề THPT chính thức, Đánh giá năng lực, Tài liệu ôn thi
  - Tải đề thi và đáp án (PDF/DOCX) với tên file gốc
  - Sử dụng máy tính Casio ảo, xem công cụ học tập trực tuyến

[ PHÍA GIÁO VIÊN - TEACHER ]
  - Đăng nhập và truy cập Bảng Quản Trị (Admin Dashboard)
  - Quản lý lý thuyết: thêm/sửa/xóa chương, bài học, nội dung lý thuyết
    và công thức cho các lớp 10, 11, 12
  - Quản lý câu hỏi quiz: thêm/sửa/xóa ngân hàng câu hỏi trắc nghiệm
    theo từng chủ đề
  - Quản lý đề thi: upload đề thi và đáp án (PDF/DOCX), phân loại theo
    khối lớp và danh mục (giữa kỳ, cuối kỳ, THPT, ...)
  - Quản lý bài tập: tạo bài tập giao cho học sinh theo lớp và hạn nộp,
    hỗ trợ upload file Word (.docx) làm đề bài
  - Xem bài nộp của học sinh (view_submissions)
  - Quản lý công cụ học tập: thêm/sửa/xóa liên kết công cụ ngoài
  - Quản lý người dùng: xem danh sách thành viên, hồ sơ học sinh

================================================================================
III. CÔNG NGHỆ SỬ DỤNG
================================================================================

  Backend    : PHP (không framework, kiến trúc thủ tục)
  Database   : MySQL / MariaDB  (qua PDO)
  Web Server : Apache (XAMPP)
  Frontend   : HTML5, CSS3 (Vanilla), JavaScript (Vanilla)
  UI Icons   : Google Material Icons Round
  Math Render: KaTeX (render công thức LaTeX $...$ và $$...$$)
  Word Parser: PHPWord (Composer) — đọc file .docx làm đề bài
  Fonts      : Google Fonts (mặc định hệ thống)
  Theme      : Hỗ trợ Dark Mode / Light Mode (lưu trong localStorage)

================================================================================
IV. CẤU TRÚC THƯ MỤC
================================================================================

  /
  ├── index.php                -> Trang chủ (danh sách chương theo lớp 10/11/12)
  ├── login.php                -> Trang đăng nhập
  ├── register.php             -> Trang đăng ký tài khoản
  ├── logout.php               -> Xử lý đăng xuất (xóa session)
  ├── theory.php               -> Trang xem lý thuyết (theo lớp, chương, bài)
  ├── quiz.php                 -> Trang luyện trắc nghiệm
  ├── exams.php                -> Kho đề thi (10/11/12/THPT Quốc Gia)
  ├── assignments.php          -> Danh sách bài tập theo lớp
  ├── assignment_details.php   -> Chi tiết bài tập (làm bài Word quiz online)
  ├── tools.php                -> Trang công cụ học tập trực tuyến
  ├── calculator.php           -> Giả lập máy tính Casio fx-580VN X
  ├── download.php             -> Handler tải file (giữ đúng tên file gốc)
  ├── record_score.php         -> Lưu điểm quiz của học sinh
  ├── setup_db.php             -> File cấu hình kết nối cơ sở dữ liệu
  │
  ├── admin/                   -> Khu vực quản trị (chỉ dành cho Teacher)
  │   ├── dashboard.php            -> Trang tổng quan quản trị
  │   ├── manage_theory.php        -> Quản lý lý thuyết
  │   ├── manage_quiz.php          -> Quản lý câu hỏi trắc nghiệm
  │   ├── manage_exams.php         -> Quản lý kho đề thi
  │   ├── manage_assignments.php   -> Quản lý bài tập
  │   ├── manage_tools.php         -> Quản lý công cụ học tập
  │   ├── manage_users.php         -> Quản lý tài khoản người dùng
  │   ├── user_profile.php         -> Hồ sơ học sinh & lịch sử
  │   ├── view_submissions.php     -> Xem bài nộp của học sinh
  │   ├── get_assignment_questions.php  -> API lấy câu hỏi bài tập
  │   └── get_submission_details.php    -> API lấy chi tiết bài nộp
  │
  ├── includes/                -> Các file dùng chung toàn hệ thống
  │   ├── db.php                   -> Kết nối CSDL (PDO)
  │   ├── functions.php            -> Hàm tiện ích (auth, query, ...)
  │   ├── header.php               -> HTML Header, thanh điều hướng (navbar)
  │   ├── footer.php               -> HTML Footer
  │   ├── data.php                 -> Dữ liệu tĩnh hỗ trợ
  │   └── WordQuizImporter.php     -> Lớp parse file .docx thành câu hỏi quiz
  │
  ├── data/
  │   └── math_portal_12.sql   -> Script SQL khởi tạo toàn bộ CSDL
  │
  ├── assets/                  -> Tài nguyên tĩnh (CSS, JS, hình ảnh)
  │   ├── css/style.css            -> File CSS chính toàn trang
  │   └── js/                      -> Các file JavaScript (calculator, ...)
  │
  ├── uploads/                 -> Thư mục lưu file upload (đề thi, bài tập)
  ├── vendor/                  -> Thư viện PHP bên thứ ba (PHPWord via Composer)
  ├── composer.json            -> Khai báo dependency Composer
  └── .gitignore               -> Cấu hình Git bỏ qua file không cần track

================================================================================
V. CƠ SỞ DỮ LIỆU
================================================================================

  Tên CSDL  : math_portal_12
  File SQL  : data/math_portal_12.sql
  Encoding  : utf8mb4 (hỗ trợ tiếng Việt và ký tự đặc biệt, LaTeX)

  Các bảng chính:
  ┌────────────────────────┬────────────────────────────────────────────────┐
  │ Bảng                   │ Mô tả                                          │
  ├────────────────────────┼────────────────────────────────────────────────┤
  │ users                  │ Tài khoản (id, username, password, role, ...)  │
  │ chapters               │ Chương học (grade, chapter_key, title, icon)   │
  │ topics                 │ Bài học trong chương (title, theory, video_url)│
  │ formulas               │ Công thức liên kết với từng bài học            │
  │ quizzes                │ Câu hỏi trắc nghiệm (4 đáp án A/B/C/D)        │
  │ quiz_sets              │ Bộ đề trắc nghiệm theo chủ đề                 │
  │ quiz_attempts          │ Lịch sử làm quiz của học sinh                  │
  │ exams                  │ Kho đề thi (file đề, file đáp án, loại, lớp)  │
  │ assignments            │ Bài tập do giáo viên giao (deadline, file Word)│
  │ assignment_submissions │ Bài nộp của học sinh                           │
  │ tools                  │ Công cụ học tập trực tuyến (tên, link, icon)   │
  └────────────────────────┴────────────────────────────────────────────────┘

================================================================================
VI. HƯỚNG DẪN CÀI ĐẶT VÀ CHẠY LOCAL
================================================================================

YÊU CẦU:
  - XAMPP (Apache + MySQL + PHP 7.4 trở lên)
  - Composer (nếu cần cài lại thư viện PHPWord)
  - Trình duyệt hiện đại (Chrome, Firefox, Edge)

CÁC BƯỚC THỰC HIỆN:

  Bước 1. Sao chép thư mục dự án vào:
            C:\xampp\htdocs\

  Bước 2. Khởi động XAMPP Control Panel:
            - Bật Apache
            - Bật MySQL

  Bước 3. Import CSDL:
            - Mở: http://localhost/phpmyadmin
            - Chọn tab "Import"
            - Chọn file: data/math_portal_12.sql
            - Nhấn "Go" để import

  Bước 4. (Nếu chưa có thư mục vendor/) Cài thư viện PHP:
            Mở Command Prompt tại thư mục dự án, chạy:
              php composer.phar install

  Bước 5. Truy cập website:
            http://localhost/<tên-thư-mục-dự-án>/
            Ví dụ: http://localhost/bài thực hành 4.2/

================================================================================
VII. TÀI KHOẢN MẶC ĐỊNH
================================================================================

  Vai trò    │ Tên đăng nhập │ Mật khẩu
  ───────────┼───────────────┼──────────
  Giáo viên  │ teacher       │ 123456
  Học sinh   │ student       │ 123456

  Lưu ý: Thay đổi mật khẩu khi triển khai lên môi trường thực tế.

================================================================================
VIII. GHI CHÚ KỸ THUẬT
================================================================================

  - Kết nối CSDL dùng PDO (PHP Data Objects), hỗ trợ Prepared Statements
    để tránh SQL Injection.
  - Mật khẩu được mã hóa bằng bcrypt (password_hash / password_verify).
  - Phiên đăng nhập (session) được kiểm tra qua hàm is_logged_in() và
    is_teacher() trong includes/functions.php.
  - Công thức toán học được render phía client bằng KaTeX (thay thế MathJax
    để tốc độ load nhanh hơn).
  - File Word (.docx) được phân tích cú pháp bằng lớp WordQuizImporter.php
    (dùng thư viện PHPWord) để trích xuất câu hỏi và đáp án tự động.
  - File tải về (download.php) tuân thủ RFC 5987/RFC 6266 để đảm bảo tên
    file tiếng Việt hiển thị đúng trên mọi trình duyệt.
  - BASE_URL được định nghĩa trong functions.php để đảm bảo đường dẫn
    tương đối hoạt động đúng trên XAMPP.

================================================================================
