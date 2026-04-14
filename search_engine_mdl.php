<?php
// Модуль поиска и фильтрации постов

class SearchEngine {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Основной метод поиска с ранжированием
     * @param string $query Поисковый запрос
     * @param array $filters Параметры фильтрации
     * @return array Массив постов с весом релевантности
     */

    public function search($query, $filters = []) {
        $query = trim($query);
        
        if (empty($query)) {
            return $this->getAllPosts($filters);
        }
        
        $keywords = preg_split('/\s+/', mb_strtolower($query));
        $keywords = array_unique($keywords);
        
        $posts = $this->getAllPostsForSearch($filters);
        
        foreach ($posts as &$post) {
            $post['weight'] = $this->calculateWeight($post, $keywords);
        }
        
        usort($posts, function($a, $b) {
            if ($a['weight'] == $b['weight']) return 0;
            return ($a['weight'] < $b['weight']) ? 1 : -1;
        });
        
        $posts = array_filter($posts, function($post) {
            return $post['weight'] > 0;
        });
        
        return array_values($posts);
    }
    
    /*
     * Получение всех постов для поиска
     */
    private function getAllPostsForSearch($filters = []) {
        $sql = "
            SELECT 
                p.id_p,
                p.title,
                p.content,
                p.ownPrev,
                p.create_at,
                p.avRate,
                COALESCE(NULLIF(u.username, ''), u.login) AS author_name,
                (SELECT COUNT(*) FROM Comments WHERE id_p = p.id_p) as comments_count
            FROM Posts p
            JOIN Users u ON p.id_u = u.Id_U
            WHERE p.isNote = 0
        ";
        
        $sql .= $this->applyFilters($filters);
        
        $result = $this->conn->query($sql);
        $posts = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['tags'] = $this->getPostTags($row['id_p']);
                $posts[] = $row;
            }
        }
        
        return $posts;
    }
    
    /*
     * Получение всех постов (без поиска)
     */
    private function getAllPosts($filters = []) {
        $sql = "
            SELECT 
                p.id_p,
                p.title,
                p.content,
                p.ownPrev,
                p.create_at,
                p.avRate,
                COALESCE(NULLIF(u.username, ''), u.login) AS author_name,
                (SELECT COUNT(*) FROM Comments WHERE id_p = p.id_p) as comments_count
            FROM Posts p
            JOIN Users u ON p.id_u = u.Id_U
            WHERE p.isNote = 0
        ";
        
        $sql .= $this->applyFilters($filters);
        $sql .= $this->applySorting($filters);
        
        $result = $this->conn->query($sql);
        $posts = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['tags'] = $this->getPostTags($row['id_p']);
                $posts[] = $row;
            }
        }
        
        return $posts;
    }
    
    /*
     * Получение тегов поста
     */
    private function getPostTags($postId) {
        $tagsSql = "
            SELECT t.name, tc.color_code
            FROM tags_posts tp
            JOIN Tags t ON tp.id_t = t.id_t
            JOIN tags_catg tc ON t.id_catg = tc.id_catg
            WHERE tp.id_p = $postId
        ";
        $tagsResult = $this->conn->query($tagsSql);
        $allTags = [];
        if ($tagsResult) {
            while ($tagRow = $tagsResult->fetch_assoc()) {
                $allTags[] = $tagRow;
            }
        }
        
        // Рандомный выбор 3 тегов
        $tags = [];
        if (count($allTags) > 0) {
            shuffle($allTags);  // ← перемешиваем массив
            $tags = array_slice($allTags, 0, 3);  // ← берём первые 3
        }
        
        return $tags;
    }
    
    /*
     * Расчет веса релевантности поста
     */
    private function calculateWeight($post, $keywords) {
        $weight = 0;
        
        $title = mb_strtolower($post['title']);
        $content = mb_strtolower(strip_tags($post['content']));
        $tags = array_map(function($tag) {
            return mb_strtolower($tag['name']);
        }, $post['tags']);
        
        foreach ($keywords as $keyword) {
            //Вес за совпадение в заголовке (высокий приоритет)
            $weight += substr_count($title, $keyword) * 10;
            
            //Вес за совпадение в содержании
            $weight += substr_count($content, $keyword) * 1;
            
            //Вес за совпадение в тегах (средний приоритет)
            foreach ($tags as $tag) {
                if (strpos($tag, $keyword) !== false) {
                    $weight += 5;
                }
            }
        }
        
        return $weight;
    }
    
    /*
     * Применение фильтров к SQL запросу
     */
    private function applyFilters($filters) {
        $sql = "";
        
        //Фильтр по тегам
        if (!empty($filters['tags']) && is_array($filters['tags'])) {
            $tagIds = implode(',', array_map('intval', $filters['tags']));
            $sql .= " AND p.id_p IN (
                SELECT DISTINCT id_p FROM tags_posts WHERE id_t IN ($tagIds)
            )";
        }
        
        //Фильтр по категории
        if (!empty($filters['category'])) {
            $sql .= " AND p.id_type = " . (int)$filters['category'];
        }
        
        //Фильтр по дате
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(p.create_at) >= '" . $this->conn->real_escape_string($filters['date_from']) . "'";
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(p.create_at) <= '" . $this->conn->real_escape_string($filters['date_to']) . "'";
        }
        
        //Фильтр по минимальному рейтингу
        if (isset($filters['min_rating']) && $filters['min_rating'] > 0) {
            $sql .= " AND p.avRate >= " . (float)$filters['min_rating'];
        }
        
        //Фильтр по минимальному количеству комментариев
        if (isset($filters['min_comments']) && $filters['min_comments'] > 0) {
            $sql .= " AND (SELECT COUNT(*) FROM Comments WHERE id_p = p.id_p) >= " . (int)$filters['min_comments'];
        }
        
        return $sql;
    }
    
    /*
     * Применение сортировки
     */
    private function applySorting($filters) {
        $sortField = isset($filters['sort_by']) ? $filters['sort_by'] : 'date';
        $sortOrder = isset($filters['sort_order']) ? $filters['sort_order'] : 'desc';
        
        $order = ($sortOrder === 'asc') ? 'ASC' : 'DESC';
        
        switch ($sortField) {
            case 'rating':
                return " ORDER BY p.avRate $order, p.create_at DESC";
            case 'comments':
                return " ORDER BY comments_count $order, p.create_at DESC";
            case 'date':
            default:
                return " ORDER BY p.create_at $order";
        }
    }
}
?>