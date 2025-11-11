<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

ensure_project_video_column($pdo);

$errors = [];
$baseUrl = rtrim($config['base_url'] ?? '', '/');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $short = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $tech = trim($_POST['tech'] ?? '');
    $videoUrl = trim($_POST['demo_video_url'] ?? '');
    $slug = make_slug($title);
    $imageName = null;

    if ($title === '') {
        $errors[] = 'Title is required.';
    }

    if ($videoUrl !== '' && !filter_var($videoUrl, FILTER_VALIDATE_URL)) {
        $errors[] = 'Demo video must be a valid URL.';
    }

    if (!$errors) {
        if (!empty($_FILES['image']['name'])) {
            $info = @getimagesize($_FILES['image']['tmp_name']);
            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
            ];

            if (!$info || !isset($allowed[$info['mime']])) {
                $errors[] = 'Please upload a valid JPG, PNG, or WEBP image.';
            } else {
                $ext = $allowed[$info['mime']];
                $imageName = uniqid('proj_', true) . '.' . $ext;
                $uploadDir = __DIR__ . '/../public/uploads/';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                    $errors[] = 'Image directory is missing and could not be created.';
                }
                $destination = $uploadDir . $imageName;
                if (!$errors && !move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    $errors[] = 'Image upload failed.';
                }
            }
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO projects (title, slug, short_description, description, image, demo_video_url, url, tech) VALUES (:title, :slug, :short, :description, :image, :video, :url, :tech)');
        $stmt->execute([
            'title' => $title,
            'slug' => $slug,
            'short' => $short,
            'description' => $description,
            'image' => $imageName,
            'video' => $videoUrl !== '' ? $videoUrl : null,
            'url' => $url,
            'tech' => $tech,
        ]);

        header('Location: projects.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Project</title>
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
    <main class="admin-form">
        <h1>New Project</h1>
        <?php if ($errors): ?>
            <ul class="admin-form__errors">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <label>Title
                <input type="text" name="title" value="<?= e($_POST['title'] ?? '') ?>" required>
            </label>
            <label>Short description
                <textarea name="short_description" rows="3"><?= e($_POST['short_description'] ?? '') ?></textarea>
            </label>
            <label>Full description
                <textarea name="description" rows="6"><?= e($_POST['description'] ?? '') ?></textarea>
            </label>
            <label>Tech stack
                <input type="text" name="tech" value="<?= e($_POST['tech'] ?? '') ?>">
            </label>
            <label>Project URL
                <input type="url" name="url" value="<?= e($_POST['url'] ?? '') ?>">
            </label>
            <label>Demo video URL (optional)
                <input type="url" name="demo_video_url" value="<?= e($_POST['demo_video_url'] ?? '') ?>" placeholder="https://youtu.be/...">
            </label>
            <label>Image
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
            </label>
            <button type="submit">Create project</button>
        </form>
    </main>
</body>
</html>
