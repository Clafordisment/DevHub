<?php
session_start();
require_once '../config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireAdminAuth();

$active_page = 'users';

$conn = getDbConnection($config);
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$users = [];

if ($searchQuery) {
    $users = searchUsers($conn, $searchQuery);
} else {
        $sql = "
            SELECT Id_U, login, username, create_at,
                (SELECT COUNT(*) FROM Posts WHERE id_u = Id_U AND isNote = 0) as posts_count,
                (SELECT COUNT(*) FROM Comments WHERE id_u = Id_U) as comments_count
            FROM Users
            WHERE login != 'devAdminHubber'
            ORDER BY create_at DESC
        ";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>AdminPanel | Пользователи</title>
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
                <h1>Управление пользователями</h1>
            </div>
            
            <div class="admin-search-bar">
                <form method="GET" action="">
                    <input type="text" name="search" class="admin-search-input" placeholder="Поиск по логину или имени..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <button type="submit" class="admin-search-btn"><img src="../icons/anim/ic-Magnifier.gif" alt="🔍" class="nav-icon"> Найти</button>
                    <?php if ($searchQuery): ?>
                        <a href="adm_users.php" class="admin-reset-btn">Сбросить</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Логин</th>
                            <th>Имя пользователя</th>
                            <th>Дата регистрации</th>
                            <th>Постов</th>
                            <th>Комментариев</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['Id_U']; ?></td>
                                    <td><?php echo htmlspecialchars($user['login']); ?></td>
                                    <td><?php echo htmlspecialchars(isset($user['username']) ? $user['username'] : '—'); ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($user['create_at'])); ?></td>
                                    <td><?php echo $user['posts_count']; ?></td>
                                    <td><?php echo $user['comments_count']; ?></td>
                                    <td>
                                        <a href="adm_user_details.php?id=<?php echo $user['Id_U']; ?>" class="admin-btn admin-btn-sm">Подробнее</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="admin-table-empty">Пользователи не найдены</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>