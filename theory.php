<?php
require_once 'includes/functions.php';
include 'includes/header.php';

$grade = isset($_GET['grade']) ? intval($_GET['grade']) : 12;
if ($grade !== 10 && $grade !== 11 && $grade !== 12) {
    $grade = 12;
}

$allContent = get_all_content($grade);
$chapters = $allContent['chapters'];

$current_chapter = $_GET['chapter'] ?? '';
if (empty($current_chapter) || !isset($chapters[$current_chapter])) {
    $current_chapter = !empty($chapters) ? array_key_first($chapters) : '';
}
$chapter_data = $chapters[$current_chapter] ?? ['title' => 'Chưa có nội dung', 'topics' => []];
?>

<main class="container">
    <div style="padding: 2rem 0;">
        <a href="index.php"
            style="color: var(--accent); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
            <span class="material-icons-round">arrow_back</span> Quay lại Trang chủ
        </a>
        <h1 style="font-size: 3rem;"><?php echo $chapter_data['title']; ?></h1>
        <p style="color: var(--text-muted);">Tổng hợp lý thuyết & công thức Toán Lớp <?php echo $grade; ?></p>
    </div>

    <div style="display: grid; grid-template-columns: 300px 1fr; gap: 2rem;">
        <aside>
            <div class="content-wrapper" style="padding: 1.5rem; position: sticky; top: 100px;">
                <h4 style="margin-bottom: 1rem; color: var(--text-main);">Chương mục Lớp <?php echo $grade; ?></h4>
                <?php if (empty($chapters)): ?>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Chưa có chương học nào.</p>
                <?php else: ?>
                    <ul style="list-style: none;">
                        <?php foreach ($chapters as $key => $c): ?>
                            <li style="margin-bottom: 0.8rem;">
                                <a href="?grade=<?php echo $grade; ?>&chapter=<?php echo $key; ?>"
                                    style="color: <?php echo ($key == $current_chapter) ? 'var(--accent)' : 'var(--text-muted)'; ?>; text-decoration: none; font-size: 0.95rem;">
                                    <?php echo $c['title']; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </aside>

        <div>
            <?php if (empty($chapter_data['topics'])): ?>
                <div class="content-wrapper animate" style="text-align: center; padding: 4rem 2rem;">
                    <span class="material-icons-round" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;">auto_stories</span>
                    <h3>Chưa có bài học nào cho chương này</h3>
                    <p style="color: var(--text-muted);">Nội dung bài học và công thức đang được biên soạn.</p>
                </div>
            <?php else: ?>
                <?php foreach ($chapter_data['topics'] as $topic): ?>
                    <section id="<?php echo $topic['id']; ?>" class="content-wrapper animate">
                        <h2 style="margin-bottom: 1.5rem; color: var(--accent);"><?php echo $topic['title']; ?></h2>
                        <p style="color: var(--text-muted); font-style: italic; margin-bottom: 1rem;">
                            <?php echo $topic['description']; ?>
                        </p>

                        <?php if (!empty($topic['video_url'])): ?>
                            <?php 
                                $embed_url = '';
                                $url = $topic['video_url'];
                                // Parse YouTube ID
                                if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $url, $match)) {
                                    $embed_url = "https://www.youtube.com/embed/" . $match[1];
                                }
                            ?>
                            <?php if ($embed_url): ?>
                                <div class="video-card animate" style="margin-bottom: 2.5rem; border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.02); box-shadow: var(--shadow-lg);">
                                    <div style="padding: 1rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; gap: 0.5rem; background: rgba(255,255,255,0.01);">
                                        <span class="material-icons-round" style="color: var(--accent); font-size: 1.2rem;">play_circle</span>
                                        <span style="font-weight: 600; font-size: 0.95rem; color: var(--text-main);">Video Bài Giảng: <?php echo htmlspecialchars($topic['title']); ?></span>
                                    </div>
                                    <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden;">
                                        <iframe src="<?php echo $embed_url; ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <div class="theory-text" style="margin-bottom: 2rem;">
                            <?php echo parse_markdown($topic['theory']); ?>
                        </div>

                        <?php if (!empty($topic['formulas'])): ?>
                            <h4 style="margin-bottom: 1rem;">Công thức:</h4>
                            <?php foreach ($topic['formulas'] as $formula): ?>
                                <div class="formula-box">
                                    <?php echo $formula; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>