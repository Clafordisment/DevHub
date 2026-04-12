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

$postId = isset($data['post_id']) ? (int)$data['post_id'] : 0;
$rating = isset($data['rating']) ? (float)$data['rating'] : 0;

if ($postId <= 0 || $rating < 0 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Неверные данные']);
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

$userId = (int)$_SESSION['user_id'];

$checkSql = "SELECT id_r FROM post_rates WHERE id_p = $postId AND id_u = $userId";
$checkResult = $conn->query($checkSql);

if ($checkResult && $checkResult->num_rows > 0) {
    $updateSql = "UPDATE post_rates SET rate = $rating WHERE id_p = $postId AND id_u = $userId";
    $conn->query($updateSql);
} else {
    $insertSql = "INSERT INTO post_rates (id_p, id_u, rate) VALUES ($postId, $userId, $rating)";
    $conn->query($insertSql);
}

$avgSql = "SELECT AVG(rate) as avg_rate, COUNT(*) as count FROM post_rates WHERE id_p = $postId";
$avgResult = $conn->query($avgSql);
$avgData = $avgResult->fetch_assoc();

$avgRating = round($avgData['avg_rate'], 1);
$votesCount = (int)$avgData['count'];

$updatePostSql = "UPDATE Posts SET avRate = $avgRating WHERE id_p = $postId";
$conn->query($updatePostSql);

echo json_encode([
    'success' => true,
    'avg_rating' => $avgRating,
    'votes_count' => $votesCount
]);

$conn->close();
?>