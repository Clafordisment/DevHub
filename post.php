<?php
session_start();

$page_title = "DevHub | Публикация";
require_once 'header.php';

require_once 'config.php';

$conn = new mysqli(
    $config['db_host'],
    $config['db_user'],
    $config['db_pass'],
    $config['db_name']
);
$conn->set_charset("utf8");

$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$post = null;

if ($postId > 0) {
    $sql = "
        SELECT 
            p.id_p,
            p.title,
            p.content,
            COALESCE(NULLIF(u.username, ''), u.login) AS author_name,
            p.create_at,
            p.avRate
        FROM Posts p
        JOIN Users u ON p.id_u = u.Id_U
        WHERE p.id_p = {$postId} AND p.isNote = 0
        LIMIT 1
    ";

    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $post = $result->fetch_assoc();
    }
}

$comments = [];
if ($postId > 0) {
    $commentsSql = "
        SELECT 
            c.id_c,
            c.content,
            c.created_at,
            c.likes_count,
            COALESCE(NULLIF(u.username, ''), u.login) AS comment_author,
            u.Id_U as author_id
        FROM Comments c
        JOIN Users u ON c.id_u = u.Id_U
        WHERE c.id_p = {$postId}
        ORDER BY c.created_at DESC
    ";
    $commentsResult = $conn->query($commentsSql);
    if ($commentsResult) {
        while ($row = $commentsResult->fetch_assoc()) {
            $comments[] = $row;
        }
    }
}

$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

$votesCount = 0;
if ($postId > 0) {
    $votesSql = "SELECT COUNT(*) as count FROM post_rates WHERE id_p = $postId";
    $votesResult = $conn->query($votesSql);
    if ($votesResult) {
        $votesData = $votesResult->fetch_assoc();
        $votesCount = (int)$votesData['count'];
    }
}

