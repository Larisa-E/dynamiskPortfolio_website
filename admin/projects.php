<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

ensure_project_video_column($pdo);

$stmt = $pdo->query('SELECT id, title, slug, created_at FROM projects ORDER BY created_at DESC');
$projects = $stmt->fetchAll();
$baseUrl = rtrim($config['base_url'] ?? '', '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Projects</title>
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
    <main class="admin-projects">
        <header class="admin-projects__header">
            <h1>Projects</h1>
            <a class="btn" href="project_new.php">Add project</a>
        </header>
        <?php if (!$projects): ?>
            <p>No projects yet. Create one to populate the public site.</p>
        <?php else: ?>
            <table class="admin-projects__table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td><?= e($project['title']) ?></td>
                            <td><?= e($project['slug']) ?></td>
                            <td><?= e($project['created_at']) ?></td>
                            <td>
                                <a href="project_edit.php?id=<?= (int) $project['id'] ?>">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>
</html>
