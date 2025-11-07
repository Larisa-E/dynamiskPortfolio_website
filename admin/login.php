<?php
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$baseUrl = rtrim($config['base_url'] ?? '', '/');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = 'Username and password are required.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = :username');
        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: dashboard.php');
            exit;
        }

        $errors[] = 'Invalid credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body class="admin">
    <main class="admin-login">
        <h1>Admin Login</h1>
        <?php if ($errors): ?>
            <ul class="admin-login__errors">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post">
            <label>
                Username
                <input type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>" required>
            </label>
            <label>
                Password
                <input type="password" name="password" required>
            </label>
            <button type="submit">Sign in</button>
        </form>
        <p class="admin-login__back"><a href="<?= $baseUrl ?>/">Back to site</a></p>
    </main>
</body>
</html>
