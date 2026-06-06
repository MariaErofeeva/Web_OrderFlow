<?php
require_once 'functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: index.php');
    exit;
}

$action = $_GET['action'] ?? 'list';
$error = '';

// Обработка добавления
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $customer_id = ($role === 'user' && !empty($_POST['customer_id'])) ? (int)$_POST['customer_id'] : null;

    if (getUserByLogin($login)) {
        $error = "Пользователь с логином '$login' уже существует.";
    } else {
        addUser($login, $password, $role, $customer_id);
        header('Location: admin.php?action=list');
        exit;
    }
}

// Обработка редактирования
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $customer_id = ($role === 'user' && !empty($_POST['customer_id'])) ? (int)$_POST['customer_id'] : null;
    $unblock = isset($_POST['unblock']);
    updateUser($id, $password, $role, $unblock, $customer_id);
    header('Location: admin.php?action=list');
    exit;
}

// Обработка удаления
if ($action === 'delete' && isset($_GET['id'])) {
    deleteUser((int)$_GET['id']);
    header('Location: admin.php?action=list');
    exit;
}

$users = getAllUsers();
$customers = getAllCustomers();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Администрирование</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Управление пользователями</h1>
    <p><a href="admin.php?action=add">Добавить пользователя</a> | <a href="index.php">На главную</a></p>

    <?php if ($action === 'add'): ?>
        <h2>Добавление пользователя</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <label>Логин:</label>
            <input type="text" name="login" required>
            <label>Пароль:</label>
            <input type="password" name="password" required>
            <label>Роль:</label>
            <select name="role" id="roleSelectAdd">
                <option value="user">Пользователь</option>
                <option value="admin">Администратор</option>
            </select>
            <div id="customerDivAdd" style="display: none;">
                <label>Привязать к заказчику:</label>
                <select name="customer_id">
                    <option value="">-- не привязывать --</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Сохранить</button>
            <a href="admin.php?action=list">Отмена</a>
        </form>
        <script>
            document.getElementById('roleSelectAdd').addEventListener('change', function() {
                document.getElementById('customerDivAdd').style.display = this.value === 'user' ? 'block' : 'none';
            });
        </script>

    <?php elseif ($action === 'edit' && isset($_GET['id'])): ?>
        <?php 
        $user = null;
        foreach ($users as $u) {
            if ($u['id'] == $_GET['id']) {
                $user = $u;
                break;
            }
        }
        ?>
        <?php if ($user): ?>
            <h2>Редактирование: <?= htmlspecialchars($user['login']) ?></h2>
            <form method="post">
                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                <label>Новый пароль (оставьте пустым, чтобы не менять):</label>
                <input type="password" name="password">
                <label>Роль:</label>
                <select name="role" id="roleSelectEdit">
                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Пользователь</option>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Администратор</option>
                </select>
                <div id="customerDivEdit" style="display: <?= $user['role'] === 'user' ? 'block' : 'none' ?>;">
                    <label>Привязать к заказчику:</label>
                    <select name="customer_id">
                        <option value="">-- не привязывать --</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($user['customer_id'] == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($user['is_blocked']): ?>
                    <label><input type="checkbox" name="unblock"> Снять блокировку</label>
                <?php endif; ?>
                <button type="submit">Сохранить</button>
                <a href="admin.php?action=list">Отмена</a>
            </form>
            <script>
                document.getElementById('roleSelectEdit').addEventListener('change', function() {
                    document.getElementById('customerDivEdit').style.display = this.value === 'user' ? 'block' : 'none';
                });
            </script>
        <?php else: ?>
            <div class="error">Пользователь не найден</div>
        <?php endif; ?>

    <?php else: ?>
        <h2>Список пользователей</h2>
        <table>
            <thead>
                <tr><th>ID</th><th>Логин</th><th>Роль</th><th>Заказчик (ID)</th><th>Заблокирован</th><th>Действия</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['login']) ?></td>
                    <td><?= $user['role'] ?></td>
                    <td><?= $user['customer_id'] ?? '—' ?></td>
                    <td><?= $user['is_blocked'] ? 'Да' : 'Нет' ?></td>
                    <td class="actions">
                        <a href="admin.php?action=edit&id=<?= $user['id'] ?>">✏️ Редактировать</a>
                        <a href="admin.php?action=delete&id=<?= $user['id'] ?>" onclick="return confirm('Удалить пользователя?')">🗑️ Удалить</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>