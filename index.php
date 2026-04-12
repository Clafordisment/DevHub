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

$catgSql = "SELECT id_PType, name FROM posts_catg ORDER BY id_PType";
$catgResult = $conn->query($catgSql);
$categories = [];
if ($catgResult) {
    while ($row = $catgResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : 0;

if ($selectedCategory > 0) {
    $sql = "
        SELECT 
            p.id_p, 
            p.title, 
            COALESCE(NULLIF(u.username, ''), u.login) AS author_name
        FROM Posts p
        JOIN Users u ON p.id_u = u.Id_U
        WHERE p.isNote = 0 AND p.id_type = $selectedCategory
        ORDER BY p.create_at DESC 
    ";
} else {
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
}

$result = $conn->query($sql);
?>

<div class="box">

    <h2>Публикации DevHub</h2>

<div class="category-buttons">
    <?php foreach ($categories as $cat): ?>
        <?php
        $isActive = ($selectedCategory == $cat['id_PType']);
        $targetCategory = $isActive ? 0 : $cat['id_PType'];
        ?>
        <a href="index.php?category=<?php echo $targetCategory; ?>" 
           class="category-btn <?php echo $isActive ? 'active' : ''; ?>">
            <?php echo htmlspecialchars($cat['name']); ?>
        </a>
    <?php endforeach; ?>
</div>

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
            <p>По данной категории публикаций нет.</p>
        <?php endif; ?>
    </div>
</div>