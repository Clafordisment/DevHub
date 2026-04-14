<?php
// Образец файла-конфига для получения доступа к БД и настройки логина и пароля админа (для активации админ-панели)
// Можно скопировать этот файл в config.php и заполнить

$config = [
    'db_host' => 'localhost',
    'db_user' => 'your_username',
    'db_pass' => 'your_password',
    'db_name' => 'your_database'
];

$admin_config = [
    'login' => 'admin_login',
    'password' => 'admin_password'
];