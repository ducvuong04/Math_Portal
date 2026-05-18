<?php
require_once __DIR__ . '/../includes/db.php';

echo "Starting database migration...\n";

// 1. Add grade column to tables if not exists
try {
    $pdo->exec("ALTER TABLE chapters ADD COLUMN grade INT NOT NULL DEFAULT 12");
    echo "Added 'grade' column to chapters table.\n";
} catch (PDOException $e) {
    echo "Column 'grade' already exists in chapters table or table not found.\n";
}

try {
    $pdo->exec("ALTER TABLE assignments ADD COLUMN grade INT NOT NULL DEFAULT 12");
    echo "Added 'grade' column to assignments table.\n";
} catch (PDOException $e) {
    echo "Column 'grade' already exists in assignments table or table not found.\n";
}

try {
    $pdo->exec("ALTER TABLE exams ADD COLUMN grade INT NOT NULL DEFAULT 12");
    echo "Added 'grade' column to exams table.\n";
} catch (PDOException $e) {
    echo "Column 'grade' already exists in exams table or table not found.\n";
}

// Clean existing seeded Grade 10/11 data to re-insert correctly
$pdo->exec("DELETE FROM chapters WHERE grade IN (10, 11)");
echo "Cleaned previous Grade 10 and 11 records to allow clean re-insertion.\n";

// 2. Seeding Grade 10 Math content
$grade10_chapters = [
    [
        'chapter_key' => 'algebra_10_1',
        'title' => 'Chương 1: Mệnh đề và tập hợp (Lớp 10)',
        'icon' => 'rule',
        'grade' => 10,
        'topics' => [
            [
                'topic_id_str' => 'menh-de',
                'title' => 'Mệnh đề toán học',
                'description' => 'Mệnh đề, mệnh đề phủ định, kéo theo, đảo, tương đương, kí hiệu mọi và tồn tại.',
                'theory' => '### 1. Mệnh đề toán học
* **Định nghĩa:** Một mệnh đề toán học là một khẳng định đúng hoặc một khẳng định sai. Một khẳng định không thể vừa đúng vừa sai.
* **Mệnh đề chứa biến:** Là khẳng định chứa biến, trở thành mệnh đề đúng hoặc sai khi ta thay biến bằng một giá trị cụ thể.

### 2. Mệnh đề phủ định
* Phủ định của mệnh đề $P$ kí hiệu là $\bar{P}$. Nếu $P$ đúng thì $\bar{P}$ sai, nếu $P$ sai thì $\bar{P}$ đúng.

### 3. Mệnh đề kéo theo và tương đương
* Mệnh đề kéo theo $P \Rightarrow Q$ chỉ sai khi $P$ đúng và $Q$ sai.
* Mệnh đề tương đương $P \Leftrightarrow Q$ đúng khi cả hai mệnh đề cùng đúng hoặc cùng sai.',
                'formulas' => [
                    'Mệnh đề phủ định: $\bar{P}$',
                    'Mệnh đề tương đương: $P \Leftrightarrow Q$'
                ]
            ],
            [
                'topic_id_str' => 'tap-hop',
                'title' => 'Tập hợp và các phép toán',
                'description' => 'Tập hợp, tập con, các phép toán giao, hợp, hiệu.',
                'theory' => '### 1. Khái niệm tập hợp
* Tập hợp là một khái niệm cơ bản của toán học. Có hai cách xác định tập hợp: liệt kê phần tử hoặc chỉ ra tính chất đặc trưng.
* Tập rỗng kí hiệu là $\varnothing$, là tập hợp không chứa phần tử nào.

### 2. Phép toán tập hợp
* **Giao của hai tập hợp:** $A \cap B = \{x \in A \text{ và } x \in B\}$.
* **Hợp của hai tập hợp:** $A \cup B = \{x \in A \text{ hoặc } x \in B\}$.
* **Hiệu của hai tập hợp:** $A \setminus B = \{x \in A \text{ và } x \notin B\}$.',
                'formulas' => [
                    'Phép giao: $A \cap B = \{x \in A \text{ và } x \in B\}$',
                    'Phép hợp: $A \cup B = \{x \in A \text{ hoặc } x \in B\}$'
                ]
            ]
        ]
    ],
    [
        'chapter_key' => 'algebra_10_2',
        'title' => 'Chương 2: Bất phương trình bậc nhất hai ẩn (Lớp 10)',
        'icon' => 'linear_scale',
        'grade' => 10,
        'topics' => [
            [
                'topic_id_str' => 'bpt-bac-nhat-hai-an',
                'title' => 'Bất phương trình bậc nhất hai ẩn',
                'description' => 'Khái niệm, biểu diễn miền nghiệm của bất phương trình bậc nhất hai ẩn.',
                'theory' => '### 1. Khái niệm
* Bất phương trình bậc nhất hai ẩn $x, y$ có dạng tổng quát là:
  $ax + by < c$ (hoặc $ax + by \le c$, $ax + by > c$, $ax + by \ge c$),
  trong đó $a, b, c$ là các số thực đã cho, $a$ và $b$ không đồng thời bằng 0.

### 2. Biểu diễn miền nghiệm
Miền nghiệm của bất phương trình trên mặt phẳng tọa độ $Oxy$ là nửa mặt phẳng được phân chia bởi đường thẳng $d: ax + by = c$. Quy trình tìm:
1. Vẽ đường thẳng $d$.
2. Chọn một điểm $M_0(x_0, y_0)$ không nằm trên $d$ (thường chọn $O(0, 0)$).
3. Tính $ax_0 + by_0$ và so sánh với $c$ để kết luận nửa mặt phẳng nghiệm.',
                'formulas' => [
                    'Dạng tổng quát: $ax + by < c$',
                    'Đường thẳng biên: $ax + by = c$'
                ]
            ]
        ]
    ],
    [
        'chapter_key' => 'algebra_10_3',
        'title' => 'Chương 3: Hàm số bậc hai và đồ thị (Lớp 10)',
        'icon' => 'show_chart',
        'grade' => 10,
        'topics' => [
            [
                'topic_id_str' => 'ham-so-bac-hai',
                'title' => 'Hàm số bậc hai và đồ thị',
                'description' => 'Chiều biến thiên và đồ thị parabol của hàm số bậc hai.',
                'theory' => '### 1. Định nghĩa
* Hàm số bậc hai là hàm số được cho bằng công thức dạng:
  $y = ax^2 + bx + c$ ($a \ne 0$).

### 2. Đồ thị và bảng biến thiên
* Đồ thị của hàm số là một đường Parabol có đỉnh là $I\left(-\frac{b}{2a}; -\frac{\Delta}{4a}\right)$ với $\Delta = b^2 - 4ac$.
* Trục đối xứng là đường thẳng $x = -\frac{b}{2a}$.
* Nếu $a > 0$, Parabol bề lõm quay lên trên. Nếu $a < 0$, bề lõm quay xuống dưới.',
                'formulas' => [
                    'Đỉnh Parabol: $I\left(-\frac{b}{2a}; -\frac{\Delta}{4a}\right)$',
                    'Trục đối xứng: $x = -\frac{b}{2a}$'
                ]
            ]
        ]
    ],
    [
        'chapter_key' => 'geometry_10_1',
        'title' => 'Chương 4: Hệ thức lượng trong tam giác (Lớp 10)',
        'icon' => 'change_history',
        'grade' => 10,
        'topics' => [
            [
                'topic_id_str' => 'dinh-ly-cos-sin',
                'title' => 'Định lý côsin và định lý sin',
                'description' => 'Áp dụng định lý côsin và định lý sin để giải tam giác.',
                'theory' => '### 1. Định lý côsin
Trong tam giác $ABC$ có các cạnh $BC=a, CA=b, AB=c$:
* $a^2 = b^2 + c^2 - 2bc \cos A$
* $b^2 = a^2 + c^2 - 2ac \cos B$
* $c^2 = a^2 + b^2 - 2ab \cos C$

### 2. Định lý sin
$\frac{a}{\sin A} = \frac{b}{\sin B} = \frac{c}{\sin C} = 2R$
Trong đó $R$ là bán kính đường tròn ngoại tiếp tam giác.',
                'formulas' => [
                    'Định lý côsin: $a^2 = b^2 + c^2 - 2bc \cos A$',
                    'Định lý sin: $\frac{a}{\sin A} = 2R$'
                ]
            ]
        ]
    ]
];

