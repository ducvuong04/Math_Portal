<?php
// Sử dụng thư viện PHPWord đã được cài đặt qua Composer
use PhpOffice\PhpWord\IOFactory;

class WordQuizImporter {
    private $pdo; // Biến lưu trữ kết nối cơ sở dữ liệu

    /**
     * Hàm khởi tạo: Nhận vào đối tượng PDO để thực hiện các truy vấn bảo mật
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Hàm chính: Xử lý file Word và lưu vào cơ sở dữ liệu
     * @param string $filePath Đường dẫn file .docx trên server
     * @param int $assignmentId ID của bài tập vừa tạo trong bảng assignments
     */
    public function import($filePath, $assignmentId) {
        try {
            // Load file Word bằng thư viện PHPWord
            $phpWord = IOFactory::load($filePath);
            $fullText = "";

            // Duyệt qua từng Section trong file Word
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    $text = "";
                    if (method_exists($element, 'getElements')) {
                        foreach ($element->getElements() as $child) {
                            if (method_exists($child, 'getText')) {
                                $text .= $child->getText();
                            }
                        }
                    } elseif (method_exists($element, 'getText')) {
                        $text = $element->getText();
                    }
                    
                    if ($text !== "") {
                        // Loại bỏ các ký tự điều khiển lạ của Word (control characters)
                        $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text);
                        $fullText .= $text . "\n";
                    }
                }
            }

            // Làm sạch văn bản: Loại bỏ khoảng trắng không ngắt (nbsp) đúng chuẩn UTF-8
            // CỰC KỲ QUAN TRỌNG: KHÔNG ĐƯỢC DÙNG "\xa0" trơn vì nó sẽ cắt mất nửa sau của chữ "à" (\xc3\xa0)
            $fullText = str_replace(["\xc2\xa0", "\r"], [" ", "\n"], $fullText);
            $fullText = preg_replace('/\n\s*\n/', "\n", $fullText); // Loại bỏ dòng trống thừa

            // Mẫu RegEx NÂNG CẤP VÀ SỬA LỖI ĐỌC NHẦM ĐÁP ÁN:
            // - Hỗ trợ các kiểu đánh số: "Câu 1:", "Câu 1.", "1.", "1:"
            // - Bắt buộc phải có từ khóa báo hiệu đáp án (Đáp án, Chọn, KQ, ĐA, Kết quả) để không bị nhầm với chữ C trong "cm2" hay D trong "VNĐ"
            // - Thêm \b để đảm bảo đáp án A, B, C, D đứng độc lập.
            $pattern = '/(?:Câu\s*\d+\s*[:\.]|\d+\s*[\.\:])\s*(.*?)\s*A\s*[\.\)](.*?)\s*B\s*[\.\)](.*?)\s*C\s*[\.\)](.*?)\s*D\s*[\.\)](.*?)\s*(?:Đáp\s*án|Dap\s*an|Chọn|KQ|ĐA|Kết\s*quả|Trả\s*lời)\s*[:\-]?\s*([A-D])\b/si';
            
            preg_match_all($pattern, $fullText, $matches, PREG_SET_ORDER);

            if (empty($matches)) {
                // Debug: Ghi lại một đoạn văn bản đã đọc được để kiểm tra định dạng
                $preview = mb_substr($fullText, 0, 500);
                throw new Exception("Không tìm thấy câu hỏi nào. Hãy chắc chắn file Word đúng mẫu. Đoạn đầu file đọc được: " . $preview);
            }

            // CHIẾN THUẬT: Xóa sạch câu hỏi cũ trước khi nạp mới để tránh trùng lặp
            $deleteStmt = $this->pdo->prepare("DELETE FROM quizzes WHERE assignment_id = ?");
            $deleteStmt->execute([$assignmentId]);

            $sql = "INSERT INTO quizzes (assignment_id, question, opt_a, opt_b, opt_c, opt_d, answer) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);

            $count = 0;
            foreach ($matches as $match) {
                $questionText = trim($match[1]);
                $optA = trim($match[2]);
                $optB = trim($match[3]);
                $optC = trim($match[4]);
                $optD = trim($match[5]);
                $correctChar = strtoupper(trim($match[6]));

                // CHIẾN THUẬT: Chỉ lưu nếu có đủ nội dung câu hỏi và cả 4 đáp án
                if (!empty($questionText) && !empty($optA) && !empty($optB) && !empty($optC) && !empty($optD)) {
                    $answerMap = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3];
                    if (isset($answerMap[$correctChar])) {
                        $correctIndex = $answerMap[$correctChar];
                        $stmt->execute([
                            $assignmentId,
                            $questionText,
                            $optA,
                            $optB,
                            $optC,
                            $optD,
                            $correctIndex
                        ]);
                        $count++;
                    }
                }
            }

            return $count;

        } catch (Exception $e) {
            // Ném ngoại lệ để bên ngoài có thể bắt và hiển thị thông báo lỗi
            throw new Exception("Lỗi xử lý file Word: " . $e->getMessage());
        }
    }

    /**
     * Hàm phụ: Xử lý file Word và lưu vào bảng quizzes cho Bộ Đề (quiz_sets)
     * @param string $filePath Đường dẫn file .docx trên server
     * @param int $quizSetId ID của bộ đề trong bảng quiz_sets
     */
    public function import_to_quiz_set($filePath, $quizSetId) {
        try {
            // Load file Word bằng thư viện PHPWord
            $phpWord = IOFactory::load($filePath);
            $fullText = "";

            // Duyệt qua từng Section trong file Word
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    $text = "";
                    if (method_exists($element, 'getElements')) {
                        foreach ($element->getElements() as $child) {
                            if (method_exists($child, 'getText')) {
                                $text .= $child->getText();
                            }
                        }
                    } elseif (method_exists($element, 'getText')) {
                        $text = $element->getText();
                    }
                    
                    if ($text !== "") {
                        // Loại bỏ các ký tự điều khiển lạ của Word (control characters)
                        $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text);
                        $fullText .= $text . "\n";
                    }
                }
            }

            // Làm sạch văn bản: Loại bỏ khoảng trắng không ngắt (nbsp) đúng chuẩn UTF-8
            $fullText = str_replace(["\xc2\xa0", "\r"], [" ", "\n"], $fullText);
            $fullText = preg_replace('/\n\s*\n/', "\n", $fullText); // Loại bỏ dòng trống thừa

            // Mẫu RegEx NÂNG CẤP VÀ SỬA LỖI ĐỌC NHẦM ĐÁP ÁN:
            $pattern = '/(?:Câu\s*\d+\s*[:\.]|\d+\s*[\.\:])\s*(.*?)\s*A\s*[\.\)](.*?)\s*B\s*[\.\)](.*?)\s*C\s*[\.\)](.*?)\s*D\s*[\.\)](.*?)\s*(?:Đáp\s*án|Dap\s*an|Chọn|KQ|ĐA|Kết\s*quả|Trả\s*lời)\s*[:\-]?\s*([A-D])\b/si';
            
            preg_match_all($pattern, $fullText, $matches, PREG_SET_ORDER);

            if (empty($matches)) {
                $preview = mb_substr($fullText, 0, 500);
                throw new Exception("Không tìm thấy câu hỏi nào. Hãy chắc chắn file Word đúng mẫu. Đoạn đầu file đọc được: " . $preview);
            }

            // CHIẾN THUẬT: Xóa sạch câu hỏi cũ trong bộ đề trước khi nạp mới để tránh trùng lặp
            $deleteStmt = $this->pdo->prepare("DELETE FROM quizzes WHERE quiz_set_id = ?");
            $deleteStmt->execute([$quizSetId]);

            $sql = "INSERT INTO quizzes (quiz_set_id, question, opt_a, opt_b, opt_c, opt_d, answer) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);

            $count = 0;
            foreach ($matches as $match) {
                $questionText = trim($match[1]);
                $optA = trim($match[2]);
                $optB = trim($match[3]);
                $optC = trim($match[4]);
                $optD = trim($match[5]);
                $correctChar = strtoupper(trim($match[6]));

                if (!empty($questionText) && !empty($optA) && !empty($optB) && !empty($optC) && !empty($optD)) {
                    $answerMap = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3];
                    if (isset($answerMap[$correctChar])) {
                        $correctIndex = $answerMap[$correctChar];
                        $stmt->execute([
                            $quizSetId,
                            $questionText,
                            $optA,
                            $optB,
                            $optC,
                            $optD,
                            $correctIndex
                        ]);
                        $count++;
                    }
                }
            }

            return $count;

        } catch (Exception $e) {
            throw new Exception("Lỗi xử lý file Word: " . $e->getMessage());
        }
    }
}
?>
