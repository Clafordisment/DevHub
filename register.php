<?php
$page_title = "DevHub | Регистрация учётной записи";
require_once 'header.php';

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "DevHub_mainDB";

$conn = new mysqli($host, $user, $pass, $dbname);
$conn->set_charset("utf8");

$message = "";
$messageClass = "";

if(isset($_POST["reg"])){
    $login = $_POST["login"];
    $password = $_POST["password"];
    
    if($login == "" || $password == ""){
        $message = "Заполните логин и пароль!";
        $messageClass = "err";
    } else {
        $check_login_sql = "SELECT login FROM users WHERE login = '$login'";
        $check_result = $conn->query($check_login_sql);
        
        if($check_result->num_rows > 0){
            $message = "Логин '$login' уже существует";
            $messageClass = "err";
        } else {
            $sql = "INSERT INTO users (login, password) VALUES ('$login', '$password')";
            
            if($conn->query($sql) === TRUE){
                $message = "Регистрация успешна!";
                $messageClass = "ok";
            } else {
                $message = "Ошибка регистрации: " . $conn->error;
                $messageClass = "err";
            }
        }
    }
}
?>

<div class="box">
    <h2>Регистрация</h2>

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

        <button type="submit" name="reg" class="button">Зарегистрироваться</button>
    </form>
</div>
