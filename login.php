<?php
session_start();
$page_title = "DevHub | Вход в учётную запись";

require_once 'config.php';

$conn = new mysqli(
    $config['db_host'],
    $config['db_user'],
    $config['db_pass'],
    $config['db_name']
);
$conn->set_charset("utf8");

$message = "";
$messageClass = "";

if(isset($_POST["log"])){
    $login = $_POST["login"];
    $password = $_POST["password"];

    if($login == "" || $password == ""){
        $message = "Заполните логин и пароль!";
        $messageClass = "err";
    } else {
        if ($login === 'devAdminHubber' && $password === '4yUcdGi3y3Duza8') {
            $_SESSION['is_admin'] = true;
            header("Location: /adminpanel/adm_index.php");
            exit;
        }
        
        $sql = "SELECT Id_U, login, username FROM Users WHERE login='$login' AND password='$password' LIMIT 1";
        $result = $conn->query($sql);

        if($result && $result->num_rows > 0){
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['Id_U'];
            $_SESSION['login']   = $user['login'];
            $_SESSION['username'] = $user['username'];
            header("Location: /cabinet.php");
            exit;
        } else {
            $message = "Неверный логин или пароль!";
            $messageClass = "err";
        }
    }
}

require_once 'header.php';
?>

<div class="box">
    <h2>Вход</h2>

    <?php if($message != ""): ?>
        <div class="msg <?= $messageClass ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label>Логин</label>
        <input type="text" name="login" class="input-zone">

        <label>Пароль</label>
        <input type="password" name="password" class="input-zone">

        <button type="submit" name="log" class="button">Войти</button>
    </form>
</div>
