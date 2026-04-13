<?php
// Получение всех тегов для модального окна поиска

session_start();
header('Content-Type: application/json');

require_once '../config.php';

$conn = new mysqli(
    $config['db_host'],
    $config['db_user'],
    $config['db_pass'],
    $config['db_name']
);
$conn->set_charset("utf8");

$sql = "
    SELECT t.id_t, t.name, tc.name as category_name, tc.color_code, tc.id_catg
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

echo json_encode([
    'success' => true,
    'tags' => $tags
]);

$conn->close();
?>