<?php
session_start();
require_once '../config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireAdminAuth();

$active_page = 'comments';

$conn = getDbConnection($config);

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $commentId = (int)$_GET['delete'];
    $conn->query("DELETE FROM Comments WHERE id_c = $commentId");
    header('Location: adm_comments.php?msg=deleted');
    exit;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "
    SELECT c.id_c, c.content, c.created_at, c.likes_count,
           COALESCE(NULLIF(u.username, ''), u.login) AS author_name,
           u.Id_U as author_id,
           p.id_p, p.title as post_title
    FROM Comments c
    JOIN Users u ON c.id_u = u.Id_U
    JOIN Posts p ON c.id_p = p.id_p
    WHERE 1=1
";

if ($search) {
    $searchEscaped = $conn->real_escape_string($search);
    $sql .= " AND (c.content LIKE '%$searchEscaped%' OR u.login LIKE '%$searchEscaped%' OR p.title LIKE '%$searchEscaped%')";
}

$sql .= " ORDER BY c.created_at DESC";

$result = $conn->query($sql);
$comments = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>AdminPanel | Управление комментариями</title>
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-wrapper">
        <nav class="admin-sidebar">
            <div class="admin-sidebar-header">
                <h3>DevHub Admin</h3>
            </div>
            <ul class="admin-sidebar-menu">
                <li><a href="adm_index.php" <?php echo ($active_page == 'dashboard') ? 'class="active"' : ''; ?>><img src="../icons/anim/ic-Graph.gif" alt="📊" class="nav-icon" style="width: 35px; height: 35px;"> Главная</a></li>
                <li><a href="adm_users.php" <?php echo ($active_page == 'users') ? 'class="active"' : ''; ?>><img src="../icons/anim/ic-Users.gif" alt="👥" class="nav-icon" style="width: 35px; height: 35px;"> Пользователи</a></li>
                <li><a href="adm_posts.php" <?php echo ($active_page == 'posts') ? 'class="active"' : ''; ?>><img src="../icons/anim/ic-Post.gif" alt="📝" class="nav-icon" style="width: 35px; height: 35px;"> Посты</a></li>
                <li><a href="adm_tags.php" <?php echo ($active_page == 'tags') ? 'class="active"' : ''; ?>><img src="../icons/static/ic-Tag_framed.png" alt="🏷️" class="nav-icon" style="width: 35px; height: 35px;"> Теги</a></li>
                <li><a href="adm_comments.php" <?php echo ($active_page == 'comments') ? 'class="active"' : ''; ?>><img src="../icons/anim/ic-Comment.gif" alt="💬" class="nav-icon" style="width: 35px; height: 35px;"> Комментарии</a></li>
                <li><a href="../logout.php" class="logout"><img src="../icons/anim/ic-Exit.gif" alt="🚪" class="nav-icon" style="width: 35px; height: 35px;"> Выход</a></li>
            </ul>
        </nav>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1>Управление комментариями</h1>
            </div>
            
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
                <div class="msg ok">Комментарий успешно удалён</div>
            <?php endif; ?>
            
            <div class="admin-filters">
                <form method="GET" action="" class="admin-filters-form">
                    <input type="text" name="search" class="admin-search-input" placeholder="Поиск по комментариям, авторам или постам..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="admin-search-btn"><img src="../icons/anim/ic-Magnifier.gif" alt="🔍" class="nav-icon"> Найти</button>
                    <a href="adm_comments.php" class="admin-reset-btn">Сбросить</a>
                </form>
            </div>
            
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Комментарий</th>
                            <th>Автор</th>
                            <th>Пост</th>
                            <th>Дата</th>
                            <th>Лайков</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($comments) > 0): ?>
                            <?php foreach ($comments as $comment): ?>
                                <tr>
                                    <td><?php echo $comment['id_c']; ?></td>
                                    <td class="admin-comment-content"><?php echo htmlspecialchars(mb_substr($comment['content'], 0, 100)) . (mb_strlen($comment['content']) > 100 ? '...' : ''); ?></td>
                                    <td>
                                        <a href="adm_user_details.php?id=<?php echo $comment['author_id']; ?>" class="admin-link">
                                            <?php echo htmlspecialchars($comment['author_name']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="../post.php?id=<?php echo $comment['id_p']; ?>" class="admin-link" target="_blank">
                                            <?php echo htmlspecialchars($comment['post_title']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?></td>
                                    <td><img src="../icons/static/ic-Like.png" alt="❤️" class="nav-icon"> <?php echo $comment['likes_count']; ?></td>
                                    <td>
                                        <a href="adm_comments.php?delete=<?php echo $comment['id_c']; ?>" 
                                           class="admin-btn admin-btn-sm admin-btn-danger" 
                                           onclick="return confirm('Удалить комментарий?')">
                                            <img src="../icons/anim/ic-Trash.gif" alt="🗑️" class="nav-icon"> Удалить
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="admin-table-empty">Комментарии не найдены</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>