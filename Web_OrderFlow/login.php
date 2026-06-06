<?php
session_start(); // Убедитесь, что session_start() есть в начале
require_once 'functions.php';

// Проверка на существующую сессию
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $captchaOk = $_POST['captchaOk'] ?? '';

    if (empty($login) || empty($password)) {
        $error = 'Логин и пароль обязательны для заполнения.';
    } elseif ($captchaOk !== '1') {
        $error = 'Соберите пазл правильно перед входом.';
    } else {
        $user = getUserByLogin($login);
        if (!$user || !password_verify($password, $user['password'])) {
            updateFailedAttempts($login, false);
            $error = 'Вы ввели неверный логин или пароль. Пожалуйста проверьте ещё раз введённые данные.';
        } elseif ($user['is_blocked']) {
            $error = 'Вы заблокированы. Обратитесь к администратору.';
        } else {
            updateFailedAttempts($login, true);
            
            // Устанавливаем сессионные переменные
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['login'] = $user['login'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['customer_id'] = $user['customer_id'];
            $_SESSION['login_success'] = true;
            
            // Принудительно сохраняем сессию
            session_write_close();
            
            // Редирект
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Авторизация</title>
    <link rel="stylesheet" href="style.css">
    <script src="captcha.js" defer></script>
</head>
<body>
<div class="container">
    <h1>Вход в систему</h1>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <label>Логин:</label>
        <input type="text" name="login" required>
        <label>Пароль:</label>
        <input type="password" name="password" required>

        <h3>Соберите пазл</h3>
        <div id="captchaContainer" class="captcha-container"></div>
        <button type="button" id="checkCaptchaBtn">Проверить пазл</button>
        <button type="button" id="resetCaptchaBtn">Сбросить пазл</button>
        <div id="captchaError" class="error"></div>
        <input type="hidden" name="captchaOk" id="captchaOk" value="">

        <button type="submit" id="loginBtn" disabled>Войти</button>
    </form>
</div>
</body>
</html>