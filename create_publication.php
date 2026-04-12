<?php
session_start();

$page_title = "DevHub | Создание публикации";

if (!isset($_SESSION['user_id'])) {
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
$draftIdFromGet = isset($_GET['draft_id']) ? (int)$_GET['draft_id'] : 0;

$editingDraft = false;
$draftTitle = '';
$draftContent = '';
$draftImage = '';
$existingTags = [];

$postCatgSql = "SELECT id_PType, name FROM posts_catg ORDER BY id_PType";
$postCatgResult = $conn->query($postCatgSql);
$postCategories = [];
if ($postCatgResult) {
    while ($row = $postCatgResult->fetch_assoc()) {
        $postCategories[] = $row;
    }
}

$tagsSql = "
    SELECT t.id_t, t.name, tc.name as category_name, tc.color_code, tc.id_catg
    FROM Tags t
    JOIN tags_catg tc ON t.id_catg = tc.id_catg
    ORDER BY tc.sort_order, t.name
";
$tagsResult = $conn->query($tagsSql);
$allTags = [];
if ($tagsResult) {
    while ($row = $tagsResult->fetch_assoc()) {
        $allTags[] = $row;
    }
}

if ($draftIdFromGet > 0) {
    $sqlDraft = "
        SELECT title, content, ownPrev
        FROM Posts 
        WHERE id_p = {$draftIdFromGet} AND id_u = {$userId} AND isNote = 1
        LIMIT 1
    ";
    $resDraft = $conn->query($sqlDraft);
    if ($resDraft && $resDraft->num_rows > 0) {
        $rowDraft = $resDraft->fetch_assoc();
        $editingDraft = true;
        $draftTitle = $rowDraft['title'];
        $draftContent = $rowDraft['content'];
        $draftImage = $rowDraft['ownPrev'];
        
        $tagsSqlExisting = "
            SELECT t.id_t 
            FROM tags_posts tp
            JOIN Tags t ON tp.id_t = t.id_t
            WHERE tp.id_p = {$draftIdFromGet}
        ";
        $tagsExistingResult = $conn->query($tagsSqlExisting);
        if ($tagsExistingResult) {
            while ($row = $tagsExistingResult->fetch_assoc()) {
                $existingTags[] = $row['id_t'];
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $title   = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $draftId = isset($_POST['draft_id']) ? (int)$_POST['draft_id'] : 0;
    $postCategory = isset($_POST['post_category']) ? (int)$_POST['post_category'] : 1;
    $selectedTags = isset($_POST['selected_tags']) ? explode(',', $_POST['selected_tags']) : [];
    
    // Обработка загрузки изображения
    $imagePath = '';
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExt = pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExt;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['post_image']['tmp_name'], $targetPath)) {
            $imagePath = $targetPath;
        }
    } elseif (isset($_POST['existing_image'])) {
        $imagePath = $_POST['existing_image'];
    }

    if ($title !== '' && $content !== '') {
        if (isset($_POST['publish'])) {
            $isNote = 0; 
        } else {
            $isNote = 1; 
        }

        $postId = 0;

        if ($draftId > 0) {
            $stmt = $conn->prepare("
                UPDATE Posts 
                SET title = ?, content = ?, isNote = ?, id_type = ?, ownPrev = ?, create_at = NOW()
                WHERE id_p = ? AND id_u = ?
            ");
            $stmt->bind_param("ssiissi", $title, $content, $isNote, $postCategory, $imagePath, $draftId, $userId);
            $stmt->execute();
            $stmt->close();
            $postId = $draftId;
        } else {
            $stmt = $conn->prepare("
                INSERT INTO Posts (id_type, id_u, title, content, create_at, avRate, isNote, ownPrev)
                VALUES (?, ?, ?, ?, NOW(), 0, ?, ?)
            ");
            $stmt->bind_param("iissis", $postCategory, $userId, $title, $content, $isNote, $imagePath);
            $stmt->execute();
            $stmt->close();
            $postId = $conn->insert_id;
        }
        
        if ($postId > 0) {
            $conn->query("DELETE FROM tags_posts WHERE id_p = $postId");
            foreach ($selectedTags as $tagId) {
                $tagId = (int)$tagId;
                if ($tagId > 0) {
                    $conn->query("INSERT INTO tags_posts (id_p, id_t) VALUES ($postId, $tagId)");
                }
            }
        }

        if ($isNote === 1) {
            header("Location: drafts.php");
            exit;
        } else {
            header("Location: index.php");
            exit;
        }
    }
}

require_once 'header.php';
?>

<div id="post-data" 
     data-existing-tags='<?php echo json_encode($existingTags); ?>'
     style="display: none;"></div>

<div class="box">
    <h2>Создать новую публикацию</h2>
    <form method="POST" id="publication-form" enctype="multipart/form-data">
        <input type="hidden" name="draft_id" value="<?php echo $editingDraft ? (int)$draftIdFromGet : 0; ?>">
        <input type="hidden" name="selected_tags" id="selected-tags-input" value="">
        <input type="hidden" name="existing_image" id="existing-image" value="<?php echo htmlspecialchars($draftImage); ?>">
        
        <label>Заголовок</label>
        <input type="text" name="title" class="input-zone" placeholder="Введите заголовок" required
               value="<?php echo htmlspecialchars($editingDraft ? $draftTitle : ''); ?>">
        
        <div class="post-category-select">
            <label>Категория публикации</label>
            <select name="post_category" required>
                <?php foreach ($postCategories as $cat): ?>
                    <option value="<?php echo $cat['id_PType']; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="image-upload-area">
            <label>Изображение для карточки</label>
            <input type="file" name="post_image" id="post_image" accept="image/jpeg,image/png,image/gif,image/webp">
            <div id="image-preview-container" style="display: <?php echo !empty($draftImage) ? 'block' : 'none'; ?>;">
                <div class="image-preview">
                    <img id="image-preview" src="<?php echo htmlspecialchars($draftImage); ?>" alt="Предпросмотр">
                </div>
                <button type="button" class="remove-image-btn" id="remove-image-btn">Удалить изображение</button>
            </div>
            <small style="color: #888888; display: block; margin-top: 8px;">Рекомендуемый размер: 400x300px. Поддерживаются JPG, PNG, GIF, WEBP.</small>
        </div>
        
        <label>Содержание</label>
        <textarea name="content" class="input-zone" placeholder="Введите текст публикации..." rows="10" required><?php 
            echo htmlspecialchars($editingDraft ? $draftContent : ''); 
        ?></textarea>
        
        <div class="selection-buttons">
            <button type="button" class="selection-btn" id="select-tags-btn">♯ Выбрать теги</button>
        </div>
    
        <div id="post-tags-container" class="post-tags" style="display: none;">
            <span class="post-tags-label">Выбранные теги:</span>
            <div id="selected-tags-list"></div>
        </div>
        
        <div class="form-actions">
            <button type="submit" name="save_draft" class="button">Сохранить черновик</button>
            <button type="submit" name="publish" class="button">Опубликовать</button>
        </div>
    </form>
</div>

<div id="tags-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Выбор тегов</h3>
            <button class="modal-close" id="close-modal-btn">&times;</button>
        </div>
        <div class="selected-tags-area" id="modal-selected-tags">
            <span style="color: #888888; font-size: 12px;">Выбранные теги:</span>
        </div>
        <div class="modal-body" id="modal-tags-list">
            <?php
            $currentCategory = null;
            foreach ($allTags as $tag):
                if ($currentCategory !== $tag['category_name']):
                    if ($currentCategory !== null): ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="tag-category" data-category-id="<?php echo $tag['id_catg']; ?>">
                        <div class="tag-category-header">
                            <h4 style="color: <?php echo $tag['color_code']; ?>;"><?php echo htmlspecialchars($tag['category_name']); ?></h4>
                            <span class="collapse-icon">▼</span>
                        </div>
                        <div class="tag-list">
                    <?php 
                    $currentCategory = $tag['category_name'];
                endif; ?>
                    <span class="tag-item" 
                        data-tag-id="<?php echo $tag['id_t']; ?>" 
                        data-tag-name="<?php echo htmlspecialchars($tag['name']); ?>" 
                        data-color-code="<?php echo $tag['color_code']; ?>"
                        style="border-color: <?php echo $tag['color_code']; ?>;">
                        <?php echo htmlspecialchars($tag['name']); ?>
                    </span>
            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
        <div class="modal-footer">
            <button id="apply-tags-btn">Применить</button>
        </div>
    </div>
</div>

<script src="JS/tag_selector.js"></script>
<script>
// Предпросмотр изображения
const imageInput = document.getElementById('post_image');
const previewContainer = document.getElementById('image-preview-container');
const previewImg = document.getElementById('image-preview');
const removeBtn = document.getElementById('remove-image-btn');
const existingImageInput = document.getElementById('existing-image');

if (imageInput) {
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                previewImg.src = event.target.result;
                previewContainer.style.display = 'block';
                existingImageInput.value = '';
            };
            reader.readAsDataURL(file);
        }
    });
}

if (removeBtn) {
    removeBtn.addEventListener('click', function() {
        previewImg.src = '';
        previewContainer.style.display = 'none';
        imageInput.value = '';
        existingImageInput.value = '';
    });
}
</script>