<?php
//Обработчик AJAX запросов для удаления комментариев

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

$commentId = isset($data['comment_id']) ? (int)$data['comment_id'] : 0;
$userId = (int)$_SESSION['user_id'];

if ($commentId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Не указан ID комментария']);
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

$conn->set_charset("utf8");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Ошибка подключения к БД']);
    exit;
}

$checkSql = "SELECT id_c FROM Comments WHERE id_c = $commentId AND id_u = $userId LIMIT 1";
$checkResult = $conn->query($checkSql);

if ($checkResult && $checkResult->num_rows > 0) {
    $deleteSql = "DELETE FROM Comments WHERE id_c = $commentId";
    
    if ($conn->query($deleteSql)) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Ошибка удаления комментария: ' . $conn->error]);
    }
} else {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Нет прав на удаление этого комментария']);
}

$conn->close();
?>