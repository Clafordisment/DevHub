<?php
//Вспомогательные функции для админ-панели

function getDbConnection($config) {
    $conn = new mysqli(
        $config['db_host'],
        $config['db_user'],
        $config['db_pass'],
        $config['db_name']
    );
    $conn->set_charset("utf8");
    return $conn;
}

//Получение общей статистики
function getGeneralStats($conn) {
    $stats = array();
    
    //Всего пользователей
    $result = $conn->query("SELECT COUNT(*) as count FROM Users");
    $row = $result->fetch_assoc();
    $stats['total_users'] = $row['count'];
    
    //Всего постов (опубликованных)
    $result = $conn->query("SELECT COUNT(*) as count FROM Posts WHERE isNote = 0");
    $row = $result->fetch_assoc();
    $stats['total_posts'] = $row['count'];
    
    //Всего черновиков
    $result = $conn->query("SELECT COUNT(*) as count FROM Posts WHERE isNote = 1");
    $row = $result->fetch_assoc();
    $stats['total_drafts'] = $row['count'];
    
    //Всего комментариев
    $result = $conn->query("SELECT COUNT(*) as count FROM Comments");
    $row = $result->fetch_assoc();
    $stats['total_comments'] = $row['count'];
    
    //Всего лайков
    $result = $conn->query("SELECT SUM(likes_count) as total FROM Comments");
    $row = $result->fetch_assoc();
    $stats['total_likes'] = isset($row['total']) ? $row['total'] : 0;
    
    //Средний рейтинг всех постов
    $result = $conn->query("SELECT AVG(avRate) as avg FROM Posts WHERE isNote = 0 AND avRate > 0");
    $row = $result->fetch_assoc();
    $stats['avg_rating'] = isset($row['avg']) ? round($row['avg'], 1) : 0;
    
    return $stats;
}

//Получение топ-10 постов по рейтингу
function getTopPostsByRating($conn, $limit = 10) {
    $sql = "
        SELECT p.id_p, p.title, p.avRate, 
               COALESCE(NULLIF(u.username, ''), u.login) AS author_name,
               (SELECT COUNT(*) FROM Comments WHERE id_p = p.id_p) as comments_count
        FROM Posts p
        JOIN Users u ON p.id_u = u.Id_U
        WHERE p.isNote = 0 AND p.avRate > 0
        ORDER BY p.avRate DESC
        LIMIT $limit
    ";
    $result = $conn->query($sql);
    $posts = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
    }
    return $posts;
}

//Получение топ-10 постов по комментариям
function getTopPostsByComments($conn, $limit = 10) {
    $sql = "
        SELECT p.id_p, p.title, p.avRate,
               COALESCE(NULLIF(u.username, ''), u.login) AS author_name,
               (SELECT COUNT(*) FROM Comments WHERE id_p = p.id_p) as comments_count
        FROM Posts p
        JOIN Users u ON p.id_u = u.Id_U
        WHERE p.isNote = 0
        ORDER BY comments_count DESC
        LIMIT $limit
    ";
    $result = $conn->query($sql);
    $posts = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
    }
    return $posts;
}

//Получение топ-10 пользователей по постами
function getTopUsersByPosts($conn, $limit = 10) {
    $sql = "
        SELECT u.Id_U, COALESCE(NULLIF(u.username, ''), u.login) AS name,
               (SELECT COUNT(*) FROM Posts WHERE id_u = u.Id_U AND isNote = 0) as posts_count,
               (SELECT COUNT(*) FROM Comments WHERE id_u = u.Id_U) as comments_count
        FROM Users u
        WHERE u.login != 'devAdminHubber'
        ORDER BY posts_count DESC
        LIMIT $limit
    ";
    $result = $conn->query($sql);
    $users = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    return $users;
}

//Получение топ-30 тегов
function getTopTags($conn, $limit = 30) {
    $sql = "
        SELECT t.id_t, t.name, tc.color_code,
               COUNT(tp.id_p) as usage_count
        FROM Tags t
        JOIN tags_posts tp ON t.id_t = tp.id_t
        JOIN tags_catg tc ON t.id_catg = tc.id_catg
        GROUP BY t.id_t
        ORDER BY usage_count DESC
        LIMIT $limit
    ";
    $result = $conn->query($sql);
    $tags = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $tags[] = $row;
        }
    }
    return $tags;
}

//Получение распределения постов по категориям
function getCategoryDistribution($conn) {
    $sql = "
        SELECT pc.name, COUNT(p.id_p) as count
        FROM posts_catg pc
        LEFT JOIN Posts p ON pc.id_PType = p.id_type AND p.isNote = 0
        GROUP BY pc.id_PType
    ";
    $result = $conn->query($sql);
    $categories = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    return $categories;
}

//Поиск пользователей
function searchUsers($conn, $query) {
    $query = $conn->real_escape_string($query);
    $sql = "
        SELECT Id_U, login, username, create_at,
               (SELECT COUNT(*) FROM Posts WHERE id_u = Id_U AND isNote = 0) as posts_count,
               (SELECT COUNT(*) FROM Comments WHERE id_u = Id_U) as comments_count
        FROM Users
        WHERE (login LIKE '%$query%' OR username LIKE '%$query%') AND login != 'devAdminHubber'
        ORDER BY create_at DESC
    ";
    $result = $conn->query($sql);
    $users = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    return $users;
}

//Получение детальной информации о посте
function getPostDetails($conn, $postId) {
    $sql = "
        SELECT p.*, 
               COALESCE(NULLIF(u.username, ''), u.login) AS author_name,
               (SELECT COUNT(*) FROM Comments WHERE id_p = p.id_p) as comments_count
        FROM Posts p
        JOIN Users u ON p.id_u = u.Id_U
        WHERE p.id_p = $postId
    ";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

//Получение детальной информации о пользователе
function getUserDetails($conn, $userId) {
    $sql = "
        SELECT u.*, 
               (SELECT COUNT(*) FROM Posts WHERE id_u = u.Id_U AND isNote = 0) as posts_count,
               (SELECT COUNT(*) FROM Comments WHERE id_u = u.Id_U) as comments_count,
               (SELECT SUM(likes_count) FROM Comments WHERE id_u = u.Id_U) as received_likes
        FROM Users u
        WHERE u.Id_U = $userId
    ";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}
?>