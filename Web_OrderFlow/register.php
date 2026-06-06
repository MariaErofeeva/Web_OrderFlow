<?php
require_once 'functions.php';

// Если уже авторизован — перенаправить на главную
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $name     = trim($_POST['name'] ?? '');
    $inn      = trim($_POST['inn'] ?? '');
    $addres   = trim($_POST['addres'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Валидация
    if (empty($name) || empty($addres) || empty($phone) || empty($login) || empty($password)) {
        $error = 'Все поля, кроме ИНН, обязательны для заполнения.';
    } elseif ($password !== $password_confirm) {
        $error = 'Пароли не совпадают.';
    } elseif (strlen($password) < 4) {
        $error = 'Пароль должен содержать не менее 4 символов.';
    } elseif (getUserByLogin($login)) {
        $error = 'Пользователь с таким логином уже существует.';
    } else {
        // 1. Добавляем запись в Customers
        $customer_id = addCustomer($name, $inn, $addres, $phone);
        if (!$customer_id) {
            $error = 'Ошибка при создании заказчика. Попробуйте позже.';
        } else {
            // 2. Добавляем запись в users
            $result = addUser($login, $password, 'user', $customer_id);
            if ($result) {
                $success = 'Регистрация прошла успешно! Теперь вы можете войти в систему.';
                // Очищаем поля формы (опционально)
                $_POST = [];
            } else {
                $error = 'Ошибка при создании учётной записи. Попробуйте позже.';
                // Откат: удаляем только что созданного заказчика (для целостности)
                // (упрощённо, но можно реализовать)
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация пользователя</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Регистрация нового пользователя</h1>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
        <p><a href="login.php">Перейти на страницу входа</a></p>
    <?php else: ?>
        <form method="post">
            <label>ФИО или название организации *</label>
            <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>

            <label>ИНН (необязательно)</label>
            <input type="text" name="inn" value="<?= htmlspecialchars($_POST['inn'] ?? '') ?>">

            <label>Адрес *</label>
            <input type="text" name="addres" value="<?= htmlspecialchars($_POST['addres'] ?? '') ?>" required>

            <label>Телефон *</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>

            <label>Логин *</label>
            <input type="text" name="login" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" required>

            <label>Пароль * (минимум 4 символа)</label>
            <input type="password" name="password" required>

            <label>Повторите пароль *</label>
            <input type="password" name="password_confirm" required>

            <button type="submit">Зарегистрироваться</button>
        </form>
        <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
    <?php endif; ?>
</div>
</body>
</html>