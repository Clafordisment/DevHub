<?php
session_start();
require_once '../config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireAdminAuth();

$active_page = 'posts';

$conn = getDbConnection($config);

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $postId = (int)$_GET['delete'];
    
    $imgSql = "SELECT ownPrev FROM Posts WHERE id_p = $postId";
    $imgResult = $conn->query($imgSql);
    if ($imgResult && $imgResult->num_rows > 0) {
        $imgRow = $imgResult->fetch_assoc();
        if (!empty($imgRow['ownPrev']) && file_exists('../' . $imgRow['ownPrev'])) {
            unlink('../' . $imgRow['ownPrev']);
        }
    }
    
    $conn->query("DELETE FROM Posts WHERE id_p = $postId");
    header('Location: adm_posts.php?msg=deleted');
    exit;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? (int)$_GET['status'] : -1;
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$sql = "
    SELECT p.id_p, p.title, p.create_at, p.avRate, p.isNote, p.id_type,
           COALESCE(NULLIF(u.username, ''), u.login) AS author_name,
           (SELECT COUNT(*) FROM Comments WHERE id_p = p.id_p) as comments_count
    FROM Posts p
    JOIN Users u ON p.id_u = u.Id_U
    WHERE u.login != 'devAdminHubber'
";

if ($search) {
    $searchEscaped = $conn->real_escape_string($search);
    $sql .= " AND (p.title LIKE '%$searchEscaped%' OR p.content LIKE '%$searchEscaped%')";
}

if ($status === 0 || $status === 1) {
    $sql .= " AND p.isNote = $status";
}

if ($category > 0) {
    $sql .= " AND p.id_type = $category";
}

$sql .= " ORDER BY p.create_at DESC";

$result = $conn->query($sql);
$posts = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}

$catResult = $conn->query("SELECT id_PType, name FROM posts_catg ORDER BY id_PType");
$categories = [];
if ($catResult) {
    while ($row = $catResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>AdminPanel | Управление постами</title>
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
                <h1>Управление постами</h1>
            </div>
            
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
                <div class="msg ok">Пост успешно удалён</div>
            <?php endif; ?>
            
            <div class="admin-filters">
                <form method="GET" action="" class="admin-filters-form">
                    <input type="text" name="search" class="admin-search-input" placeholder="Поиск по заголовку или содержанию..." value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="status" class="admin-filter-select">
                        <option value="-1" <?php echo $status == -1 ? 'selected' : ''; ?>>Все статусы</option>
                        <option value="0" <?php echo $status == 0 ? 'selected' : ''; ?>>Опубликованные</option>
                        <option value="1" <?php echo $status == 1 ? 'selected' : ''; ?>>Черновики</option>
                    </select>
                    
                    <select name="category" class="admin-filter-select">
                        <option value="0" <?php echo $category == 0 ? 'selected' : ''; ?>>Все категории</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id_PType']; ?>" <?php echo $category == $cat['id_PType'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" class="admin-search-btn"><img src="../icons/anim/ic-Magnifier.gif" alt="🔍" class="nav-icon"> Фильтровать</button>
                    <a href="adm_posts.php" class="admin-reset-btn">Сбросить</a>
                </form>
            </div>
            
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Заголовок</th>
                            <th>Автор</th>
                            <th>Категория</th>
                            <th>Дата</th>
                            <th>Статус</th>
                            <th>Рейтинг</th>
                            <th>Комм.</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($posts) > 0): ?>
                            <?php foreach ($posts as $post): 
                                $categoryName = '';
                                foreach ($categories as $cat) {
                                    if ($cat['id_PType'] == $post['id_type']) {
                                        $categoryName = $cat['name'];
                                        break;
                                    }
                                }
                            ?>
                                <tr>
                                    <td><?php echo $post['id_p']; ?></td>
                                    <td class="admin-post-title"><?php echo htmlspecialchars($post['title']); ?></td>
                                    <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                                    <td><?php echo htmlspecialchars($categoryName); ?></td>
                                    <td><?php echo date('d.m.Y', strtotime($post['create_at'])); ?></td>
                                    <td>
                                        <span class="admin-badge <?php echo $post['isNote'] ? 'admin-badge-draft' : 'admin-badge-published'; ?>">
                                            <?php echo $post['isNote'] ? 'Черновик' : 'Опубликован'; ?>
                                        </span>
                                    </td>
                                    <td>⭐ <?php echo isset($post['avRate']) ? $post['avRate'] : 0; ?></td>
                                    <td>💬 <?php echo $post['comments_count']; ?></td>
                                    <td>
                                        <?php if ($post['isNote'] == 0): ?>
                                            <a href="../post.php?id=<?php echo $post['id_p']; ?>" class="admin-btn admin-btn-sm" target="_blank">Просмотр</a>
                                        <?php endif; ?>
                                        <a href="adm_posts.php?delete=<?php echo $post['id_p']; ?>" class="admin-btn admin-btn-sm admin-btn-danger" onclick="return confirm('Удалить пост?')">Удалить</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="admin-table-empty">Посты не найдены</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>