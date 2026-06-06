<?php
session_start(); // Убедитесь, что session_start() есть в начале
require_once 'functions.php';

// Проверка авторизации
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'];
$login = $_SESSION['login'];
$customer_id = $_SESSION['customer_id'] ?? null;

$orders = [];
if ($role === 'user' && $customer_id) {
    $orders = getOrdersByCustomerId($customer_id);
} elseif ($role === 'admin') {
    $orders = getAllOrders();
}

// Проверяем флаг успешной авторизации
$showSuccessMessage = false;
if (isset($_SESSION['login_success']) && $_SESSION['login_success'] === true) {
    $showSuccessMessage = true;
    unset($_SESSION['login_success']);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <?php if ($showSuccessMessage): ?>
        <div class="success-message" style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
           Вы успешно авторизовались!
        </div>
    <?php endif; ?>

    <h1>Добро пожаловать, <?= htmlspecialchars($login) ?> (<?= $role ?>)</h1>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <p><a href="admin.php">Управление пользователями</a></p>
        <a href="cost_price.php">Расчёт себестоимости заказов</a>
        <p><a href="transfer.php">Валидация данных (Transfer Simulator)</a></p>
    <?php endif; ?>

    <h2>Список заказов</h2>
    <?php if (empty($orders)): ?>
        <p>Нет доступных заказов.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>№ заказа</th>
                    <th>Дата</th>
                    <th>Сумма (руб.)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['number_of_order']) ?></td>
                    <td><?= htmlspecialchars($order['date']) ?></td>
                    <td><?= number_format($order['total_sum'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p style="margin-top: 20px;"><a href="logout.php">Выйти</a></p>
</div>
</body>
</html>