<?php
session_start();
header('Content-Type: application/json');

$postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
$lastId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

if ($postId <= 0) {
    echo json_encode(['comments' => []]);
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

$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

$sql = "
    SELECT 
        c.id_c,
        c.content,
        c.created_at,
        c.likes_count,
        COALESCE(NULLIF(u.username, ''), u.login) AS comment_author,
        u.Id_U as author_id
    FROM Comments c
    JOIN Users u ON c.id_u = u.Id_U
    WHERE c.id_p = $postId AND c.id_c > $lastId 
    ORDER BY c.created_at ASC
";

$result = $conn->query($sql);
$comments = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $comments[] = [
            'id' => (int)$row['id_c'],
            'content' => $row['content'],
            'author' => $row['comment_author'],
            'author_id' => (int)$row['author_id'],
            'likes_count' => (int)$row['likes_count'],
            'date' => date('d.m.Y H:i', strtotime($row['created_at']))
        ];
    }
}

echo json_encode(['comments' => $comments]);
$conn->close();
?>