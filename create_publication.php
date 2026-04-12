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

if ($draftIdFromGet > 0) {
    $sqlDraft = "
        SELECT title, content 
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
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $title   = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $draftId = isset($_POST['draft_id']) ? (int)$_POST['draft_id'] : 0;

    if ($title !== '' && $content !== '') {
        if (isset($_POST['publish'])) {
            $isNote = 0; 
        } else {
            $isNote = 1; 
        }

        if ($draftId > 0) {
            $stmt = $conn->prepare("
                UPDATE Posts 
                SET title = ?, content = ?, isNote = ?, create_at = NOW()
                WHERE id_p = ? AND id_u = ?
            ");
            $stmt->bind_param("ssiii", $title, $content, $isNote, $draftId, $userId);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $conn->prepare("
                INSERT INTO Posts (id_type, id_u, title, content, create_at, avRate, isNote, ownPrev)
                VALUES (0, ?, ?, ?, NOW(), 0, ?, '')
            ");
            $stmt->bind_param("issi", $userId, $title, $content, $isNote);
            $stmt->execute();
            $stmt->close();
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

<div class="box">
    <h2>Создать новую публикацию</h2>
    <form method="POST">
        <input type="hidden" name="draft_id" value="<?php echo $editingDraft ? (int)$draftIdFromGet : 0; ?>">
        <label>Заголовок</label>
        <input type="text" name="title" class="input-zone" placeholder="Введите заголовок" required
               value="<?php echo htmlspecialchars($editingDraft ? $draftTitle : ''); ?>">

        <button type="button" class="button" disabled>Добавить тег (в разработке)</button>
        
        <label>Содержание</label>
        <textarea name="content" class="input-zone" placeholder="Введите текст публикации..." rows="10" required><?php 
            echo htmlspecialchars($editingDraft ? $draftContent : ''); 
        ?></textarea>
        
        <div class="form-actions">
            <button type="submit" name="save_draft" class="button">Сохранить черновик</button>
            <button type="submit" name="publish" class="button">Опубликовать</button>
        </div>
    </form>
</div>