$grade11_chapters = [
    [
        'chapter_key' => 'algebra_11_1',
        'title' => 'Chương 1: Hàm số lượng giác và PT lượng giác (Lớp 11)',
        'icon' => 'waves',
        'grade' => 11,
        'topics' => [
            [
                'topic_id_str' => 'goc-luong-giac',
                'title' => 'Góc lượng giác và giá trị lượng giác',
                'description' => 'Đơn vị ra-đi-an, đường tròn lượng giác, các công thức biến đổi lượng giác.',
                'theory' => '### 1. Đơn vị đo góc
* Số đo của một góc bằng ra-đi-an (rad): $\pi \text{ rad} = 180^\circ$.

### 2. Công thức lượng giác cơ bản
* $\sin^2 \alpha + \cos^2 \alpha = 1$
* $1 + \tan^2 \alpha = \frac{1}{\cos^2 \alpha}$ (với $\cos \alpha \ne 0$)
* $1 + \cot^2 \alpha = \frac{1}{\sin^2 \alpha}$ (với $\sin \alpha \ne 0$)',
                'formulas' => [
                    'Hệ thức cơ bản: $\sin^2 \alpha + \cos^2 \alpha = 1$',
                    'Công thức nhân đôi: $\sin 2\alpha = 2\sin\alpha\cos\alpha$'
                ]
            ],
            [
                'topic_id_str' => 'pt-luong-giac',
                'title' => 'Phương trình lượng giác cơ bản',
                'description' => 'Giải phương trình lượng giác dạng sin, cos, tan, cot.',
                'theory' => '### 1. Phương trình $\sin x = m$
* Có nghiệm khi và chỉ khi $|m| \le 1$.
* Nếu $\sin\alpha = m$ thì:
  $x = \alpha + k2\pi$ hoặc $x = \pi - \alpha + k2\pi$ ($k \in \mathbb{Z}$).

### 2. Phương trình $\cos x = m$
* Có nghiệm khi và chỉ khi $|m| \le 1$.
* Nếu $\cos\alpha = m$ thì:
  $x = \alpha + k2\pi$ hoặc $x = -\alpha + k2\pi$ ($k \in \mathbb{Z}$).',
                'formulas' => [
                    'Nghiệm sin: $\sin x = \sin\alpha \Leftrightarrow x = \alpha + k2\pi \lor x = \pi - \alpha + k2\pi$',
                    'Nghiệm cos: $\cos x = \cos\alpha \Leftrightarrow x = \pm\alpha + k2\pi$'
                ]
            ]
        ]
    ],
    [
        'chapter_key' => 'algebra_11_2',
        'title' => 'Chương 2: Dãy số, cấp số cộng và cấp số nhân (Lớp 11)',
        'icon' => 'format_list_numbered',
        'grade' => 11,
        'topics' => [
            [
                'topic_id_str' => 'cap-so-cong',
                'title' => 'Cấp số cộng',
                'description' => 'Định nghĩa, số hạng tổng quát, tính chất, tổng n số hạng đầu.',
                'theory' => '### 1. Định nghĩa
Cấp số cộng là một dãy số trong đó, kể từ số hạng thứ hai, mỗi số hạng đều bằng số hạng đứng ngay trước nó cộng với một số không đổi $d$ (gọi là công sai):
$u_{n} = u_{n-1} + d$.

### 2. Công thức số hạng tổng quát
$u_n = u_1 + (n-1)d$

### 3. Tổng $n$ số hạng đầu
$S_n = \frac{n(u_1 + u_n)}{2} = \frac{n[2u_1 + (n-1)d]}{2}$',
                'formulas' => [
                    'Số hạng tổng quát CSC: $u_n = u_1 + (n-1)d$',
                    'Tổng CSC: $S_n = \frac{n[2u_1 + (n-1)d]}{2}$'
                ]
            ],
            [
                'topic_id_str' => 'cap-so-nhan',
                'title' => 'Cấp số nhân',
                'description' => 'Định nghĩa, số hạng tổng quát, tính chất, tổng n số hạng đầu.',
                'theory' => '### 1. Định nghĩa
Cấp số nhân là một dãy số trong đó, kể từ số hạng thứ hai, mỗi số hạng đều bằng số hạng đứng ngay trước nó nhân với một số không đổi $q$ (gọi là công bội):
$u_n = u_{n-1} \cdot q$.

### 2. Công thức số hạng tổng quát
$u_n = u_1 \cdot q^{n-1}$

### 3. Tổng $n$ số hạng đầu
$S_n = u_1 \cdot \frac{1 - q^n}{1 - q}$ (với $q \ne 1$)',
                'formulas' => [
                    'Số hạng tổng quát CSN: $u_n = u_1 \cdot q^{n-1}$',
                    'Tổng CSN: $S_n = u_1 \cdot \frac{1 - q^n}{1 - q}$'
                ]
            ]
        ]
    ]
];

