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

//Удаление черновика
 if (isset($_POST['delete_post']) && isset($_SESSION['user_id'])) {
     $postId = (int)$_POST['post_id'];
     
     $checkSql = "SELECT id_p FROM Posts WHERE id_p = $postId AND id_u = $userId AND isNote = 1 LIMIT 1";
     $checkResult = $conn->query($checkSql);
     
     if ($checkResult && $checkResult->num_rows > 0) {
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
        COALESCE(NULLIF(u.username, ''), u.login) AS author_name
    FROM Posts p
    JOIN Users u ON p.id_u = u.Id_U
    WHERE p.isNote = 1 AND p.id_u = {$userId}
    ORDER BY p.create_at DESC
";

$result = $conn->query($sql);
?>

<h2>Мои черновики</h2>

<div class="posts-wrapper">
    <?php if ($result && $result->num_rows > 0): ?>
        <div class="posts-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                    <a class="post-card-link" href="create_publication.php?draft_id=<?php echo (int)$row['id_p']; ?>">
                        <div class="post-card">
                            <div style="position: relative; display: inline-block;"></div>
                                <div class="post-card-title">
                                    <?php echo htmlspecialchars($row['title']); ?>
                                </div>
                                <div class="post-card-author">
                                    Автор: <?php echo htmlspecialchars($row['author_name']); ?>
                                </div>
                                
                                <form method="POST" style="position: absolute; top: 0; left: 0; z-index: 20;" onsubmit="return confirm('Вы точно хотите удалить этот черновик?');">
                                    <input type="hidden" name="post_id" value="<?php echo (int)$row['id_p']; ?>">
                                    <button type="submit" name="delete_post" class="delete-post-btn">Удалить</button>
                                </form>
                        </div>
                    </a>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p style="margin-bottom: 20px">У вас пока нет черновиков.</p>
    <?php endif; ?>
    <div style="display: flex; justify-content: center;">
        <a href="create_publication.php" class="new-publication-btn">Создать новую публикацию</a>
    </div>
</div>