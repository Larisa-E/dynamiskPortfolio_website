<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare('SELECT * FROM projects WHERE id = :id');
$stmt->execute(['id' => $id]);
$project = $stmt->fetch();

if (!$project) {
    http_response_code(404);
    die('Project not found.');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $short = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $tech = trim($_POST['tech'] ?? '');
    $imageName = $project['image'];

    if ($title === '') {
        $errors[] = 'Title is required.';
    }

    if ($slug === '') {
        $slug = make_slug($title);
    }

    if (!$errors && !empty($_FILES['image']['name'])) {
        $info = @getimagesize($_FILES['image']['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

        if (!$info || !isset($allowed[$info['mime']])) {
            $errors[] = 'Please upload a valid JPG, PNG, or WEBP image.';
        } else {
            $ext = $allowed[$info['mime']];
            $newName = uniqid('proj_', true) . '.' . $ext;
            $destination = __DIR__ . '/../public/uploads/' . $newName;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $errors[] = 'Image upload failed.';
            } else {
                if ($imageName) {
                    $oldPath = __DIR__ . '/../public/uploads/' . $imageName;
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $imageName = $newName;
            }
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE projects SET title = :title, slug = :slug, short_description = :short, description = :description, image = :image, url = :url, tech = :tech WHERE id = :id');
        $stmt->execute([
            'title' => $title,
            'slug' => $slug,
            'short' => $short,
            'description' => $description,
            'image' => $imageName,
            'url' => $url,
            'tech' => $tech,
            'id' => $project['id'],
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
    <title>Edit Project</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body class="admin">
    <nav class="admin-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="projects.php">Projects</a>
        <a href="logout.php">Logout</a>
    </nav>
    <main class="admin-form">
        <h1>Edit Project</h1>
        <?php if ($errors): ?>
            <ul class="admin-form__errors">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <label>Title
                <input type="text" name="title" value="<?= e($_POST['title'] ?? $project['title']) ?>" required>
            </label>
            <label>Slug
                <input type="text" name="slug" value="<?= e($_POST['slug'] ?? $project['slug']) ?>" required>
            </label>
            <label>Short description
                <textarea name="short_description" rows="3"><?= e($_POST['short_description'] ?? $project['short_description']) ?></textarea>
            </label>
            <label>Full description
                <textarea name="description" rows="6"><?= e($_POST['description'] ?? $project['description']) ?></textarea>
            </label>
            <label>Tech stack
                <input type="text" name="tech" value="<?= e($_POST['tech'] ?? $project['tech']) ?>">
            </label>
            <label>Project URL
                <input type="url" name="url" value="<?= e($_POST['url'] ?? $project['url']) ?>">
            </label>
            <label>Image (optional)
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
            </label>
            <?php if ($project['image']): ?>
                <p>Current image: <?= e($project['image']) ?></p>
            <?php endif; ?>
            <button type="submit">Save changes</button>
        </form>
    </main>
</body>
</html>
