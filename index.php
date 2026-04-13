<?php
$page_title = "DevHub | Главная";
require_once 'header.php';
require_once 'config.php';
require_once 'search_engine_mdl.php';

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

$searchEngine = new SearchEngine($conn);
$posts = $searchEngine->search('', ['category' => $selectedCategory]);

$conn->close();
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
                                    <?php echo htmlspecialchars($row['author_name']); ?>
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
            <p>По вашему запросу ничего не найдено.</p>
        <?php endif; ?>
    </div>
</div>

<script src="JS/search_ui.js"></script>