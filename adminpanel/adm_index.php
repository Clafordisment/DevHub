<?php
session_start();
require_once '../config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireAdminAuth();

$active_page = 'dashboard';

$conn = getDbConnection($config);
$stats = getGeneralStats($conn);
$topPostsRating = getTopPostsByRating($conn, 10);
$topPostsComments = getTopPostsByComments($conn, 10);
$topUsers = getTopUsersByPosts($conn, 10);
$topTags = getTopTags($conn, 30);
$categoryDistribution = getCategoryDistribution($conn);

$ratingChartLabels = array();
$ratingChartData = array();
foreach ($topPostsRating as $post) {
    $ratingChartLabels[] = mb_substr($post['title'], 0, 20);
    $ratingChartData[] = $post['avRate'];
}

$commentsChartLabels = array();
$commentsChartData = array();
foreach ($topPostsComments as $post) {
    $commentsChartLabels[] = mb_substr($post['title'], 0, 20);
    $commentsChartData[] = $post['comments_count'];
}

$authorsChartLabels = array();
$authorsChartData = array();
foreach ($topUsers as $user) {
    $authorsChartLabels[] = mb_substr($user['name'], 0, 20);
    $authorsChartData[] = $user['posts_count'];
}

$tagsChartLabels = array();
$tagsChartData = array();
foreach ($topTags as $tag) {
    $tagsChartLabels[] = $tag['name'];
    $tagsChartData[] = $tag['usage_count'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>AdminPanel | DevHub</title>
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- Plotly.js -->
    <script src="https://cdn.plot.ly/plotly-3.0.1.min.js" charset="utf-8"></script>
    <!-- Функции создания графиков -->
    <script src="includes/adm_charts.js"></script>
</head>
<body>
    <div class="admin-wrapper">
        <nav class="admin-sidebar">
            <div class="admin-sidebar-header">
                <h3>DevHub Admin</h3>
            </div>
            <ul class="admin-sidebar-menu">
                <li><a href="adm_index.php" <?php echo ($active_page == 'dashboard') ? 'class="active"' : ''; ?>><img src="../icons/anim/ic-Graph.gif" alt="📊" class="nav-icon" style="width: 35px; height: 35px;"> Главная</a></li>
                <li><a href="adm_users.php" <?php echo ($active_page == 'users') ? 'class="active"' : ''; ?>><img src="../icons/anim/ic-Users.gif" alt="👥" class="nav-icon" style="width: 35px; height: 35px;"> Пользователи</a></li>
                <li><a href="adm_posts.php" <?php echo ($active_page == 'posts') ? 'class="active"' : ''; ?>><img src="../icons/anim/ic-Post.gif" alt="📝" class="nav-icon" style="width: 35px; height: 35px;"> Посты</a></li>
                <li><a href="adm_tags.php" <?php echo ($active_page == 'tags') ? 'class="active"' : ''; ?>><img src="../icons/static/ic-Tag_framed.png" alt="🏷️" class="nav-icon" style="width: 35px; height: 35px;"> Теги</a></li>
                <li><a href="adm_comments.php" <?php echo ($active_page == 'comments') ? 'class="active"' : ''; ?>><img src="../icons/anim/ic-Comment.gif" alt="💬" class="nav-icon" style="width: 35px; height: 35px;"> Комментарии</a></li>
                <li><a href="../logout.php" class="logout"><img src="../icons/anim/ic-Exit.gif" alt="🚪" class="nav-icon" style="width: 35px; height: 35px;"> Выход</a></li>
            </ul>
        </nav>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1>Панель управления</h1>
                <div class="admin-user">
                    <span>Добро пожаловать в админ-панель</span>
                </div>
            </div>
            
            <div class="admin-stats-grid">
                <div class="admin-stat-card">
                    <div class="admin-stat-value"><?php echo $stats['total_users']; ?></div>
                    <div class="admin-stat-label">Пользователей</div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-value"><?php echo $stats['total_posts']; ?></div>
                    <div class="admin-stat-label">Публикаций</div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-value"><?php echo $stats['total_drafts']; ?></div>
                    <div class="admin-stat-label">Черновиков</div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-value"><?php echo $stats['total_comments']; ?></div>
                    <div class="admin-stat-label">Комментариев</div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-value"><?php echo $stats['total_likes']; ?></div>
                    <div class="admin-stat-label">Лайков</div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-value"><?php echo $stats['avg_rating']; ?></div>
                    <div class="admin-stat-label">Средний рейтинг</div>
                </div>
            </div>
            
            <!-- Топ-10 постов по рейтингу -->
            <div class="admin-section">
                <div class="section-header">
                    <h2><img src="../icons/anim/ic-Cup.gif" alt="🏆" class="nav-icon" style="width: 35px; height: 35px;"> Топ-10 постов по рейтингу</h2>
                    <button class="chart-toggle-btn" data-target="ratingChart"> Показать график</button>
                </div>
                <div class="admin-top-list">
                    <?php 
                    $index = 0;
                    foreach ($topPostsRating as $post): 
                        if ($index == 0) {
                            $height = 100;
                        } elseif ($index == 1) {
                            $height = 80;
                        } elseif ($index == 2) {
                            $height = 60;
                        } else {
                            $height = 50;
                        }
                    ?>
                        <div class="admin-top-item" style="height: <?php echo $height; ?>px;">
                            <div class="admin-top-rank">#<?php echo $index + 1; ?></div>
                            <div class="admin-top-content">
                                <div class="admin-top-title"><?php echo htmlspecialchars($post['title']); ?></div>
                                <div class="admin-top-meta">
                                    Автор: <?php echo htmlspecialchars($post['author_name']); ?> | 
                                    Рейтинг: <img src="../icons/anim/ic-Star_rate.gif" alt="⭐" class="nav-icon"> <?php echo $post['avRate']; ?> | 
                                    Комментариев: <img src="../icons/anim/ic-Comment.gif" alt="💬" class="nav-icon"> <?php echo $post['comments_count']; ?>
                                </div>
                            </div>
                        </div>
                    <?php 
                        $index++;
                    endforeach; 
                    ?>
                </div>
                <div class="chart-wrapper" id="ratingChartWrapper" style="display: none;">
                    <canvas id="ratingChart" width="800" height="400" style="max-width: 100%; height: auto;"></canvas>
                </div>
            </div>
            
            <!-- Распределение постов по категориям -->
            <div class="admin-section">
                <h2><img src="../icons/anim/ic-Graph.gif" alt="📊" class="nav-icon"> Распределение постов по категориям</h2>
                <div class="admin-category-stats">
                    <?php 
                    $total = 0;
                    foreach ($categoryDistribution as $cat) {
                        $total += $cat['count'];
                    }
                    foreach ($categoryDistribution as $cat): 
                        $percent = ($total > 0) ? ($cat['count'] / $total * 100) : 0;
                    ?>
                        <div class="admin-category-item">
                            <span class="admin-category-name"><?php echo htmlspecialchars($cat['name']); ?></span>
                            <div class="admin-category-bar">
                                <div class="admin-category-fill" style="width: <?php echo $percent; ?>%;"></div>
                            </div>
                            <span class="admin-category-count"><?php echo $cat['count']; ?> постов</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="admin-two-columns">
                <!-- Топ-10 постов по комментариям -->
                <div class="admin-section">
                    <div class="section-header">
                        <h2><img src="../icons/anim/ic-Comment.gif" alt="💬" class="nav-icon" style="width: 35px; height: 35px;"> Топ-10 постов по комментариям</h2>
                        <button class="chart-toggle-btn" data-target="commentsChart">📊 Показать график</button>
                    </div>
                    <div class="admin-top-list">
                        <?php 
                        $index = 0;
                        foreach ($topPostsComments as $post): 
                            if ($index == 0) {
                                $height = 100;
                            } elseif ($index == 1) {
                                $height = 80;
                            } elseif ($index == 2) {
                                $height = 60;
                            } else {
                                $height = 50;
                            }
                        ?>
                            <div class="admin-top-item" style="height: <?php echo $height; ?>px;">
                                <div class="admin-top-rank">#<?php echo $index + 1; ?></div>
                                <div class="admin-top-content">
                                    <div class="admin-top-title"><?php echo htmlspecialchars($post['title']); ?></div>
                                    <div class="admin-top-meta">
                                        Автор: <?php echo htmlspecialchars($post['author_name']); ?> | 
                                        Комментариев: <img src="../icons/anim/ic-Comment.gif" alt="💬" class="nav-icon"> <?php echo $post['comments_count']; ?>
                                    </div>
                                </div>
                            </div>
                        <?php 
                            $index++;
                        endforeach; 
                        ?>
                    </div>
                    <div class="chart-wrapper" id="commentsChartWrapper" style="display: none;">
                        <canvas id="commentsChart" width="800" height="400" style="max-width: 100%; height: auto;"></canvas>
                    </div>
                </div>
                
                <!-- Топ-10 авторов -->
                <div class="admin-section">
                    <div class="section-header">
                        <h2><img src="../icons/static/ic-Crown.png" alt="👑" class="nav-icon" style="width: 35px; height: 35px;"> Топ-10 авторов</h2>
                        <button class="chart-toggle-btn" data-target="authorsChart">📊 Показать график</button>
                    </div>
                    <div class="admin-top-list">
                        <?php 
                        $index = 0;
                        foreach ($topUsers as $user): 
                            if ($index == 0) {
                                $height = 100;
                            } elseif ($index == 1) {
                                $height = 80;
                            } elseif ($index == 2) {
                                $height = 60;
                            } else {
                                $height = 50;
                            }
                        ?>
                            <div class="admin-top-item" style="height: <?php echo $height; ?>px;">
                                <div class="admin-top-rank">#<?php echo $index + 1; ?></div>
                                <div class="admin-top-content">
                                    <div class="admin-top-title"><?php echo htmlspecialchars($user['name']); ?></div>
                                    <div class="admin-top-meta">
                                        Постов: <img src="../icons/anim/ic-Post.gif" alt="📝" class="nav-icon"> <?php echo $user['posts_count']; ?> | 
                                        Комментариев: <img src="../icons/anim/ic-Comment.gif" alt="💬" class="nav-icon"> <?php echo $user['comments_count']; ?>
                                    </div>
                                </div>
                            </div>
                        <?php 
                            $index++;
                        endforeach; 
                        ?>
                    </div>
                    <div class="chart-wrapper" id="authorsChartWrapper" style="display: none;">
                        <canvas id="authorsChart" width="800" height="400" style="max-width: 100%; height: auto;"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Топ-30 тегов -->
            <div class="admin-section">
                <div class="section-header">
                    <h2><img src="../icons/static/ic-Tag_framed.png" alt="🏷️" class="nav-icon" style="width: 35px; height: 35px;"> Топ-30 тегов</h2>
                    <button class="chart-toggle-btn" data-target="tagsChart">📊 Показать график</button>
                </div>
                <div class="admin-tags-cloud">
                    <?php 
                    $maxUsage = isset($topTags[0]['usage_count']) ? $topTags[0]['usage_count'] : 1;
                    foreach ($topTags as $tag): 
                        $size = 12 + ($tag['usage_count'] / $maxUsage) * 24;
                    ?>
                        <span class="admin-tag" style="font-size: <?php echo $size; ?>px; border-color: <?php echo $tag['color_code']; ?>; background-color: <?php echo $tag['color_code']; ?>20;">
                            <?php echo htmlspecialchars($tag['name']); ?>
                            <span class="admin-tag-count">(<?php echo $tag['usage_count']; ?>)</span>
                        </span>
                    <?php endforeach; ?>
                </div>
                <div class="chart-wrapper" id="tagsChartWrapper" style="display: none;">
                    <div id="tagsChart" style="width: 100%; height: 450px;"></div>
                </div>
            </div>

        </main>
    </div>
    <script>
        //Данные для графиков в amd_charts.js
        const ratingLabels = <?php echo json_encode($ratingChartLabels); ?>;
        const ratingData = <?php echo json_encode($ratingChartData); ?>;
        const commentsLabels = <?php echo json_encode($commentsChartLabels); ?>;
        const commentsData = <?php echo json_encode($commentsChartData); ?>;
        const authorsLabels = <?php echo json_encode($authorsChartLabels); ?>;
        const authorsData = <?php echo json_encode($authorsChartData); ?>;
        const tagsLabels = <?php echo json_encode($tagsChartLabels); ?>;
        const tagsData = <?php echo json_encode($tagsChartData); ?>;
    </script>
</body>
</html>