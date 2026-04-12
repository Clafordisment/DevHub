<?php
session_start();

$page_title = "DevHub | Личный кабинет";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

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
    $checkSql = "SELECT id_p, ownPrev FROM Posts WHERE id_p = $postId AND id_u = $userId LIMIT 1";
    $checkResult = $conn->query($checkSql);
    
    if ($checkResult && $checkResult->num_rows > 0) {
        $postData = $checkResult->fetch_assoc();
        if (!empty($postData['ownPrev']) && file_exists($postData['ownPrev'])) {
            unlink($postData['ownPrev']);
        }
        $deleteSql = "DELETE FROM Posts WHERE id_p = $postId";
        $conn->query($deleteSql);
    }
    header("Location: cabinet.php");
    exit;
}

$userSql = "SELECT login, username FROM Users WHERE Id_U = {$userId}";
$userResult = $conn->query($userSql);
$userData = $userResult->fetch_assoc();
$username = $userData['username'];
$_SESSION['username'] = $username;

$sql = "
    SELECT 
        p.id_p, 
        p.title, 
        p.ownPrev,
        COALESCE(NULLIF(u.username, ''), u.login) AS author_name
    FROM Posts p
    JOIN Users u ON p.id_u = u.Id_U
    WHERE p.isNote = 0 AND p.id_u = {$userId}
    ORDER BY p.create_at DESC 
";

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
        $posts[] = $row;
    }
}

require_once 'header.php';
?>

<div class="box" style="width: 1200px;">
    <h1>Личный кабинет</h1>
    <div style="display: flex; justify-content: center;text-align: center; margin-bottom: 20px;">
        <?php if (!empty($username)): ?>
            <p style="font-size: xx-large; margin: 5px; width: 400px;"><?= htmlspecialchars($username) ?></p>
        <?php endif; ?>
    </div>
    
    <div style="margin: 20px; display: flex; justify-content: center; text-align: center">
        <a href="edit_prof.php" class="button" style="width: 400px;">Редактировать профиль</a>
    </div>

    <hr>

    <div class="post-wrapper">
        <h2>Публикации пользователя</h2>
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
                            <form method="POST" style="position: absolute; top: 10px; left: 10px; z-index: 20;" onsubmit="return confirm('Вы точно хотите удалить этот пост?');">
                                <input type="hidden" name="post_id" value="<?php echo (int)$row['id_p']; ?>">
                                <button type="submit" name="delete_post" class="delete-post-btn">Удалить</button>
                            </form>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Пока нет опубликованных публикаций.</p>
        <?php endif; ?>
    </div>
</div>