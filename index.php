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
            p.ownPrev,
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
            p.ownPrev,
            COALESCE(NULLIF(u.username, ''), u.login) AS author_name
        FROM Posts p
        JOIN Users u ON p.id_u = u.Id_U
        WHERE p.isNote = 0
        ORDER BY p.create_at DESC 
    ";
}

$result = $conn->query($sql);

$posts = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $postId = $row['id_p'];
        
        $tagsSql = "
            SELECT t.name, tc.color_code
            FROM tags_posts tp
            JOIN Tags t ON tp.id_t = t.id_t
            JOIN tags_catg tc ON t.id_catg = tc.id_catg
            WHERE tp.id_p = $postId
            ORDER BY tc.sort_order, t.name
        ";
        $tagsResult = $conn->query($tagsSql);
        $allTags = [];
        if ($tagsResult) {
            while ($tagRow = $tagsResult->fetch_assoc()) {
                $allTags[] = $tagRow;
            }
        }
        
        $tags = [];
        if (count($allTags) > 0) {
            shuffle($allTags); 
            $tags = array_slice($allTags, 0, 3);  
        }
        
        $row['tags'] = $tags;
        $posts[] = $row;
    }
}
?>
<div class="container" style="background-color: #1a1a1a00; box-shadow: none; animation: none; margin: 0px;">
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
        <?php if (count($posts) > 0): ?>
            <div class="posts-grid">
                <?php foreach ($posts as $row): ?>
                    <a class="post-card-link" href="post.php?id=<?php echo (int)$row['id_p']; ?>">
                        <div class="post-card <?php echo empty($row['ownPrev']) ? 'no-image' : ''; ?>">
                            <?php if (!empty($row['ownPrev'])): ?>
                                <img class="post-card-image" src="<?php echo htmlspecialchars($row['ownPrev']); ?>" alt="Изображение поста">
                            <?php endif; ?>
                            <div class="post-card-content">
                                <div class="post-card-title">
                                    <?php echo htmlspecialchars($row['title']); ?>
                                </div>
                                <div class="post-card-author">
                                    Автор: <?php echo htmlspecialchars($row['author_name']); ?>
                                </div>
                                <?php if (!empty($row['tags'])): ?>
                                    <div class="post-card-tags">
                                        <?php foreach ($row['tags'] as $tag): ?>
                                            <span class="post-card-tag" style="border-color: <?php echo $tag['color_code']; ?>; color: <?php echo $tag['color_code']; ?>;">
                                                <?php echo htmlspecialchars($tag['name']); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['id_u']): ?>
                                <form method="POST" style="position: absolute; top: 10px; left: 10px; z-index: 20;" onsubmit="return confirm('Вы точно хотите удалить этот пост?');">
                                    <input type="hidden" name="post_id" value="<?php echo (int)$row['id_p']; ?>">
                                    <button type="submit" name="delete_post" class="delete-post-btn">Удалить</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>По данной категории публикаций нет.</p>
        <?php endif; ?>
    </div>
</div>
</div>