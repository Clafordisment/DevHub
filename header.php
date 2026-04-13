<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?php echo isset($page_title) ? $page_title : 'DevHub'; ?></title>
    <link rel="stylesheet" href="CSS/main.css">
    <link rel="stylesheet" href="CSS/nav.css">
    <link rel="stylesheet" href="CSS/layout.css">
    <link rel="stylesheet" href="CSS/components.css">
    <link rel="stylesheet" href="CSS/typography.css">
    <link rel="stylesheet" href="CSS/post_cards.css">
    <link rel="stylesheet" href="CSS/post_page.css">
    <link rel="stylesheet" href="CSS/comments.css">
    <link rel="stylesheet" href="CSS/rating.css">
    <link rel="stylesheet" href="CSS/categories.css">
    <link rel="stylesheet" href="CSS/search.css">
    <link rel="stylesheet" href="CSS/modal_overlay.css">
    <link rel="stylesheet" href="CSS/create_pub_forms.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
</head>
<body>

<nav class="top-nav">
    <div class="nav-container">
        <div class="nav-item">
            <a href="index.php">Главная</a>
        </div>
        
        <div class="nav-item dropdown">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="#" class="dropbtn">Создать публикацию</a>
                <div class="dropdown-content">
                    <a href="create_publication.php">Создать новую</a>
                    <a href="drafts.php">Просмотр черновиков</a>
                    </div>
            <?php else: ?>
                <a class="dropbtn disabled-link">Создать публикацию</a>
            <?php endif; ?>
        </div>
        
        <div class="nav-search search-container">
            <input type="text" placeholder="Поиск публикаций..." class="search-input" id="search-input">
        </div>
        
        <div class="nav-item dropdown right-dropdown">
            <a href="#" class="user-dropbtn">
                <?php if(isset($_SESSION['login'])): ?>
                    <?php
                    $displayName = $_SESSION['login'];
                    if (isset($_SESSION['username']) && !empty($_SESSION['username'])) 
                        {
                            $displayName = $_SESSION['username'];   
                        }
                        echo $displayName;
                    ?> 
                <?php else: ?>
                    Аккаунт
                <?php endif ?>
            </a>
            <div class="dropdown-content">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="cabinet.php">Просмотреть профиль</a>
                    <a href="logout.php">Выйти из аккаунта</a>
                <?php else: ?>
                    <a href="login.php">Авторизация</a>
                    <a href="register.php">Регистрация</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
