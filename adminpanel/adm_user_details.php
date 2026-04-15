<?php
session_start();
require_once '../config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireAdminAuth();

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($userId <= 0) {
    header('Location: adm_users.php');
    exit;
}

$conn = getDbConnection($config);
$user = getUserDetails($conn, $userId);

$postsSql = "
    SELECT id_p, title, create_at, avRate, isNote,
           (SELECT COUNT(*) FROM Comments WHERE id_p = p.id_p) as comments_count
    FROM Posts p
    WHERE id_u = $userId
    ORDER BY create_at DESC
";
$postsResult = $conn->query($postsSql);
$posts = [];
if ($postsResult) {
    while ($row = $postsResult->fetch_assoc()) {
        $posts[] = $row;
    }
}

$conn->close();

if (!$user) {
    header('Location: adm_users.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>AdminPanel | Пользователь: <?php echo htmlspecialchars($user['login']); ?></title>
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
                <h1>Профиль пользователя</h1>
                <a href="adm_users.php" class="back-link">← Назад к списку</a>
            </div>
            
            <div class="admin-user-profile">
                <div class="admin-user-info">
                    <div class="admin-user-avatar">
                        <div class="admin-avatar-placeholder"><img src="../icons/anim/ic-User.gif" alt="👤" class="nav-icon" style="width: 35px; height: 35px;"></div>
                    </div>
                    <div class="admin-user-details">
                        <h2><?php echo htmlspecialchars($user['login']); ?></h2>
                        <?php if ($user['username']): ?>
                            <p>Имя: <?php echo htmlspecialchars($user['username']); ?></p>
                        <?php endif; ?>
                        <p>Email: <?php echo htmlspecialchars($user['email'] ?: 'не указан'); ?></p>
                        <p>Дата регистрации: <?php echo date('d.m.Y H:i', strtotime($user['create_at'])); ?></p>
                    </div>
                </div>
                
                <div class="admin-user-stats">
                    <div class="admin-user-stat">
                        <div class="admin-user-stat-value"><?php echo $user['posts_count']; ?></div>
                        <div class="admin-user-stat-label">Постов</div>
                    </div>
                    <div class="admin-user-stat">
                        <div class="admin-user-stat-value"><?php echo $user['comments_count']; ?></div>
                        <div class="admin-user-stat-label">Комментариев</div>
                    </div>
                    <div class="admin-user-stat">
                        <div class="admin-user-stat-value"><?php echo isset($user['received_likes']) ? $user['received_likes'] : 0; ?></div>
                        <div class="admin-user-stat-label">Получено лайков</div>
                    </div>
                </div>
            </div>
            
            <div class="admin-section">
                <h2>📝 Посты пользователя</h2>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Заголовок</th>
                                <th>Дата создания</th>
                                <th>Статус</th>
                                <th>Рейтинг</th>
                                <th>Комментариев</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($posts) > 0): ?>
                                <?php foreach ($posts as $post): ?>
                                    <tr>
                                        <td><?php echo $post['id_p']; ?></td>
                                        <td><?php echo htmlspecialchars($post['title']); ?></td>
                                        <td><?php echo date('d.m.Y H:i', strtotime($post['create_at'])); ?></td>
                                        <td><?php echo $post['isNote'] ? 'Черновик' : 'Опубликован'; ?></td>
                                        <td>⭐ <?php echo isset($post['avRate']) ? $post['avRate'] : 0; ?></td>
                                        <td>💬 <?php echo $post['comments_count']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="admin-table-empty">У пользователя нет постов</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>