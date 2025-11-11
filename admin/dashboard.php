<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$baseUrl = rtrim($config['base_url'] ?? '', '/');
$rootUrl = preg_replace('#/public$#', '', $baseUrl) ?: $baseUrl;

$totalProjects = (int) $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
$totalMessages = (int) $pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body class="admin">
    <nav class="admin-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="about.php">About Page</a>
        <a href="projects.php">Projects</a>
        <a href="messages.php">Messages</a>
        <a href="<?= $baseUrl ?>/" target="_blank" rel="noopener">View Site</a>
        <a href="logout.php">Logout</a>
    </nav>
    <main class="admin-dashboard">
        <h1>Welcome back, <?= e($_SESSION['admin_username'] ?? '') ?></h1>
        <ul class="admin-stats">
            <li><strong><?= $totalProjects ?></strong> Projects</li>
            <li><a href="messages.php"><strong><?= $totalMessages ?></strong> Messages</a></li>
        </ul>
    </main>
</body>
</html>
