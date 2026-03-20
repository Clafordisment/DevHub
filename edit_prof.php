<?php
session_start();

$page_title = "DevHub | Редактирование профиля";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "DevHub_mainDB";

$conn = new mysqli($host, $user, $pass, $dbname);
$conn->set_charset("utf8");

$message = "";
$messageClass = "";

$user_id = $_SESSION["user_id"];
$sql = "SELECT Id_U, login, username, password FROM users WHERE Id_U = $user_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    $current_login = $user_data['login'];
    $current_password = $user_data['password'];
    $current_username = $user_data['username'];
}

if (isset($_POST["change_login"])) {
    $new_login = $_POST["new_login"];
    
    if (empty($new_login)) {
        $message = "Логин не может быть пустым!";
        $messageClass = "err";
    } else {
        $check_sql = "SELECT Id_U FROM users WHERE login = '$new_login' AND Id_U != $user_id";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            $message = "Логин '$new_login' уже занят!";
            $messageClass = "err";
        } else {
            $update_sql = "UPDATE users SET login = '$new_login' WHERE Id_U = $user_id";
            if ($conn->query($update_sql) === TRUE) {
                $_SESSION['login'] = $new_login;
                $message = "Логин успешно изменен на '$new_login'!";
                $messageClass = "ok";
                $current_login = $new_login;
            } else {
                $message = "Ошибка: " . $conn->error;
                $messageClass = "err";
            }
        }
    }
}

if (isset($_POST["change_username"])) {
    $new_username = $_POST["new_username"];

    $update_sql = "UPDATE users SET username = " . ($new_username === '' ? "NULL" : "'$new_username'") . "WHERE Id_U = $user_id";
    if ($conn->query($update_sql) === TRUE) {
        $_SESSION['username'] = $new_username;
        $current_username = $new_username;
    }
    else {
        $message = "Ошибка: " . $conn->error;
        $messageClass = "err";
    }
}


if (isset($_POST["change_password"])) {
    $new_password = $_POST["new_password"];
    
    if (empty($new_password)) {
        $message = "Пароль не может быть пустым!";
        $messageClass = "err";
    } else {
        $update_sql = "UPDATE users SET password = '$new_password' WHERE Id_U = $user_id";
        if ($conn->query($update_sql) === TRUE) {
            $message = "Пароль успешно изменен!";
            $messageClass = "ok";
        } else {
            $message = "Ошибка: " . $conn->error;
            $messageClass = "err";
        }
    }
}


// Удаление аккаунта вместе с его постами
if(isset($_POST["delete_account"])) {
    $delete_posts_sql = "DELETE FROM Posts WHERE id_u = $user_id";
    $conn->query($delete_posts_sql);

    $delete_sql = "DELETE FROM users WHERE Id_U = $user_id";
    if ($conn->query($delete_sql) === TRUE) {
        session_destroy();
        header("Location: index.php?message=Аккаунт успешно удален&class=ok");
        exit;
    } else {
        $message = "Ошибка при удалении аккаунта: " . $conn->error;
        $messageClass = "err";
    }
}

require_once 'header.php';
?>




<div class="box" style="width: 1000px;">
    <h2>Редактирование профиля</h2>
    
    <div style="text-align: center; margin-top: 20px;">
        <a href="cabinet.php" class="back-link">Вернуться в кабинет</a>
    </div>

    <?php if($message != ""): ?>
        <div class="msg <?= $messageClass ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>
    
    <h3>Текущие данные</h3>
    <p>Логин: <?= $current_login ?></p>
    <p>Пароль: <?= $current_password ?></p>
    <p>Имя пользователя: <?= !empty($current_username) ? $current_username : '<span style="color: #666;">не задано</span>' ?></p>

    <hr>
    
    <h3>Изменить логин</h3>
    <form method="POST">
        <label>Новый логин:</label>
        <input type="text" name="new_login" value="<?= $current_login ?>" class="input-zone" required>
        <button type="submit" name="change_login" class="button">Изменить логин</button>
    </form>
    
    <hr>
        
    <h3>Изменить имя пользователя (для отображения)</h3>
    <form method="POST">
        <label>Новое имя пользователя (можно оставить пустым):</label>
        <input type="text" name="new_username" value="<?= htmlspecialchars($current_username) ?>" class="input-zone" placeholder="Введите имя для отображения">
        <button type="submit" name="change_username" class="button">Изменить имя</button>
    </form>
    
    <hr>

    <h3>Изменить пароль</h3>
    <form method="POST">
        <label>Новый пароль:</label>
        <input type="password" name="new_password" class="input-zone" required>
        <button type="submit" name="change_password" class="button">Изменить пароль</button>
    </form>
    
    <hr>

    <form method="POST" onsubmit="return confirm('Удалить аккаунт?');">
        <button type="submit" name="delete_account" class="button danger-button">Удалить аккаунт</button>
    </form>
</div>

