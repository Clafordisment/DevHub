<?php
session_start();

$page_title = "DevHub | Публикация";
require_once 'header.php';

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "DevHub_mainDB";

$conn = new mysqli($host, $user, $pass, $dbname);
$conn->set_charset("utf8");

$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$post = null;

if ($postId > 0) {
    $sql = "
        SELECT 
            p.id_p,
            p.title,
            p.content,
            COALESCE(NULLIF(u.username, ''), u.login) AS author_name,
            p.create_at
        FROM Posts p
        JOIN Users u ON p.id_u = u.Id_U
        WHERE p.id_p = {$postId} AND p.isNote = 0
        LIMIT 1
    ";

    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $post = $result->fetch_assoc();
    }
}

$comments = [];
if ($postId > 0) {
    $commentsSql = "
        SELECT 
            c.id_c,
            c.content,
            c.issued_rate,
            c.created_at,
            COALESCE(NULLIF(u.username, ''), u.login) AS comment_author,
            u.Id_U as author_id
        FROM Comments c
        JOIN Users u ON c.id_u = u.Id_U
        WHERE c.id_p = {$postId}
        ORDER BY c.created_at DESC
    ";
    $commentsResult = $conn->query($commentsSql);
    if ($commentsResult) {
        while ($row = $commentsResult->fetch_assoc()) {
            $comments[] = $row;
        }
    }
}

$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
?>

<div id="post-data" 
     data-post-id="<?php echo $postId; ?>" 
     data-current-user-id="<?php echo $currentUserId; ?>"
     style="display: none;"></div>

<div class="box" style="width: 1200px;">
    <?php if ($post): ?>
        <div class="post-full-header">
            <a href="index.php" class="back-button">
                &#8592; Назад
            </a>
            <h1 class="post-full-title">
                <?php echo htmlspecialchars($post['title']); ?>
            </h1>
        </div>
        <div class="post-full-author">
            Автор: <?php echo htmlspecialchars($post['author_name']); ?>
        </div>
        <div class="post-full-content">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>
    <?php else: ?>
        <div class="msg err">
            Публикация не найдена.
        </div>
        <div style="margin-top: 20px; text-align: center;">
            <a href="index.php" class="back-link">&#8592; Вернуться на главную</a>
        </div>
    <?php endif; ?>
</div>

<?php if ($post): ?>
<div class="box" style="width: 1200px; margin-top: 0; border-radius: 12px;">
    <h2 style="text-align: left; margin-bottom: 25px;">Комментарии</h2>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <form class="comment-form" id="comment-form">
            <label>Оставить комментарий</label>
            <textarea name="comment_content" class="comment-input" placeholder="Введите ваш комментарий..." required></textarea>
            <button type="submit" name="add_comment" class="comment-submit">Отправить</button>
        </form>
        <hr>
    <?php else: ?>        
        <div class="login-to-comment">
            <a href="login.php" style="color: #b0b0b0;">Войдите</a>, чтобы оставить комментарий
        </div>
    <?php endif; ?>
    
    <div class="comments-wrapper">
        <?php if (count($comments) > 0): ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment-card" data-comment-id="<?php echo $comment['id_c']; ?>">
                    <div class="comment-header">
                        <span class="comment-author"><?php echo htmlspecialchars($comment['comment_author']); ?></span>
                        <span class="comment-date">
                            <?php echo isset($comment['created_at']) ? date('d.m.Y H:i', strtotime($comment['created_at'])) : 'только что'; ?>
                        </span>
                    </div>
                    <p class="comment-content">
                        <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                    </p>
                    <?php if ($currentUserId == $comment['author_id']): ?>
                        <div class="comment-actions">
                            <button class="comment-delete-btn delete-comment-btn" data-comment-id="<?php echo $comment['id_c']; ?>">
                                Удалить
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-comments">Пока нет комментариев. Будьте первым!</p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script src="comments_ajax.js"></script>