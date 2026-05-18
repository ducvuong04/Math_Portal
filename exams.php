<?php
require_once 'includes/functions.php';

if (!is_logged_in()) redirect('login.php');

include 'includes/header.php';

$is_teacher = is_teacher();

$grade_param = isset($_GET['grade']) ? $_GET['grade'] : '12';
$is_thpt = ($grade_param === 'thpt');

if (!$is_thpt) {
    $grade = intval($grade_param);
    if (!in_array($grade, [10, 11, 12])) $grade = 12;
    // For normal grades, filter by grade and exclude THPT categories
    $stmt = $pdo->prepare("SELECT * FROM exams WHERE grade = ? AND category IN ('midterm', 'final') ORDER BY created_at DESC");
    $stmt->execute([$grade]);
} else {
    $grade = 'THPT';
    // For THPT, fetch all THPT categories (we can ignore grade column as we set it to 12 in backend anyway)
    $stmt = $pdo->prepare("SELECT * FROM exams WHERE category LIKE 'thpt_%' ORDER BY created_at DESC");
    $stmt->execute();
}

$exams = $stmt->fetchAll();

// Group exams by category
$grouped_exams = [];
foreach ($exams as $e) {
    $cat = $e['category'];
    if (!isset($grouped_exams[$cat])) {
        $grouped_exams[$cat] = [];
    }
    $grouped_exams[$cat][] = $e;
}

if ($is_thpt) {
    $tabs = [
        'thpt_mock' => 'Đề thi thử THPT',
        'thpt_official' => 'Đề THPT chính thức',
        'thpt_dgnl' => 'Đánh giá năng lực',
        'thpt_prep' => 'Tài liệu ôn thi'
    ];
} else {
    $tabs = [
        'midterm' => 'Đề thi Giữa kì',
        'final' => 'Đề thi Cuối kì'
    ];
}
?>

