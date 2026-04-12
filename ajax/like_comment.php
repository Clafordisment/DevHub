<?php
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
require_once '../config.php';

$conn = new mysqli(
    $config['db_host'],
    $config['db_user'],
    $config['db_pass'],
    $config['db_name']
);
$conn->set_charset("utf8");

$checkSql = "SELECT id_l FROM comments_likes WHERE id_c = $commentId AND id_u = $userId";
$checkResult = $conn->query($checkSql);
$hasLike = $checkResult && $checkResult->num_rows > 0;

if ($hasLike) {
    $deleteSql = "DELETE FROM comments_likes WHERE id_c = $commentId AND id_u = $userId";
    $conn->query($deleteSql);
    $action = 'unliked';
} else {
    $insertSql = "INSERT INTO comments_likes (id_c, id_u) VALUES ($commentId, $userId)";
    $conn->query($insertSql);
    $action = 'liked';
}

$updateSql = "UPDATE Comments c 
              SET c.likes_count = (SELECT COUNT(*) FROM comments_likes WHERE id_c = $commentId)
              WHERE c.id_c = $commentId";
$conn->query($updateSql);

$countSql = "SELECT likes_count FROM Comments WHERE id_c = $commentId";
$countResult = $conn->query($countSql);
$countData = $countResult->fetch_assoc();
$likesCount = (int)$countData['likes_count'];

echo json_encode([
    'success' => true,
    'action' => $action,
    'likes_count' => $likesCount
]);

$conn->close();
?>