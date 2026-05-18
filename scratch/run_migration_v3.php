<?php
require_once __DIR__ . '/../includes/db.php';

echo "Starting comprehensive database migration and seeding for Grade 10 and 11...\n";

try {
    $pdo->beginTransaction();

    // 1. Clean existing seeded Grade 10/11 data to re-insert correctly
    // First delete related formulas
    $pdo->exec("DELETE FROM formulas WHERE topic_id IN (
        SELECT t.id FROM topics t JOIN chapters c ON t.chapter_id = c.id WHERE c.grade IN (10, 11)
    )");
    // Then delete topics
    $pdo->exec("DELETE FROM topics WHERE chapter_id IN (
        SELECT id FROM chapters WHERE grade IN (10, 11)
    )");
    // Finally delete chapters
    $pdo->exec("DELETE FROM chapters WHERE grade IN (10, 11)");
    echo "Successfully cleaned previous Grade 10 and 11 records to allow clean re-insertion.\n";

    // 2. Seeding Grade 10 Math content
    $grade10_chapters = [
        [
            'chapter_key' => 'algebra_10_1',
            'title' => 'Chương 1: Mệnh đề và tập hợp',
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
* Mệnh đề tương đương $P \Leftrightarrow Q$ đúng khi cả hai mệnh đề cùng đúng hoặc cùng sai.
* Kí hiệu với mọi ($\forall$) và tồn tại ($\exists$) biểu diễn các khẳng định trong toán học một cách ngắn gọn.',
                    'formulas' => [
                        'Mệnh đề phủ định: $\bar{P}$',
                        'Mệnh đề kéo theo: $P \Rightarrow Q$',
                        'Mệnh đề tương đương: $P \Leftrightarrow Q$',
                        'Mọi và tồn tại: $\forall x \in M, P(x)$ hoặc $\exists x \in M, P(x)$'
                    ]
                ],
                [
                    'topic_id_str' => 'tap-hop',
                    'title' => 'Tập hợp và các phép toán trên tập hợp',
                    'description' => 'Tập hợp, tập con, các phép toán giao, hợp, hiệu và phần bù.',
                    'theory' => '### 1. Khái niệm tập hợp
* Tập hợp là một khái niệm cơ bản của toán học. Có hai cách xác định tập hợp: liệt kê phần tử hoặc chỉ ra tính chất đặc trưng.
* Tập rỗng kí hiệu là $\varnothing$, là tập hợp không chứa phần tử nào.
* Tập hợp $A$ là con của tập hợp $B$, kí hiệu $A \subset B$, nếu mọi phần tử của $A$ đều là phần tử của $B$.

### 2. Phép toán tập hợp
* **Giao của hai tập hợp:** $A \cap B = \{x \mid x \in A \text{ và } x \in B\}$.
* **Hợp của hai tập hợp:** $A \cup B = \{x \mid x \in A \text{ hoặc } x \in B\}$.
* **Hiệu của hai tập hợp:** $A \setminus B = \{x \mid x \in A \text{ và } x \notin B\}$.
* **Phần bù:** Nếu $B \subset A$, phần bù của $B$ trong $A$ kí hiệu là $C_A B = A \setminus B$.',
                    'formulas' => [
                        'Phép giao: $A \cap B = \{x \mid x \in A \land x \in B\}$',
                        'Phép hợp: $A \cup B = \{x \mid x \in A \lor x \in B\}$',
                        'Phép hiệu: $A \setminus B = \{x \mid x \in A \land x \notin B\}$',
                        'Phần bù: $C_A B = A \setminus B \text{ (với } B \subset A\text{)}'
                    ]
                ]
            ]
        ],
        [
            'chapter_key' => 'algebra_10_2',
            'title' => 'Chương 2: Bất phương trình và hệ bất phương trình bậc nhất hai ẩn',
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
1. Vẽ đường thẳng $d: ax + by = c$.
2. Chọn một điểm $M_0(x_0, y_0)$ không nằm trên $d$ (thường chọn gốc tọa độ $O(0, 0)$ nếu $c \ne 0$).
3. Tính $ax_0 + by_0$ và so sánh với $c$ để kết luận nửa mặt phẳng nghiệm. Phần không là nghiệm sẽ được gạch bỏ.',
                    'formulas' => [
                        'Dạng tổng quát: $ax + by < c$',
                        'Đường thẳng biên: $ax + by = c$'
                    ]
                ],
                [
                    'topic_id_str' => 'he-bpt-bac-nhat-hai-an',
                    'title' => 'Hệ bất phương trình bậc nhất hai ẩn',
                    'description' => 'Khái niệm miền nghiệm của hệ bất phương trình bậc nhất hai ẩn và bài toán tối ưu hóa.',
                    'theory' => '### 1. Hệ bất phương trình bậc nhất hai ẩn
* Là một hệ gồm nhiều bất phương trình bậc nhất hai ẩn $x, y$.
* Miền nghiệm của hệ bất phương trình là giao các miền nghiệm của các bất phương trình trong hệ. Trên mặt phẳng tọa độ, miền nghiệm thường là một đa giác (lồi) được bao quanh bởi các đường biên.

### 2. Ứng dụng tối ưu hóa (Quy hoạch tuyến tính)
* Xét biểu thức $F(x, y) = ax + by$ với $(x; y)$ chạy trên miền nghiệm đa giác $S$.
* Định lý: Biểu thức $F(x, y)$ đạt giá trị lớn nhất (hoặc nhỏ nhất) tại một trong các đỉnh của đa giác miền nghiệm $S$.',
                    'formulas' => [
                        'Hệ bất phương trình mẫu: $\begin{cases} a_1 x + b_1 y \le c_1 \\ a_2 x + b_2 y \le c_2 \end{cases}$',
                        'Hàm mục tiêu tuyến tính: $F(x, y) = ax + by$'
                    ]
                ]
            ]
        ],
        [
            'chapter_key' => 'geometry_10_1',
            'title' => 'Chương 3: Hệ thức lượng trong tam giác',
            'icon' => 'change_history',
            'grade' => 10,
            'topics' => [
                [
                    'topic_id_str' => 'gia-tri-luong-giac-0-180',
                    'title' => 'Giá trị lượng giác của góc từ 0° đến 180°',
                    'description' => 'Định nghĩa giá trị lượng giác, góc bù nhau, phụ nhau trên nửa đường tròn đơn vị.',
                    'theory' => '### 1. Định nghĩa nửa đường tròn đơn vị
* Với mỗi góc $\alpha$ ($0^\circ \le \alpha \le 180^\circ$), điểm $M(x_0; y_0)$ nằm trên nửa đường tròn đơn vị.
* Các giá trị lượng giác được định nghĩa:
  * $\sin \alpha = y_0$
  * $\cos \alpha = x_0$
  * $\tan \alpha = \frac{y_0}{x_0}$ (với $x_0 \ne 0$)
  * $\cot \alpha = \frac{x_0}{y_0}$ (với $y_0 \ne 0$)

### 2. Góc bù nhau ($\alpha$ và $180^\circ - \alpha$)
* $\sin(180^\circ - \alpha) = \sin \alpha$
* $\cos(180^\circ - \alpha) = -\cos \alpha$
* $\tan(180^\circ - \alpha) = -\tan \alpha$
* $\cot(180^\circ - \alpha) = -\cot \alpha$',
                    'formulas' => [
                        'Đồng nhất cơ bản: $\sin^2 \alpha + \cos^2 \alpha = 1$',
                        'Đồng nhất thương: $1 + \tan^2 \alpha = \frac{1}{\cos^2 \alpha}$ (với $\alpha \ne 90^\circ$)',
                        'Góc bù nhau: $\sin(180^\circ - \alpha) = \sin\alpha$',
                        'Góc phụ nhau: $\cos(90^\circ - \alpha) = \sin\alpha$'
                    ]
                ],
                [
                    'topic_id_str' => 'he-thuc-luong-tam-giac',
                    'title' => 'Hệ thức lượng trong tam giác và giải tam giác',
                    'description' => 'Định lý côsin, định lý sin, công thức trung tuyến và các công thức tính diện tích tam giác.',
                    'theory' => '### 1. Định lý côsin
Trong tam giác $ABC$ có các cạnh $BC=a, CA=b, AB=c$:
* $a^2 = b^2 + c^2 - 2bc \cos A$
* Hệ quả tính góc: $\cos A = \frac{b^2 + c^2 - a^2}{2bc}$

### 2. Định lý sin
* $\frac{a}{\sin A} = \frac{b}{\sin B} = \frac{c}{\sin C} = 2R$ (với $R$ là bán kính đường tròn ngoại tiếp tam giác).

### 3. Công thức tính diện tích tam giác $S$
* $S = \frac{1}{2}a h_a = \frac{1}{2}bc \sin A$
* $S = \frac{abc}{4R}$
* $S = pr$ (với $p = \frac{a+b+c}{2}$ là nửa chu vi, $r$ là bán kính đường tròn nội tiếp)
* Công thức Heron: $S = \sqrt{p(p-a)(p-b)(p-c)}$',
                    'formulas' => [
                        'Định lý côsin: $a^2 = b^2 + c^2 - 2bc \cos A$',
                        'Định lý sin: $\frac{a}{\sin A} = 2R$',
                        'Độ dài đường trung tuyến: $m_a^2 = \frac{2(b^2 + c^2) - a^2}{4}$',
                        'Công thức Heron: $S = \sqrt{p(p-a)(p-b)(p-c)}$',
                        'Diện tích tam giác: $S = pr = \frac{abc}{4R}$'
                    ]
                ]
            ]
        ],
        [
            'chapter_key' => 'geometry_10_2',
            'title' => 'Chương 4: Vectơ',
            'icon' => 'navigation',
            'grade' => 10,
            'topics' => [
                [
                    'topic_id_str' => 'vecto-dai-cuong',
                    'title' => 'Các khái niệm mở đầu và phép toán vectơ',
                    'description' => 'Khái niệm vectơ, độ dài, cùng phương, bằng nhau, quy tắc cộng trừ vectơ.',
                    'theory' => '### 1. Khái niệm vectơ
* Vectơ là một đoạn thẳng có hướng. Kí hiệu là $\vec{AB}$ (điểm đầu $A$, điểm cuối $B$) hoặc $\vec{a}$.
* Độ dài của vectơ là khoảng cách giữa điểm đầu và điểm cuối, kí hiệu $|\vec{AB}|$.
* Hai vectơ gọi là cùng phương nếu giá của chúng song song hoặc trùng nhau. Hai vectơ cùng phương thì có thể cùng hướng hoặc ngược hướng.
* Hai vectơ bằng nhau nếu chúng cùng hướng và cùng độ dài.

### 2. Các quy tắc phép toán vectơ
* **Quy tắc ba điểm:** Với ba điểm $A, B, C$ bất kì, ta có $\vec{AB} + \vec{BC} = \vec{AC}$.
* **Quy tắc trừ:** $\vec{AB} - \vec{AC} = \vec{CB}$.
* **Quy tắc hình bình hành:** Nếu $ABCD$ là hình bình hành thì $\vec{AB} + \vec{AD} = \vec{AC}$.',
                    'formulas' => [
                        'Quy tắc cộng: $\vec{AB} + \vec{BC} = \vec{AC}$',
                        'Quy tắc trừ: $\vec{AB} - \vec{AC} = \vec{CB}$',
                        'Quy tắc hình bình hành: $\vec{AB} + \vec{AD} = \vec{AC}$',
                        'Tính chất trung điểm $I$ của $AB$: $\vec{IA} + \vec{IB} = \vec{0}$'
                    ]
                ],
                [
                    'topic_id_str' => 'tich-vecto',
                    'title' => 'Tích của một vectơ với một số và Tích vô hướng',
                    'description' => 'Nhân vectơ với số, tích vô hướng của hai vectơ, góc giữa hai vectơ.',
                    'theory' => '### 1. Tích của vectơ với một số
* Tích của một số thực $k$ với vectơ $\vec{a}$ là một vectơ, kí hiệu là $k\vec{a}$.
* Vectơ $k\vec{a}$ cùng hướng với $\vec{a}$ nếu $k \ge 0$, ngược hướng với $\vec{a}$ nếu $k < 0$.
* Độ dài: $|k\vec{a}| = |k| \cdot |\vec{a}|$.

### 2. Tích vô hướng của hai vectơ
* Tích vô hướng của hai vectơ $\vec{a}$ và $\vec{b}$ là một số thực, kí hiệu là $\vec{a} \cdot \vec{b}$, định nghĩa bởi:
  $\vec{a} \cdot \vec{b} = |\vec{a}| \cdot |\vec{b}| \cdot \cos(\vec{a}, \vec{b})$.
* Biểu thức tọa độ trong mặt phẳng $Oxy$ với $\vec{a}=(x_1; y_1)$ và $\vec{b}=(x_2; y_2)$:
  $\vec{a} \cdot \vec{b} = x_1 x_2 + y_1 y_2$.',
                    'formulas' => [
                        'Tích vô hướng: $\vec{a} \cdot \vec{b} = |\vec{a}| \cdot |\vec{b}| \cdot \cos(\vec{a}, \vec{b})$',
                        'Góc giữa hai vectơ: $\cos(\vec{a}, \vec{b}) = \frac{\vec{a} \cdot \vec{b}}{|\vec{a}| \cdot |\vec{b}|}$',
                        'Biểu thức tọa độ tích vô hướng: $\vec{a} \cdot \vec{b} = x_1 x_2 + y_1 y_2$',
                        'Điều kiện vuông góc: $\vec{a} \perp \vec{b} \Leftrightarrow x_1 x_2 + y_1 y_2 = 0$'
                    ]
                ]
            ]
        ],
        [
            'chapter_key' => 'algebra_10_3',
            'title' => 'Chương 6: Hàm số, đồ thị và ứng dụng',
            'icon' => 'show_chart',
            'grade' => 10,
            'topics' => [
                [
                    'topic_id_str' => 'ham-so-co-ban',
                    'title' => 'Hàm số và đồ thị',
                    'description' => 'Khái niệm hàm số, tập xác định, tập giá trị, sự biến thiên và đồ thị của hàm số.',
                    'theory' => '### 1. Định nghĩa hàm số
* Hàm số là một quy tắc cho tương ứng mỗi số $x$ thuộc tập hợp $D$ với một và chỉ một số $y$.
* Tập hợp $D$ gọi là tập xác định. Tập tất cả các giá trị $y$ nhận được gọi là tập giá trị.

### 2. Sự biến thiên của hàm số
* Hàm số $y = f(x)$ đồng biến (tăng) trên khoảng $K$ nếu:
  $\forall x_1, x_2 \in K, x_1 < x_2 \Rightarrow f(x_1) < f(x_2)$.
* Hàm số nghịch biến (giảm) trên khoảng $K$ nếu:
  $\forall x_1, x_2 \in K, x_1 < x_2 \Rightarrow f(x_1) > f(x_2)$.',
                    'formulas' => [
                        'Điều kiện đồng biến: $x_1 < x_2 \Rightarrow f(x_1) < f(x_2)$',
                        'Điều kiện nghịch biến: $x_1 < x_2 \Rightarrow f(x_1) > f(x_2)$'
                    ]
                ],
                [
                    'topic_id_str' => 'ham-so-bac-hai-theory',
                    'title' => 'Hàm số bậc hai và đồ thị Parabol',
                    'description' => 'Chiều biến thiên, đỉnh parabol, trục đối xứng và đồ thị hàm số bậc hai.',
                    'theory' => '### 1. Định nghĩa
* Hàm số bậc hai là hàm số có công thức dạng: $y = ax^2 + bx + c$ ($a \ne 0$).

### 2. Đồ thị và bảng biến thiên
* Đồ thị của hàm số bậc hai là một đường Parabol có đỉnh là $I\left(-\frac{b}{2a}; -\frac{\Delta}{4a}\right)$ với $\Delta = b^2 - 4ac$.
* Trục đối xứng là đường thẳng $x = -\frac{b}{2a}$.
* **Chiều biến thiên:**
  * Nếu $a > 0$, hàm số nghịch biến trên $\left(-\infty; -\frac{b}{2a}\right)$ và đồng biến trên $\left(-\frac{b}{2a}; +\infty\right)$. Đồ thị quay bề lõm lên trên.
  * Nếu $a < 0$, hàm số đồng biến trên $\left(-\infty; -\frac{b}{2a}\right)$ và nghịch biến trên $\left(-\frac{b}{2a}; +\infty\right)$. Đồ thị quay bề lõm xuống dưới.',
                    'formulas' => [
                        'Tọa độ đỉnh Parabol: $I\left(-\frac{b}{2a}; -\frac{\Delta}{4a}\right)$',
                        'Trục đối xứng: $x = -\frac{b}{2a}$',
                        'Biệt thức Delta: $\Delta = b^2 - 4ac$'
                    ]
                ]
            ]
        ],
        [
            'chapter_key' => 'geometry_10_3',
            'title' => 'Chương 7: Phương pháp tọa độ trong mặt phẳng',
            'icon' => 'grid_view',
            'grade' => 10,
            'topics' => [
                [
                    'topic_id_str' => 'pt-duong-thang',
                    'title' => 'Phương trình đường thẳng',
                    'description' => 'Vectơ pháp tuyến, chỉ phương, phương trình tham số, phương trình tổng quát, khoảng cách và góc.',
                    'theory' => '### 1. Vectơ pháp tuyến và chỉ phương
* Vectơ chỉ phương $\vec{u}(u_1; u_2)$ song song hoặc trùng với đường thẳng.
* Vectơ pháp tuyến $\vec{n}(a; b)$ vuông góc với đường thẳng. Nếu $\vec{u}=(u_1; u_2)$ thì ta chọn $\vec{n}=(-u_2; u_1)$.

### 2. Các dạng phương trình đường thẳng
* **Phương trình tổng quát:** $a(x - x_0) + b(y - y_0) = 0 \Leftrightarrow ax + by + c = 0$ ($a^2+b^2 \ne 0$).
* **Phương trình tham số:** $\begin{cases} x = x_0 + u_1 t \\ y = y_0 + u_2 t \end{cases}$ (với $t \in \mathbb{R}$).

### 3. Khoảng cách từ điểm đến đường thẳng
* Khoảng cách từ điểm $M_0(x_0; y_0)$ đến đường thẳng $\Delta: ax + by + c = 0$ được tính bằng:
  $d(M_0, \Delta) = \frac{|a x_0 + b y_0 + c|}{\sqrt{a^2 + b^2}}$.',
                    'formulas' => [
                        'Phương trình tổng quát: $ax + by + c = 0$',
                        'Phương trình tham số: $\begin{cases} x = x_0 + u_1 t \\ y = y_0 + u_2 t \end{cases}$',
                        'Khoảng cách từ điểm đến ĐT: $d(M_0, \Delta) = \frac{|a x_0 + b y_0 + c|}{\sqrt{a^2 + b^2}}$',
                        'Góc $\varphi$ giữa hai đường thẳng: $\cos\varphi = \frac{|a_1 a_2 + b_1 b_2|}{\sqrt{a_1^2+b_1^2}\sqrt{a_2^2+b_2^2}}$'
                    ]
                ],
                [
                    'topic_id_str' => 'pt-duong-tron',
                    'title' => 'Phương trình đường tròn',
                    'description' => 'Phương trình chính tắc, phương trình tổng quát của đường tròn và phương trình tiếp tuyến.',
                    'theory' => '### 1. Phương trình chính tắc
* Đường tròn tâm $I(a; b)$, bán kính $R$ có phương trình chính tắc:
  $(x - a)^2 + (y - b)^2 = R^2$.

### 2. Phương trình tổng quát
* Phương trình dạng $x^2 + y^2 - 2ax - 2by + c = 0$ là phương trình đường tròn khi và chỉ khi $a^2 + b^2 - c > 0$.
* Khi đó đường tròn có:
  * Tâm $I(a; b)$
  * Bán kính $R = \sqrt{a^2 + b^2 - c}$.

### 3. Phương trình tiếp tuyến tại 1 điểm
* Tiếp tuyến của đường tròn tâm $I(a; b)$ tại điểm $M_0(x_0; y_0)$ thuộc đường tròn có phương trình:
  $(x_0 - a)(x - x_0) + (y_0 - b)(y - y_0) = 0$.',
                    'formulas' => [
                        'Phương trình chính tắc: $(x-a)^2 + (y-b)^2 = R^2$',
                        'Phương trình tổng quát: $x^2 + y^2 - 2ax - 2by + c = 0$',
                        'Điều kiện là đường tròn: $a^2 + b^2 - c > 0$',
                        'Bán kính đường tròn tổng quát: $R = \sqrt{a^2 + b^2 - c}$'
                    ]
                ]
            ]
        ],
        [
            'chapter_key' => 'algebra_10_4',
            'title' => 'Chương 8: Đại số tổ hợp',
            'icon' => 'widgets',
            'grade' => 10,
            'topics' => [
                [
                    'topic_id_str' => 'quy-tac-dem',
                    'title' => 'Quy tắc đếm và hoán vị, chỉnh hợp, tổ hợp',
                    'description' => 'Quy tắc cộng, quy tắc nhân, hoán vị, chỉnh hợp, tổ hợp.',
                    'theory' => '### 1. Quy tắc đếm cơ bản
* **Quy tắc cộng:** Nếu một công việc có thể thực hiện theo một trong hai phương án A (có $m$ cách) hoặc B (có $n$ cách, độc lập với A), thì số cách hoàn thành công việc là $m + n$.
* **Quy tắc nhân:** Nếu một công việc gồm hai giai đoạn liên tiếp, giai đoạn 1 có $m$ cách thực hiện, giai đoạn 2 có $n$ cách thực hiện, thì số cách hoàn thành công việc là $m \times n$.

### 2. Hoán vị, chỉnh hợp, tổ hợp
* **Hoán vị:** Sắp xếp $n$ phần tử khác nhau theo thứ tự. Số hoán vị là $P_n = n! = n(n-1)\dots 2 \cdot 1$.
* **Chỉnh hợp:** Lấy $k$ phần tử từ $n$ phần tử khác nhau và xếp thứ tự ($0 \le k \le n$). Số chỉnh hợp là:
  $A_n^k = \frac{n!}{(n-k)!}$.
* **Tổ hợp:** Lấy $k$ phần tử từ $n$ phần tử khác nhau (không xếp thứ tự, $0 \le k \le n$). Số tổ hợp là:
  $C_n^k = \frac{n!}{k!(n-k)!}$.',
                    'formulas' => [
                        'Giai thừa: $n! = n(n-1)\dots 1$ (quy ước $0! = 1$)',
                        'Số hoán vị: $P_n = n!$',
                        'Số chỉnh hợp: $A_n^k = \frac{n!}{(n-k)!}$',
                        'Số tổ hợp: $C_n^k = \frac{n!}{k!(n-k)!}$',
                        'Hệ thức đối xứng tổ hợp: $C_n^k = C_n^{n-k}$'
                    ]
                ],
                [
                    'topic_id_str' => 'nhi-thuc-newton',
                    'title' => 'Nhị thức Newton',
                    'description' => 'Công thức khai triển nhị thức Newton với số mũ nhỏ (n = 4 và n = 5).',
                    'theory' => '### 1. Công thức khai triển nhị thức Newton
* Khai triển lũy thừa của một nhị thức $(a + b)^n$ dựa trên các hệ số tổ hợp. Với lớp 10, ta tập trung vào $n = 4$ và $n = 5$:
* **Với n = 4:**
  $(a + b)^4 = C_4^0 a^4 + C_4^1 a^3b + C_4^2 a^2b^2 + C_4^3 ab^3 + C_4^4 b^4$
  $(a + b)^4 = a^4 + 4a^3b + 6a^2b^2 + 4ab^3 + b^4$.
* **Với n = 5:**
  $(a + b)^5 = C_5^0 a^5 + C_5^1 a^4b + C_5^2 a^3b^2 + C_5^3 a^2b^3 + C_5^4 ab^4 + C_5^5 b^5$
  $(a + b)^5 = a^5 + 5a^4b + 10a^3b^2 + 10a^2b^3 + 5ab^4 + b^5$.

### 2. Các tính chất khai triển
* Số các số hạng trong khai triển bằng $n + 1$.
* Tổng số mũ của $a$ và $b$ trong mỗi số hạng luôn bằng $n$.
* Các hệ số của số hạng cách đều hai đầu bằng nhau (do $C_n^k = C_n^{n-k}$).',
                    'formulas' => [
                        'Khai triển $(a+b)^4 = a^4 + 4a^3b + 6a^2b^2 + 4ab^3 + b^4$',
                        'Khai triển $(a+b)^5 = a^5 + 5a^4b + 10a^3b^2 + 10a^2b^3 + 5ab^4 + b^5$',
                        'Hệ số tổ hợp tổng quát: $C_n^k$'
                    ]
                ]
            ]
        ]
    ];

    $grade11_chapters = [
        [
            'chapter_key' => 'algebra_11_1',
            'title' => 'Chương 1: Hàm số lượng giác và phương trình lượng giác',
            'icon' => 'waves',
            'grade' => 11,
            'topics' => [
                [
                    'topic_id_str' => 'goc-luong-giac-ly-thuyet',
                    'title' => 'Góc lượng giác và giá trị lượng giác',
                    'description' => 'Đơn vị ra-đi-an, hệ thức cơ bản và các công thức lượng giác quan trọng.',
                    'theory' => '### 1. Đơn vị đo góc
* Số đo ra-đi-an của góc: $\pi \text{ rad} = 180^\circ \Rightarrow 1^\circ = \frac{\pi}{180} \text{ rad}$.

### 2. Công thức lượng giác cơ bản
* $\sin^2 x + \cos^2 x = 1$
* $1 + \tan^2 x = \frac{1}{\cos^2 x}$ (với $x \ne \frac{\pi}{2} + k\pi$)
* $1 + \cot^2 x = \frac{1}{\sin^2 x}$ (với $x \ne k\pi$)

### 3. Công thức lượng giác biến đổi
* **Công thức cộng:**
  * $\cos(a - b) = \cos a\cos b + \sin a\sin b$
  * $\sin(a + b) = \sin a\cos b + \cos a\sin b$
* **Công thức nhân đôi:**
  * $\sin 2a = 2\sin a\cos a$
  * $\cos 2a = \cos^2 a - \sin^2 a = 2\cos^2 a - 1 = 1 - 2\sin^2 a$',
                    'formulas' => [
                        'Hệ thức cơ bản: $\sin^2 x + \cos^2 x = 1$',
                        'Công thức cộng sin: $\sin(a+b) = \sin a\cos b + \cos a\sin b$',
                        'Công thức cộng cos: $\cos(a+b) = \cos a\cos b - \sin a\sin b$',
                        'Công thức nhân đôi: $\sin 2x = 2\sin x\cos x$',
                        'Công thức hạ bậc: $\cos^2 x = \frac{1 + \cos 2x}{2}$'
                    ]
                ],
                [
                    'topic_id_str' => 'pt-luong-giac-ly-thuyet',
                    'title' => 'Hàm số lượng giác và Phương trình lượng giác cơ bản',
                    'description' => 'Tuần hoàn, đồ thị và các công thức nghiệm của phương trình lượng giác cơ bản.',
                    'theory' => '### 1. Các hàm số lượng giác
* **Hàm số $y = \sin x$ và $y = \cos x$:** Tập xác định $\mathbb{R}$, tập giá trị $[-1; 1]$, tuần hoàn với chu kỳ $T = 2\pi$.
* **Hàm số $y = \tan x$:** Tập xác định $\mathbb{R} \setminus \{\frac{\pi}{2} + k\pi\}$, tuần hoàn với chu kỳ $T = \pi$.

### 2. Phương trình lượng giác cơ bản
* **Phương trình $\sin x = m$:** (Có nghiệm khi $|m| \le 1$)
  $x = \alpha + k2\pi$ hoặc $x = \pi - \alpha + k2\pi$ (với $\sin\alpha = m, k \in \mathbb{Z}$).
* **Phương trình $\cos x = m$:** (Có nghiệm khi $|m| \le 1$)
  $x = \pm\alpha + k2\pi$ (với $\cos\alpha = m, k \in \mathbb{Z}$).
* **Phương trình $\tan x = m$:**
  $x = \alpha + k\pi$ (với $\tan\alpha = m, k \in \mathbb{Z}$).',
                    'formulas' => [
                        'Nghiệm sin: $\sin x = \sin\alpha \Leftrightarrow x = \alpha + k2\pi \lor x = \pi - \alpha + k2\pi$',
                        'Nghiệm cos: $\cos x = \cos\alpha \Leftrightarrow x = \pm\alpha + k2\pi$',
                        'Nghiệm tan: $\tan x = \tan\alpha \Leftrightarrow x = \alpha + k\pi$'
                    ]
                ]
            ]
        ],
        [
            'chapter_key' => 'algebra_11_2',
            'title' => 'Chương 2: Dãy số. Cấp số cộng và cấp số nhân',
            'icon' => 'format_list_numbered',
            'grade' => 11,
            'topics' => [
                [
                    'topic_id_str' => 'day-so-co-ban',
                    'title' => 'Dãy số và Cấp số cộng',
                    'description' => 'Định nghĩa dãy số, tính chất tăng giảm và công thức số hạng tổng quát cấp số cộng.',
                    'theory' => '### 1. Dãy số
* Dãy số là một hàm số xác định trên tập hợp các số nguyên dương $\mathbb{N}^*$.
* Dãy số $(u_n)$ gọi là đồng biến (dãy số tăng) nếu $u_{n+1} > u_n$ với mọi $n \in \mathbb{N}^*$.

### 2. Cấp số cộng (CSC)
* Định nghĩa: Cấp số cộng là một dãy số trong đó, kể từ số hạng thứ hai, mỗi số hạng đều bằng số hạng đứng ngay trước nó cộng với một số không đổi $d$ (gọi là công sai):
  $u_n = u_{n-1} + d$.
* **Số hạng tổng quát:** $u_n = u_1 + (n - 1)d$.
* **Tổng $n$ số hạng đầu:** $S_n = \frac{n(u_1 + u_n)}{2} = \frac{n[2u_1 + (n-1)d]}{2}$.',
                    'formulas' => [
                        'Công sai CSC: $d = u_n - u_{n-1}$',
                        'Số hạng tổng quát CSC: $u_n = u_1 + (n-1)d$',
                        'Tổng n số hạng đầu: $S_n = \frac{n[2u_1 + (n-1)d]}{2}$',
                        'Tính chất ba số hạng liên tiếp: $u_k = \frac{u_{k-1} + u_{k+1}}{2}$'
                    ]
                ],
                [
                    'topic_id_str' => 'cap-so-nhan-co-ban',
                    'title' => 'Cấp số nhân',
                    'description' => 'Khái niệm, số hạng tổng quát, tính chất, tổng n số hạng đầu của cấp số nhân.',
                    'theory' => '### 1. Định nghĩa Cấp số nhân (CSN)
* Cấp số nhân là một dãy số trong đó, kể từ số hạng thứ hai, mỗi số hạng đều bằng số hạng đứng ngay trước nó nhân với một số không đổi $q$ (gọi là công bội):
  $u_n = u_{n-1} \cdot q$.

### 2. Số hạng tổng quát
* Số hạng tổng quát của CSN được tính bằng:
  $u_n = u_1 \cdot q^{n-1}$ (với $n \ge 2$).

### 3. Tổng $n$ số hạng đầu $S_n$
* Nếu $q \ne 1$, ta có: $S_n = u_1 \frac{1 - q^n}{1 - q}$.
* Nếu $q = 1$, ta có: $S_n = n u_1$.',
                    'formulas' => [
                        'Công bội CSN: $q = \frac{u_n}{u_{n-1}}$',
                        'Số hạng tổng quát CSN: $u_n = u_1 \cdot q^{n-1}$',
                        'Tổng n số hạng đầu: $S_n = u_1 \frac{1-q^n}{1-q}$ (với $q \ne 1$)',
                        'Tính chất ba số hạng: $u_k^2 = u_{k-1} \cdot u_{k+1}$'
                    ]
                ]
            ]
        ],
        [
            'chapter_key' => 'algebra_11_3',
            'title' => 'Chương 3: Giới hạn. Hàm số liên tục',
            'icon' => 'trending_flat',
            'grade' => 11,
            'topics' => [
                [
                    'topic_id_str' => 'gioi-han-day-ham',
                    'title' => 'Giới hạn của dãy số và hàm số',
                    'description' => 'Giới hạn hữu hạn, giới hạn vô cực và các quy tắc định lý tính giới hạn.',
                    'theory' => '### 1. Giới hạn của dãy số
* Dãy số $(u_n)$ có giới hạn hữu hạn $L$ khi $n \to +\infty$ nếu sai lệch $|u_n - L|$ nhỏ hơn một số dương bé tùy ý kể từ một số hạng nào đó trở đi. Kí hiệu $\lim u_n = L$.
* Các giới hạn cơ bản: $\lim \frac{1}{n} = 0$, $\lim \frac{1}{n^k} = 0$, $\lim q^n = 0$ (với $|q| < 1$).

### 2. Giới hạn của hàm số
* Cho hàm số $f(x)$ xác định trên khoảng $K$ chứa điểm $x_0$. Ta nói $\lim_{x \to x_0} f(x) = L$ nếu với mọi dãy số $(x_n)$ trong $K \setminus \{x_0\}$ mà $x_n \to x_0$, ta đều có $f(x_n) \to L$.
* **Định lý liên hệ:** Nếu $\lim f(x) = L$ và $\lim g(x) = M$, thì giới hạn của tổng, hiệu, tích, thương bằng tổng, hiệu, tích, thương tương ứng các giới hạn (với mẫu khác 0).',
                    'formulas' => [
                        'Giới hạn hữu hạn cơ bản: $\lim_{x \to x_0} x = x_0$',
                        'Giới hạn hằng số: $\lim_{x \to x_0} c = c$',
                        'Giới hạn vô cực của dãy: $\lim n^k = +\infty \text{ (với } k > 0\text{)}$'
                    ]
                ],
                [
                    'topic_id_str' => 'ham-so-lien-tuc-ly-thuyet',
                    'title' => 'Hàm số liên tục',
                    'description' => 'Liên tục tại một điểm, liên tục trên một khoảng, tính chất và các định lý giá trị trung gian.',
                    'theory' => '### 1. Khái niệm hàm số liên tục
* **Liên tục tại một điểm:** Hàm số $y = f(x)$ liên tục tại điểm $x_0$ thuộc tập xác định của nó nếu:
  $\lim_{x \to x_0} f(x) = f(x_0)$.
* Nếu hàm số không liên tục tại $x_0$, ta nói nó gián đoạn tại $x_0$.
* **Liên tục trên một khoảng:** Hàm số liên tục tại mọi điểm thuộc khoảng đó.

### 2. Định lý giá trị trung gian (Định lý Bolzano-Cauchy)
* Nếu hàm số $y = f(x)$ liên tục trên đoạn $[a; b]$ và $f(a) \cdot f(b) < 0$, thì tồn tại ít nhất một điểm $c \in (a; b)$ sao cho $f(c) = 0$.
* Ý nghĩa hình học: Đồ thị hàm số liên tục trên $[a; b]$ là một đường liền nét nối điểm $(a; f(a))$ và $(b; f(b))$. Nếu chúng nằm ở hai phía trục hoành, đồ thị bắt buộc phải cắt trục hoành ít nhất một lần.',
                    'formulas' => [
                        'Định nghĩa liên tục: $\lim_{x \to x_0} f(x) = f(x_0)$',
                        'Sự tồn tại nghiệm: $f(a) \cdot f(b) < 0 \Rightarrow \exists c \in (a; b): f(c) = 0$'
                    ]
                ]
            ]
        ],
        [
            'chapter_key' => 'geometry_11_4',
            'title' => 'Chương 4: Quan hệ song song trong không gian',
            'icon' => 'layers',
            'grade' => 11,
            'topics' => [
                [
                    'topic_id_str' => 'quan-he-song-song',
                    'title' => 'Đường thẳng và mặt phẳng song song',
                    'description' => 'Điểm, đường thẳng, mặt phẳng trong không gian, hai đường thẳng song song, chéo nhau và quan hệ song song.',
                    'theory' => '### 1. Khái niệm không gian hình học
* Các đối tượng cơ bản trong không gian: Điểm, Đường thẳng, Mặt phẳng.
* Ba cách xác định mặt phẳng: qua ba điểm không thẳng hàng, qua một đường thẳng và điểm ngoài đường thẳng, qua hai đường thẳng song song hoặc cắt nhau.

### 2. Vị trí tương đối của hai đường thẳng
* **Đồng phẳng:** Cắt nhau (có 1 điểm chung), Song song (cùng thuộc một mặt phẳng và không có điểm chung), Trùng nhau.
* **Không đồng phẳng (Chéo nhau):** Không cùng nằm trên bất kì mặt phẳng nào và không có điểm chung.

### 3. Đường thẳng và mặt phẳng song song
* Định lý: Nếu đường thẳng $d$ không nằm trong mặt phẳng $(\alpha)$ và song song với một đường thẳng $d\'$ nằm trong $(\alpha)$ thì $d$ song song với $(\alpha)$.
  $d \not\subset (\alpha) \text{ và } d \parallel d\' \subset (\alpha) \Rightarrow d \parallel (\alpha)$.',
                    'formulas' => [
                        'Điều kiện ĐT song song MP: $d \parallel d\' \subset (\alpha) \Rightarrow d \parallel (\alpha)$',
                        'Định lý giao tuyến: $(\alpha) \cap (\beta) = d$, $d_1 \subset (\alpha)$, $d_2 \subset (\beta)$, $d_1 \parallel d_2 \Rightarrow d \parallel d_1 \parallel d_2$'
                    ]
                ]
            ]
        ],
        [
            'chapter_key' => 'algebra_11_5',
            'title' => 'Chương 5: Các số đặc trưng đo xu thế trung tâm cho mẫu số liệu ghép nhóm',
            'icon' => 'insights',
            'grade' => 11,
            'topics' => [
                [
                    'topic_id_str' => 'so-lieu-ghep-nhom-co-ban',
                    'title' => 'Mẫu số liệu ghép nhóm và các số đặc trưng',
                    'description' => 'Khái niệm ghép nhóm, số trung bình, trung vị, tứ phân vị và mốt của mẫu số liệu ghép nhóm.',
                    'theory' => '### 1. Khái niệm mẫu số liệu ghép nhóm
* Mẫu số liệu ghép nhóm là mẫu số liệu được thu thập và trình bày dưới dạng các khoảng (nhóm) liên tiếp $[a_i; b_i)$.
* Mỗi nhóm $[a_i; b_i)$ có:
  * Tần số $n_i$ (số lượng số liệu thuộc khoảng đó)
  * Giá trị đại diện $x_i = \frac{a_i + b_i}{2}$ (trung điểm của khoảng).

### 2. Các số đặc trưng đo xu thế trung tâm
* **Số trung bình ($\bar{x}$):**
  $\bar{x} = \frac{n_1 x_1 + n_2 x_2 + \dots + n_k x_k}{n} = \frac{1}{n} \sum_{i=1}^k n_i x_i$
  (với $n = n_1 + n_2 + \dots + n_k$ là cỡ mẫu).
* **Trung vị ($M_e$):** Chia mẫu số liệu thành hai phần bằng nhau. Thường được xác định dựa trên tần số tích lũy.',
                    'formulas' => [
                        'Số trung bình mẫu ghép nhóm: $\bar{x} = \frac{1}{n} \sum_{i=1}^k n_i x_i$',
                        'Giá trị đại diện nhóm $[a_i; b_i]$: $x_i = \frac{a_i + b_i}{2}$'
                    ]
                ]
            ]
        ]
    ];

    // Helper function to seed chapters, topics, and formulas
    function seed_chapters($pdo, $chapters_data) {
        foreach ($chapters_data as $chap) {
            $stmt = $pdo->prepare("INSERT INTO chapters (chapter_key, title, icon, grade) VALUES (?, ?, ?, ?)");
            $stmt->execute([$chap['chapter_key'], $chap['title'], $chap['icon'], $chap['grade']]);
            $chapter_id = $pdo->lastInsertId();
            echo "Seeded chapter: {$chap['title']} (Grade {$chap['grade']})\n";

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

    $pdo->commit();
    echo "Comprehensive database migration and seeding successfully completed!\n";

} catch (PDOException $e) {
    $pdo->rollBack();
    echo "ERROR during migration: " . $e->getMessage() . "\n";
}
?>
