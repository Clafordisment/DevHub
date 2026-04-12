<?php
$page_title = "DevHub | Главная";
require_once 'header.php';

require_once 'config.php';

$conn = new mysqli(
    $config['db_host'],
    $config['db_user'],
    $config['db_pass'],
    $config['db_name']
);
$conn->set_charset("utf8");

$sql = "
    SELECT 
        p.id_p, 
        p.title, 
        COALESCE(NULLIF(u.username, ''), u.login) AS author_name
    FROM Posts p
    JOIN Users u ON p.id_u = u.Id_U
    WHERE p.isNote = 0
    ORDER BY p.create_at DESC 
";

$result = $conn->query($sql);
?>

<h2>Публикации DevHub</h2>

<div class="posts-wrapper">
    <?php if ($result && $result->num_rows > 0): ?>
        <div class="posts-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <a class="post-card-link" href="post.php?id=<?php echo (int)$row['id_p']; ?>">
                    <div class="post-card">
                        <div class="post-card-title">
                            <?php echo htmlspecialchars($row['title']); ?>
                        </div>
                        <div class="post-card-author">
                            Автор: <?php echo htmlspecialchars($row['author_name']); ?>
                        </div>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>Пока нет опубликованных публикаций.</p>
    <?php endif; ?>
</div>