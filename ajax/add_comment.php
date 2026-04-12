<?php
//Обработчик AJAX запросов для добавления комментариев

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Неверный формат данных']);
    exit;
}

$postId = isset($data['post_id']) ? (int)$data['post_id'] : 0;
$content = isset($data['content']) ? trim($data['content']) : '';

if ($postId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Не указан ID поста']);
    exit;
}

if (empty($content)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Комментарий не может быть пустым']);
    exit;
}

require_once '../config.php';

$conn = new mysqli(
    $config['db_host'],
    $config['db_user'],
    $config['db_pass'],
    $config['db_name']
);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Ошибка подключения к БД']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$escapedContent = $conn->real_escape_string($content);

$checkPostSql = "SELECT id_p FROM Posts WHERE id_p = $postId LIMIT 1";
$checkPostResult = $conn->query($checkPostSql);

if (!$checkPostResult || $checkPostResult->num_rows == 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Пост не найден']);
    $conn->close();
    exit;
}

$insertSql = "INSERT INTO Comments (id_p, id_u, content, created_at) 
              VALUES ($postId, $userId, '$escapedContent', NOW())";

if ($conn->query($insertSql)) {
    $commentId = $conn->insert_id;

    $userSql = "SELECT login, username FROM Users WHERE Id_U = $userId LIMIT 1";
    $userResult = $conn->query($userSql);
    
    if ($userResult && $userResult->num_rows > 0) {
        $userData = $userResult->fetch_assoc();
        $authorName = !empty($userData['username']) ? $userData['username'] : $userData['login'];
    } else {
        $authorName = 'Пользователь';
    }
    
    echo json_encode([
        'success' => true,
        'comment' => [
            'id' => $commentId,
            'content' => $content,
            'author' => $authorName,
            'author_id' => $userId,
            'date' => date('d.m.Y H:i'),
            'likes_count' => 0
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Ошибка сохранения комментария: ' . $conn->error]);
}

$conn->close();
?>