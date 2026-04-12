<?php
session_start();

$page_title = "DevHub | Черновики";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'header.php';
require_once 'config.php';

$conn = new mysqli(
    $config['db_host'],
    $config['db_user'],
    $config['db_pass'],
    $config['db_name']
);
$conn->set_charset("utf8");

$userId = (int)$_SESSION['user_id'];

if (isset($_POST['delete_post']) && isset($_SESSION['user_id'])) {
    $postId = (int)$_POST['post_id'];
    
    $checkSql = "SELECT id_p, ownPrev FROM Posts WHERE id_p = $postId AND id_u = $userId AND isNote = 1 LIMIT 1";
    $checkResult = $conn->query($checkSql);
    
    if ($checkResult && $checkResult->num_rows > 0) {
        $postData = $checkResult->fetch_assoc();
        if (!empty($postData['ownPrev']) && file_exists($postData['ownPrev'])) {
            unlink($postData['ownPrev']);
        }
        $deleteSql = "DELETE FROM Posts WHERE id_p = $postId";
        $conn->query($deleteSql);
    }
    header("Location: drafts.php");
    exit;
}

$sql = "
    SELECT 
        p.id_p,
        p.title,
        p.ownPrev,
        COALESCE(NULLIF(u.username, ''), u.login) AS author_name
    FROM Posts p
    JOIN Users u ON p.id_u = u.Id_U
    WHERE p.isNote = 1 AND p.id_u = {$userId}
    ORDER BY p.create_at DESC
";

$result = $conn->query($sql);

$drafts = [];
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
            LIMIT 3
        ";
        $tagsResult = $conn->query($tagsSql);
        $tags = [];
        if ($tagsResult) {
            while ($tagRow = $tagsResult->fetch_assoc()) {
                $tags[] = $tagRow;
            }
        }
        
        $row['tags'] = $tags;
        $drafts[] = $row;
    }
}
?>

<h2>Мои черновики</h2>

<div class="posts-wrapper">
    <?php if (count($drafts) > 0): ?>
        <div class="posts-grid">
            <?php foreach ($drafts as $row): ?>
                <a class="post-card-link" href="create_publication.php?draft_id=<?php echo (int)$row['id_p']; ?>">
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
                        <form method="POST" style="position: absolute; top: 10px; left: 10px; z-index: 20;" onsubmit="return confirm('Вы точно хотите удалить этот черновик?');">
                            <input type="hidden" name="post_id" value="<?php echo (int)$row['id_p']; ?>">
                            <button type="submit" name="delete_post" class="delete-post-btn">Удалить</button>
                        </form>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="margin-bottom: 20px">У вас пока нет черновиков.</p>
    <?php endif; ?>
    <div style="display: flex; justify-content: center;">
        <a href="create_publication.php" class="new-publication-btn">Создать новую публикацию</a>
    </div>
</div>