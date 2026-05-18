<?php
$chapters = [
    'calculus' => [
        'title' => 'Giải tích 12',
        'icon' => 'functions',
        'topics' => [
            [
                'id' => 'dao-ham',
                'title' => 'Chương 1: Ứng dụng Đạo hàm',
                'description' => 'Khảo sát sự biến thiên và vẽ đồ thị hàm số.',
                'theory' => 'Khảo sát hàm số $y=f(x)$:\n1. Tập xác định.\n2. Sự biến thiên: \n   - Tính $f\'(x)$.\n   - Tìm nghiệm $f\'(x)=0$ hoặc nơi không xác định.\n   - Lập bảng biến thiên.\n3. Đồ thị: Tìm tâm đối xứng, tiệm cận, giao với trục tọa độ.',
                'formulas' => [
                    'Cực trị: $f\'(x_0)=0$ và $f\'\'(x_0) < 0 \Rightarrow$ Cực đại.',
                    'Tiệm cận ngang: $\lim_{x \to \infty} f(x) = y_0$.',
                    'Tiệm cận đứng: $\lim_{x \to x_0^+} f(x) = \infty$.'
                ]
            ],
            [
                'id' => 'mu-logarit',
                'title' => 'Chương 2: Mũ và Logarit',
                'description' => 'Hàm số lũy thừa, hàm số mũ và hàm số logarit.',
                'theory' => 'Logarit cơ số $a$ của $b$: $\log_a b = \alpha \Leftrightarrow a^\alpha = b$.\nĐiều kiện: $0 < a \neq 1, b > 0$.',
                'formulas' => [
                    '$\log_a (bc) = \log_a b + \log_a c$',
                    '$\log_a (b^n) = n \log_a b$',
                    '$(a^x)\' = a^x \ln a$',
                    '$(\log_a x)\' = \frac{1}{x \ln a}$'
                ]
            ],
            [
                'id' => 'tich-phan',
                'title' => 'Chương 3: Nguyên hàm & Tích phân',
                'description' => 'Tính diện tích hình phẳng và thể tích khối tròn xoay.',
                'theory' => 'Công thức Newton-Leibniz: $\int_a^b f(x)dx = F(b) - F(a)$.\nỨng dụng: Diện tích $S = \int_a^b |f(x)|dx$. Thể tích $V = \pi \int_a^b f^2(x)dx$.',
                'formulas' => [
                    '$\int \sin x dx = -\cos x + C$',
                    '$\int e^x dx = e^x + C$',
                    '$\int \frac{1}{x} dx = \ln|x| + C$'
                ]
            ],
            [
                'id' => 'so-phuc',
                'title' => 'Chương 4: Số phức',
                'description' => 'Dạng đại số, dạng lượng giác và các phép toán.',
                'theory' => 'Số phức $z = a + bi$ ($a, b \in \mathbb{R}, i^2 = -1$).\n$a$: phần thực, $b$: phần ảo.\nSố phức liên hợp $\bar{z} = a - bi$. Môđun $|z| = \sqrt{a^2 + b^2}$.',
                'formulas' => [
                    '$z_1 + z_2 = (a+c) + (b+d)i$',
                    '$z_1 \cdot z_2 = (ac - bd) + (ad + bc)i$'
                ]
            ]
        ]
    ],
    'geometry' => [
        'title' => 'Hình học 12',
        'icon' => 'architecture',
        'topics' => [
            [
                'id' => 'khoi-da-dien',
                'title' => 'Chương 1: Khối đa diện',
                'description' => 'Thể tích khối lăng trụ, khối chóp.',
                'theory' => 'Thể tích khối chóp: $V = \frac{1}{3} B \cdot h$.\nThể tích khối lăng trụ: $V = B \cdot h$.\n(B: diện tích đáy, h: chiều cao).',
                'formulas' => [
                    'Chóp đều: Đáy là đa giác đều, chân đường cao trùng tâm đáy.',
                    'Lăng trụ đứng: Cạnh bên vuông góc với đáy.'
                ]
            ],
            [
                'id' => 'mat-tron-xoay',
                'title' => 'Chương 2: Mặt nón, trụ, cầu',
                'description' => 'Diện tích xung quanh, toàn phần và thể tích.',
                'theory' => 'Mặt cầu: $S = 4\pi R^2, V = \frac{4}{3}\pi R^3$.\nMặt nón: $S_{xq} = \pi R l, V = \frac{1}{3}\pi R^2 h$.\nMặt trụ: $S_{xq} = 2\pi R l, V = \pi R^2 h$.',
                'formulas' => [
                    'Đường sinh nón: $l^2 = h^2 + R^2$.',
                    'Thiết diện qua trục: Hình chữ nhật (trụ) hoặc Tam giác cân (nón).'
                ]
            ],
            [
                'id' => 'oxyz',
                'title' => 'Chương 3: Tọa độ trong không gian',
                'description' => 'Hệ tọa độ Oxyz, mặt phẳng, đường thẳng, mặt cầu.',
                'theory' => 'Phương trình mặt phẳng $(P): Ax + By + Cz + D = 0$.\nPhương trình mặt cầu tâm $I(a,b,c): (x-a)^2 + (y-b)^2 + (z-c)^2 = R^2$.',
                'formulas' => [
                    'Tích vô hướng: $\vec{a} \cdot \vec{b} = a_1b_1 + a_2b_2 + a_3b_3$.',
                    'Góc giữa 2 mặt phẳng: $\cos \varphi = \frac{|\vec{n_1} \cdot \vec{n_2}|}{|\vec{n_1}| \cdot |\vec{n_2}|}$.'
                ]
            ]
        ]
    ]
];

$quizzes = [
    [
        'question' => 'Nghiệm của phương trình $\log_2 (x-1) = 3$ là:',
        'options' => ['$x = 7$', '$x = 8$', '$x = 9$', '$x = 10$'],
        'answer' => 2
    ],
    [
        'question' => 'Môđun của số phức $z = 3 - 4i$ là:',
        'options' => ['$5$', '$7$', '$\sqrt{7}$', '$25$'],
        'answer' => 0
    ],
    [
        'question' => 'Thể tích khối cầu có bán kính $R=3$ là:',
        'options' => ['$36\pi$', '$108\pi$', '$12\pi$', '$27\pi$'],
        'answer' => 0
    ],
    [
        'question' => 'Nguyên hàm của $f(x) = e^{2x}$ là:',
        'options' => ['$e^{2x} + C$', '$\frac{1}{2}e^{2x} + C$', '$2e^{2x} + C$', '$e^x + C$'],
        'answer' => 1
    ],
    [
        'question' => 'Tâm của mặt cầu $(x-1)^2 + y^2 + (z+2)^2 = 9$ là:',
        'options' => ['$I(1, 0, 2)$', '$I(-1, 0, -2)$', '$I(1, 0, -2)$', '$I(1, 1, 2)$'],
        'answer' => 2
    ]
];
?>
