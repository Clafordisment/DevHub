<?php
session_start();

$page_title = "DevHub | Личный кабинет";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "DevHub_mainDB";

$conn = new mysqli($host, $user, $pass, $dbname);
$conn->set_charset("utf8");

$userId = (int)$_SESSION['user_id'];

 if (isset($_POST['delete_post']) && isset($_SESSION['user_id'])) 
    {     
    $postId = (int)$_POST['post_id'];     
     $checkSql = "SELECT id_p FROM Posts WHERE id_p = $postId AND id_u = $userId LIMIT 1";
     $checkResult = $conn->query($checkSql);
     
     if ($checkResult && $checkResult->num_rows > 0) {
         $deleteSql = "DELETE FROM Posts WHERE id_p = $postId";
         $conn->query($deleteSql);
     }
     header("Location: cabinet.php");
     exit;
}

$userSql = "SELECT login, username FROM Users WHERE Id_U = {$userId}";

$userResult = $conn -> query($userSql);
$userData = $userResult->fetch_assoc();
$username = $userData['username'];

$_SESSION['username'] = $username;

$sql = "
    SELECT 
        p.id_p, 
        p.title, 
        COALESCE(NULLIF(u.username, ''), u.login) AS author_name
    FROM Posts p
    JOIN Users u ON p.id_u = u.Id_U
    WHERE p.isNote = 0 AND p.id_u = {$userId}
    ORDER BY p.create_at DESC 
";

$result = $conn->query($sql);
if (!$result) {
    die("Ошибка запроса: " . $conn->error);
}

require_once 'header.php';
?>

<div class="box" style="width: 1200px;">
    <h1>Личный кабинет</h1>
    <div style="display: flex; justify-content: center;text-align: center; margin-bottom: 20px;">
        <!-- <p style="font-size: x-large; margin: 5px;">Логин: <?= htmlspecialchars($_SESSION['login']) ?></p> -->
        <?php if (!empty($username)): ?>
            <p style="font-size: xx-large;  margin: 5px; width: 400px;"><?= htmlspecialchars($username) ?></p>
        <?php endif; ?>
    </div>
    
    <div style="margin: 20px; display: flex; justify-content: center; text-align: center">
        <a href="edit_prof.php" class="button" style="width: 400px;">Редактировать профиль</a>
    </div>

    <hr>

    <div class="post-wrapper">
        <h2>Публикации пользователя</h2>
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="posts-grid">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <a class="post-card-link" href="post.php?id=<?php echo (int)$row['id_p']; ?>">
                            <div class="post-card">
                                <div style="position:relative; display: inline-block;"></div>
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
                <p>Пока нет опубликованных публикаций.</p>
            <?php endif; ?>
    </div>
</div>