<?php
// config.php — подключение к БД и настройки
session_start();

$db_host = '134.90.167.42';
$db_port = 10306;                // порт теперь целое число
$db_user = 'Erofeeva';
$db_pass = '_q0N_r';
$db_name = 'project_Erofeeva';

// Правильный порядок параметров: host, user, password, database, port
$db = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

if ($db->connect_error) {
    die("Ошибка подключения к БД: " . $db->connect_error);
}

// Опционально: установка кодировки UTF-8
$db->set_charset("utf8mb4");
?>