// Helper function to seed a batch of chapters
function seed_chapters($pdo, $chapters_data) {
    foreach ($chapters_data as $chap) {
        $stmt = $pdo->prepare("INSERT INTO chapters (chapter_key, title, icon, grade) VALUES (?, ?, ?, ?)");
        $stmt->execute([$chap['chapter_key'], $chap['title'], $chap['icon'], $chap['grade']]);
        $chapter_id = $pdo->lastInsertId();
        echo "Seeded chapter: {$chap['title']}\n";

        foreach ($chap['topics'] as $top) {
            $stmt = $pdo->prepare("INSERT INTO topics (chapter_id, topic_id_str, title, description, theory) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$chapter_id, $top['topic_id_str'], $top['title'], $top['description'], $top['theory']]);
            $topic_id = $pdo->lastInsertId();
            echo "  Seeded topic: {$top['title']}\n";

            // Seed formulas
            foreach ($top['formulas'] as $f_text) {
                $stmt = $pdo->prepare("INSERT INTO formulas (topic_id, formula_text) VALUES (?, ?)");
                $stmt->execute([$topic_id, $f_text]);
                echo "    Seeded formula: $f_text\n";
            }
        }
    }
}

seed_chapters($pdo, $grade10_chapters);
seed_chapters($pdo, $grade11_chapters);

echo "Database migration and seeding successfully completed!\n";
?>
