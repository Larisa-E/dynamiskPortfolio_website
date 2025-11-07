<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $short = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $tech = trim($_POST['tech'] ?? '');
    $slug = make_slug($title);
    $imageName = null;

    if ($title === '') {
        $errors[] = 'Title is required.';
    }

    if (!$errors) {
        if (!empty($_FILES['image']['name'])) {
            $info = @getimagesize($_FILES['image']['tmp_name']);
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

            if (!$info || !isset($allowed[$info['mime']])) {
                $errors[] = 'Please upload a valid JPG, PNG, or WEBP image.';
            } else {
                $ext = $allowed[$info['mime']];
                $imageName = uniqid('proj_', true) . '.' . $ext;
                $destination = __DIR__ . '/../public/uploads/' . $imageName;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    $errors[] = 'Image upload failed.';
                }
            }
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO projects (title, slug, short_description, description, image, url, tech) VALUES (:title, :slug, :short, :description, :image, :url, :tech)');
        $stmt->execute([
            'title' => $title,
            'slug' => $slug,
            'short' => $short,
            'description' => $description,
            'image' => $imageName,
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
        <a href="projects.php">Projects</a>
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
            <label>Image
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
            </label>
            <button type="submit">Create project</button>
        </form>
    </main>
</body>
</html>