$userRating = 0;
if ($postId > 0 && isset($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
    $userRateSql = "SELECT rate FROM post_rates WHERE id_p = $postId AND id_u = $userId LIMIT 1";
    $userRateResult = $conn->query($userRateSql);
    if ($userRateResult && $userRateResult->num_rows > 0) {
        $userRateData = $userRateResult->fetch_assoc();
        $userRating = (float)$userRateData['rate'];
    }
}


?>



<div id="post-data" 
     data-post-id="<?php echo $postId; ?>" 
     data-current-user-id="<?php echo $currentUserId; ?>"
     style="display: none;"></div>

<div class="box">
    <?php if ($post): ?>
        <div class="post-full-header">
            <a href="index.php" class="back-button">
                &#8592; Назад
            </a>
            <h1 class="post-full-title">
                <?php echo htmlspecialchars($post['title']); ?>
            </h1>
        </div>
        <div class="post-full-author">
            Автор: <?php echo htmlspecialchars($post['author_name']); ?>
        </div>
        <div class="post-full-content">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>
    <?php else: ?>
        <div class="msg err">
            Публикация не найдена.
        </div>
        <div style="margin-top: 20px; text-align: center;">
            <a href="index.php" class="back-link">&#8592; Вернуться на главную</a>
        </div>
    <?php endif; ?>
</div>

<?php if ($post): ?>
<div class="rating-container" data-post-id="<?php echo $postId; ?> "data-user-rating="<?php echo $userRating; ?>">
    <div class="rating-title">Оцените публикацию</div>
    <div class="stars" id="star-rating">
        <span class="star" data-value="1">☆</span>
        <span class="star" data-value="2">☆</span>
        <span class="star" data-value="3">☆</span>
        <span class="star" data-value="4">☆</span> 
        <span class="star" data-value="5">☆</span>
    </div>
    <div class="rating-value">
        Средняя оценка: <span class="rating-current"><?php echo isset($post['avRate']) ? $post['avRate'] : '0'; ?></span> / 5
        <span class="rating-votes">(<?php echo $votesCount; ?> голосов)</span>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star');
    const ratingContainer = document.querySelector('.rating-container');
    const ratingCurrent = document.querySelector('.rating-current');
    const ratingVotes = document.querySelector('.rating-votes');
    let currentRating = parseFloat(ratingContainer.dataset.userRating) || 0;

    if(currentRating > 0) {
        highlightStars(currentRating);
    }    

    stars.forEach(star => {
        star.addEventListener('mouseenter', function() {
            const value = parseInt(this.dataset.value);
            highlightStars(value);
        });
        
        star.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const width = rect.width;
            const value = parseInt(this.dataset.value);
            if (x < width / 2) {
                highlightStars(value - 0.5);
            } else {
                highlightStars(value);
            }
        });
        
        star.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const width = rect.width;
            const value = parseInt(this.dataset.value);
            let rating = (x < width / 2) ? value - 0.5 : value;
            submitRating(rating);
        });
        
        star.addEventListener('mouseleave', function() {
            highlightStars(currentRating);
        });
    });
    
    function highlightStars(rating) {
        stars.forEach(star => {
            const starValue = parseInt(star.dataset.value);
            star.textContent = '☆';
            star.classList.remove('active');
            if (rating >= starValue) {
                star.textContent = '★';
                star.classList.add('active');
            } else if (rating > starValue - 1 && rating < starValue) {
                star.textContent = '★';
                star.classList.add('active');
            }
        });
    }
    
    async function submitRating(rating) {
        const postId = ratingContainer.dataset.postId;
        try {
            const response = await fetch('ajax/rate_post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_id: postId, rating: rating })
            });
            const data = await response.json();
            if (data.success) {
                currentRating = rating;
                highlightStars(rating);
                ratingCurrent.textContent = data.avg_rating;
                ratingVotes.textContent = `(${data.votes_count} голосов)`;
                showRatingMessage('Оценка сохранена!', 'ok');
            } else {
                showRatingMessage(data.error || 'Ошибка', 'err');
            }
        } catch (error) {
            showRatingMessage('Ошибка соединения', 'err');
        }
    }
    
    function showRatingMessage(text, type) {
        const oldMsg = document.querySelector('.rating-message');
        if (oldMsg) oldMsg.remove();
        const messageDiv = document.createElement('div');
        messageDiv.className = `msg ${type} rating-message`;
        messageDiv.textContent = text;
        messageDiv.style.marginTop = '10px';
        ratingContainer.appendChild(messageDiv);
        setTimeout(() => messageDiv.remove(), 3000);
    }
});
</script>

<div class="box" style="margin-top: 0; border-radius: 12px;">
    <h2 style="text-align: left; margin-bottom: 25px;">Комментарии</h2>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <form class="comment-form" id="comment-form">
            <label>Оставить комментарий</label>
            <textarea name="comment_content" class="comment-input" placeholder="Введите ваш комментарий..." required></textarea>
            <button type="submit" name="add_comment" class="comment-submit">Отправить</button>
        </form>
        <hr>
    <?php else: ?>        
        <div class="login-to-comment">
            <a href="login.php" style="color: #b0b0b0;">Войдите</a>, чтобы оставить комментарий
        </div>
    <?php endif; ?>
    
    <div class="comments-wrapper">
        <?php if (count($comments) > 0): ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment-card" data-comment-id="<?php echo $comment['id_c']; ?>">
                    <div class="comment-header">
                        <span class="comment-author"><?php echo htmlspecialchars($comment['comment_author']); ?></span>
                        <span class="comment-date">
                            <?php echo isset($comment['created_at']) ? date('d.m.Y H:i', strtotime($comment['created_at'])) : 'только что'; ?>
                        </span>
                    </div>
                    <p class="comment-content">
                        <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                    </p>
                    <?php if ($currentUserId == $comment['author_id']): ?>
                        <div class="comment-actions">
                            <button class="comment-delete-btn delete-comment-btn" data-comment-id="<?php echo $comment['id_c']; ?>">
                                Удалить
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-comments">Пока нет комментариев. Будьте первым!</p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script src="JS/comments_ajax.js"></script>