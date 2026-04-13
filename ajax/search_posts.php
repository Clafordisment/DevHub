<?php
// AJAX-обработчик поиска постов

session_start();
header('Content-Type: application/json');

require_once '../config.php';
require_once '../search_engine_mdl.php';

$conn = new mysqli(
    $config['db_host'],
    $config['db_user'],
    $config['db_pass'],
    $config['db_name']
);
$conn->set_charset("utf8");

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Неверный формат данных']);
    exit;
}

$query = isset($data['query']) ? $data['query'] : '';
$filters = isset($data['filters']) ? $data['filters'] : [];

$searchEngine = new SearchEngine($conn);
$posts = $searchEngine->search($query, $filters);

echo json_encode([
    'success' => true,
    'posts' => $posts
]);

$conn->close();
?>