<main class="container">
    <section class="hero animate" style="padding: 4rem 0 2rem;">
        <h1>Kho Đề Thi <?php echo $is_thpt ? 'THPT Quốc Gia' : 'Lớp ' . $grade; ?></h1>
        <p>Tổng hợp các đề thi thử, đề kiểm tra và đáp án chi tiết.</p>
        <?php if ($is_teacher): ?>
            <a href="admin/manage_exams.php" class="btn btn-primary" style="margin-top: 1rem;">
                <span class="material-icons-round">settings</span> Quản lý đề thi
            </a>
        <?php endif; ?>
    </section>

    <!-- Tabs Navigation -->
    <div class="tabs-container animate" style="display: flex; gap: 1rem; border-bottom: 2px solid var(--glass-border); margin-bottom: 2rem; overflow-x: auto; padding-bottom: 0.5rem;">
        <?php 
        $first_tab = true;
        foreach ($tabs as $key => $label): 
            $active = $first_tab ? 'active' : '';
        ?>
            <button class="tab-btn <?php echo $active; ?>" data-target="tab-<?php echo $key; ?>" style="background: none; border: none; color: <?php echo $first_tab ? 'var(--primary)' : 'var(--text-muted)'; ?>; font-weight: 600; font-size: 1.1rem; padding: 0.5rem 1rem; cursor: pointer; transition: all 0.3s ease; white-space: nowrap; border-bottom: 2px solid <?php echo $first_tab ? 'var(--primary)' : 'transparent'; ?>; margin-bottom: -2px;">
                <?php echo $label; ?>
            </button>
        <?php 
        $first_tab = false;
        endforeach; 
        ?>
    </div>

    <!-- Tabs Content -->
    <?php 
    $first_content = true;
    foreach ($tabs as $key => $label): 
        $display = $first_content ? 'block' : 'none';
        $items = $grouped_exams[$key] ?? [];
    ?>
        <div id="tab-<?php echo $key; ?>" class="tab-content animate" style="display: <?php echo $display; ?>;">
            <div class="grid">
                <?php if (empty($items)): ?>
                    <div class="card" style="grid-column: 1/-1; text-align: center; background: var(--glass-bg);">
                        <p style="color: var(--text-muted);">Hiện chưa có đề thi nào trong mục này.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($items as $e): ?>
                        <div class="card" style="transition: transform 0.3s ease, box-shadow 0.3s ease;">
                            <?php 
                                $is_q_pdf = strtolower(pathinfo($e['question_file'], PATHINFO_EXTENSION)) === 'pdf';
                            ?>
                            <?php if ($is_q_pdf): ?>
                                <div style="position: relative; width: 100%; aspect-ratio: 1/1.414; border-radius: 8px; overflow: hidden; background: #fff; margin-bottom: 1.5rem; border: 1px solid var(--glass-border);">
                                    <iframe src="<?php echo htmlspecialchars($e['question_file']); ?>#toolbar=0&navpanes=0&scrollbar=0&view=FitH" style="position: absolute; top: 0; left: 0; width: 250%; height: 250%; border: none; pointer-events: none; transform: scale(0.4); transform-origin: top left;"></iframe>
                                    <a href="<?php echo htmlspecialchars($e['question_file']); ?>" target="_blank" style="position: absolute; inset: 0; z-index: 10; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0); transition: background 0.3s; text-decoration: none;" onmouseover="this.style.background='rgba(0,0,0,0.5)'; this.children[0].style.opacity='1'; this.children[0].style.transform='scale(1)';" onmouseout="this.style.background='rgba(0,0,0,0)'; this.children[0].style.opacity='0'; this.children[0].style.transform='scale(0.8)';">
                                        <div style="background: var(--primary); color: white; padding: 0.5rem 1rem; border-radius: 20px; font-weight: bold; opacity: 0; transform: scale(0.8); transition: all 0.3s ease; display: flex; align-items: center; gap: 5px; box-shadow: 0 4px 12px rgba(0,0,0,0.4);">
                                            <span class="material-icons-round" style="font-size: 18px;">visibility</span> Xem đề thi
                                        </div>
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="card-icon" style="background: rgba(245, 158, 11, 0.1);">
                                    <span class="material-icons-round" style="color: var(--accent);">assignment</span>
                                </div>
                            <?php endif; ?>
                            
                            <h3><?php echo htmlspecialchars($e['title']); ?></h3>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem;">
                                Ngày đăng: <?php echo date('d/m/Y', strtotime($e['created_at'])); ?>
                            </p>

                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <?php 
                                    $q_ext = pathinfo($e['question_file'], PATHINFO_EXTENSION);
                                    $q_name = $e['original_q_name'] ?: $e['title'];
                                    if (pathinfo($q_name, PATHINFO_EXTENSION) !== $q_ext) {
                                        $q_name .= '.' . $q_ext;
                                    }
                                ?>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                    <a href="<?php echo htmlspecialchars($e['question_file']); ?>" target="_blank" class="btn btn-outline" style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 0.6rem;">
                                        <span class="material-icons-round" style="font-size: 18px;">visibility</span> Xem trước
                                    </a>
                                    <a href="download.php?file=<?php echo urlencode($e['question_file']); ?>&name=<?php echo urlencode($q_name); ?>" class="btn btn-primary" style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 0.6rem;">
                                        <span class="material-icons-round" style="font-size: 18px;">file_download</span> Tải về
                                    </a>
                                </div>
                                
                                <?php if ($e['answer_file']): ?>
                                    <?php 
                                        $a_ext = pathinfo($e['answer_file'], PATHINFO_EXTENSION);
                                        $a_name = $e['original_a_name'] ?: 'Dap-an-' . $e['title'];
                                        if (pathinfo($a_name, PATHINFO_EXTENSION) !== $a_ext) {
                                            $a_name .= '.' . $a_ext;
                                        }
                                    ?>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                        <a href="<?php echo htmlspecialchars($e['answer_file']); ?>" target="_blank" class="btn btn-outline" style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 0.6rem; opacity: 0.85;">
                                            <span class="material-icons-round" style="font-size: 18px;">visibility</span> Xem đáp án
                                        </a>
                                        <a href="download.php?file=<?php echo urlencode($e['answer_file']); ?>&name=<?php echo urlencode($a_name); ?>" class="btn btn-outline" style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 0.6rem;">
                                            <span class="material-icons-round" style="font-size: 18px;">description</span> Tải đáp án
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <button class="btn btn-outline" disabled style="opacity: 0.5; cursor: not_allowed; padding: 0.6rem;">Chưa có đáp án</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php 
    $first_content = false;
    endforeach; 
    ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    function activateTab(targetId) {
        // Remove active from all
        tabBtns.forEach(b => {
            b.style.color = 'var(--text-muted)';
            b.style.borderBottomColor = 'transparent';
        });
        tabContents.forEach(c => c.style.display = 'none');

        // Set active to current
        const btn = document.querySelector(`.tab-btn[data-target="${targetId}"]`);
        if (btn) {
            btn.style.color = 'var(--primary)';
            btn.style.borderBottomColor = 'var(--primary)';
        }
        
        const targetElement = document.getElementById(targetId);
        if (targetElement) {
            targetElement.style.display = 'block';
        }
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            activateTab(btn.getAttribute('data-target'));
        });
    });

    // Handle URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const initialTab = urlParams.get('tab');
    if (initialTab) {
        activateTab('tab-' + initialTab);
    }
});
</script>

<?php include 'includes/footer.php'; ?>
