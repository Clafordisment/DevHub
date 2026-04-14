<?php
session_start();
require_once '../config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireAdminAuth();

$active_page = 'tags';

$conn = getDbConnection($config);
$message = '';
$messageClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tag'])) {
    $tagName = trim($_POST['tag_name']);
    $categoryId = (int)$_POST['category_id'];
    
    if (!empty($tagName) && $categoryId > 0) {
        $tagNameEscaped = $conn->real_escape_string($tagName);
        $checkSql = "SELECT id_t FROM Tags WHERE name = '$tagNameEscaped'";
        $checkResult = $conn->query($checkSql);
        
        if ($checkResult && $checkResult->num_rows > 0) {
            $message = "Тег '$tagName' уже существует";
            $messageClass = "err";
        } else {
            $insertSql = "INSERT INTO Tags (name, id_catg) VALUES ('$tagNameEscaped', $categoryId)";
            if ($conn->query($insertSql)) {
                $message = "Тег '$tagName' успешно добавлен";
                $messageClass = "ok";
            } else {
                $message = "Ошибка при добавлении тега";
                $messageClass = "err";
            }
        }
    } else {
        $message = "Заполните все поля";
        $messageClass = "err";
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $tagId = (int)$_GET['delete'];
    $conn->query("DELETE FROM Tags WHERE id_t = $tagId");
    header('Location: adm_tags.php?msg=deleted');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_tag'])) {
    $tagId = (int)$_POST['tag_id'];
    $tagName = trim($_POST['tag_name']);
    $categoryId = (int)$_POST['category_id'];
    
    if (!empty($tagName) && $categoryId > 0) {
        $tagNameEscaped = $conn->real_escape_string($tagName);
        $updateSql = "UPDATE Tags SET name = '$tagNameEscaped', id_catg = $categoryId WHERE id_t = $tagId";
        $conn->query($updateSql);
        header('Location: adm_tags.php?msg=updated');
        exit;
    }
}

$sql = "
    SELECT t.id_t, t.name, tc.id_catg, tc.name as category_name, tc.color_code,
           (SELECT COUNT(*) FROM tags_posts WHERE id_t = t.id_t) as usage_count
    FROM Tags t
    JOIN tags_catg tc ON t.id_catg = tc.id_catg
    ORDER BY tc.sort_order, t.name
";
$result = $conn->query($sql);
$tags = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tags[] = $row;
    }
}

$catResult = $conn->query("SELECT id_catg, name, color_code FROM tags_catg ORDER BY sort_order");
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
    <title>AdminPanel | Управление тегами</title>
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
                <li><a href="adm_index.php" <?php echo ($active_page == 'dashboard') ? 'class="active"' : ''; ?>><img src="../icons/anim/light/ic-Graph-lightVer.gif" alt="📊" class="nav-icon" style="width: 35px; height: 35px;"> Главная</a></li>
                <li><a href="adm_users.php" <?php echo ($active_page == 'users') ? 'class="active"' : ''; ?>><img src="../icons/anim/light/ic-Users-lightVer.gif" alt="👥" class="nav-icon" style="width: 35px; height: 35px;"> Пользователи</a></li>
                <li><a href="adm_posts.php" <?php echo ($active_page == 'posts') ? 'class="active"' : ''; ?>><img src="../icons/anim/light/ic-Post-lightVer.gif" alt="📝" class="nav-icon" style="width: 35px; height: 35px;"> Посты</a></li>
                <li><a href="adm_tags.php" <?php echo ($active_page == 'tags') ? 'class="active"' : ''; ?>><img src="../icons/static/ic-Tag_framed-darkVer.png" alt="🏷️" class="nav-icon" style="width: 35px; height: 35px;"> Теги</a></li>
                <li><a href="adm_comments.php" <?php echo ($active_page == 'comments') ? 'class="active"' : ''; ?>><img src="../icons/anim/light/ic-Comment-lightVer.gif" alt="💬" class="nav-icon" style="width: 35px; height: 35px;"> Комментарии</a></li>
                <li><a href="../logout.php" class="logout"><img src="../icons/anim/light/ic-Exit-lightVer.gif" alt="🚪" class="nav-icon" style="width: 35px; height: 35px;"> Выход</a></li>
            </ul>
        </nav>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1>Управление тегами</h1>
            </div>
            
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
                <div class="msg ok">Тег успешно удалён</div>
            <?php endif; ?>
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
                <div class="msg ok">Тег успешно обновлён</div>
            <?php endif; ?>
            <?php if ($message): ?>
                <div class="msg <?php echo $messageClass; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <div class="admin-section">
                <h2>➕ Добавить новый тег</h2>
                <form method="POST" class="admin-form">
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label>Название тега</label>
                            <input type="text" name="tag_name" class="admin-input" required>
                        </div>
                        <div class="admin-form-group">
                            <label>Категория</label>
                            <select name="category_id" class="admin-select" required>
                                <option value="">Выберите категорию</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id_catg']; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="admin-form-group admin-form-group-btn">
                            <button type="submit" name="add_tag" class="admin-btn">➕ Добавить</button>
                        </div>
                    </div>
                </form>
            </div>
        
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Тег</th>
                            <th>Категория</th>
                            <th>Использований</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($tags) > 0): ?>
                            <?php foreach ($tags as $tag): ?>
                                <tr>
                                    <td><?php echo $tag['id_t']; ?></td>
                                    <td>
                                        <span class="admin-tag-preview" style="border-color: <?php echo $tag['color_code']; ?>; background-color: <?php echo $tag['color_code']; ?>20;">
                                            <?php echo htmlspecialchars($tag['name']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="color: <?php echo $tag['color_code']; ?>;">
                                            <?php echo htmlspecialchars($tag['category_name']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $tag['usage_count']; ?></td>
                                    <td>
                                        <button class="admin-btn admin-btn-sm edit-tag-btn" 
                                                data-id="<?php echo $tag['id_t']; ?>"
                                                data-name="<?php echo htmlspecialchars($tag['name']); ?>"
                                                data-category="<?php echo $tag['id_catg']; ?>">
                                            ✏️ Редактировать
                                        </button>
                                        <a href="adm_tags.php?delete=<?php echo $tag['id_t']; ?>" 
                                           class="admin-btn admin-btn-sm admin-btn-danger" 
                                           onclick="return confirm('Удалить тег? Он будет удалён из всех постов.')">
                                            🗑️ Удалить
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="admin-table-empty">Теги не найдены</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div id="editTagModal" class="admin-modal">
        <div class="admin-modal-content">
            <div class="admin-modal-header">
                <h3>✏️ Редактировать тег</h3>
                <button class="admin-modal-close">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="tag_id" id="edit_tag_id">
                <div class="admin-form-group">
                    <label>Название тега</label>
                    <input type="text" name="tag_name" id="edit_tag_name" class="admin-input" required>
                </div>
                <div class="admin-form-group">
                    <label>Категория</label>
                    <select name="category_id" id="edit_category_id" class="admin-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id_catg']; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-form-group">
                    <button type="submit" name="edit_tag" class="admin-btn">💾 Сохранить</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    const modal = document.getElementById('editTagModal');
    const closeBtn = modal.querySelector('.admin-modal-close');
    
    document.querySelectorAll('.edit-tag-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('edit_tag_id').value = btn.dataset.id;
            document.getElementById('edit_tag_name').value = btn.dataset.name;
            document.getElementById('edit_category_id').value = btn.dataset.category;
            modal.classList.add('show');
        });
    });
    
    closeBtn.addEventListener('click', () => {
        modal.classList.remove('show');
    });
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('show');
        }
    });
    </script>
</body>
</html>