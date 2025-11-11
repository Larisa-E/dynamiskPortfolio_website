<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$baseUrl = rtrim($config['base_url'] ?? '', '/');
$errors = [];
$success = null;

$pdo->exec('CREATE TABLE IF NOT EXISTS about_profiles (
    id TINYINT UNSIGNED PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    intro TEXT NULL,
    body LONGTEXT NOT NULL,
    signature VARCHAR(255) NULL,
    profile_image VARCHAR(255) NULL,
    github_url VARCHAR(255) NULL,
    linkedin_url VARCHAR(255) NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

$columnCheck = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'about_profiles'");
$columnCheck->execute();
$existingColumns = $columnCheck->fetchAll(PDO::FETCH_COLUMN);

if (!in_array('profile_image', $existingColumns, true)) {
    $pdo->exec('ALTER TABLE about_profiles ADD COLUMN profile_image VARCHAR(255) NULL AFTER signature');
}

if (!in_array('github_url', $existingColumns, true)) {
    $pdo->exec('ALTER TABLE about_profiles ADD COLUMN github_url VARCHAR(255) NULL AFTER profile_image');
}

if (!in_array('linkedin_url', $existingColumns, true)) {
    $pdo->exec('ALTER TABLE about_profiles ADD COLUMN linkedin_url VARCHAR(255) NULL AFTER github_url');
}

$stmt = $pdo->prepare('SELECT title, intro, body, signature, profile_image, github_url, linkedin_url FROM about_profiles WHERE id = :id');
$stmt->execute(['id' => 1]);
$about = $stmt->fetch() ?: [
    'title' => 'Meet Larisa',
    'intro' => '',
    'body' => '',
    'signature' => '',
    'profile_image' => '',
    'github_url' => '',
    'linkedin_url' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $intro = trim($_POST['intro'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $signature = trim($_POST['signature'] ?? '');
    $profileImage = $about['profile_image'] ?? null;
    $githubUrl = trim($_POST['github_url'] ?? '');
    $linkedinUrl = trim($_POST['linkedin_url'] ?? '');

    if ($title === '') {
        $errors[] = 'A title for the page is required.';
    }

    if ($body === '') {
        $errors[] = 'Please add the main story for your about page.';
    }

    if ($githubUrl !== '' && !filter_var($githubUrl, FILTER_VALIDATE_URL)) {
        $errors[] = 'GitHub URL must be a valid link.';
    }

    if ($linkedinUrl !== '' && !filter_var($linkedinUrl, FILTER_VALIDATE_URL)) {
        $errors[] = 'LinkedIn URL must be a valid link.';
    }

    if (!$errors) {
        if (!empty($_FILES['profile_image']['name'])) {
            $info = @getimagesize($_FILES['profile_image']['tmp_name']);
            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
            ];

            if (!$info || !isset($allowed[$info['mime']])) {
                $errors[] = 'Upload a valid JPG, PNG, WEBP, or GIF image.';
            } else {
                $ext = $allowed[$info['mime']];
                $fileName = uniqid('profile_', true) . '.' . $ext;
                $uploadDir = __DIR__ . '/../public/uploads/profile/';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                    $errors[] = 'Profile image directory is missing and could not be created.';
                }
                $destination = $uploadDir . $fileName;
                if (!$errors && !move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)) {
                    $errors[] = 'Profile image upload failed.';
                } else {
                    if ($profileImage) {
                        $oldPath = __DIR__ . '/../public/uploads/profile/' . $profileImage;
                        if (is_file($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                    $profileImage = $fileName;
                }
            }
        } elseif (!empty($_POST['remove_image'])) {
            if ($profileImage) {
                $oldPath = __DIR__ . '/../public/uploads/profile/' . $profileImage;
                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }
            $profileImage = null;
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO about_profiles (id, title, intro, body, signature, profile_image, github_url, linkedin_url) VALUES (:id, :title, :intro, :body, :signature, :profile_image, :github_url, :linkedin_url)
            ON DUPLICATE KEY UPDATE title = VALUES(title), intro = VALUES(intro), body = VALUES(body), signature = VALUES(signature), profile_image = VALUES(profile_image), github_url = VALUES(github_url), linkedin_url = VALUES(linkedin_url)');
        $stmt->execute([
            'id' => 1,
            'title' => $title,
            'intro' => $intro,
            'body' => $body,
            'signature' => $signature,
            'profile_image' => $profileImage,
            'github_url' => $githubUrl,
            'linkedin_url' => $linkedinUrl,
        ]);

        $success = 'About page updated successfully.';
        $about = [
            'title' => $title,
            'intro' => $intro,
            'body' => $body,
            'signature' => $signature,
            'profile_image' => $profileImage,
            'github_url' => $githubUrl,
            'linkedin_url' => $linkedinUrl,
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit About Page</title>
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
        <h1>Edit About Page</h1>
        <p>Write your story just like you would in a cover letter. Separate paragraphs with a blank line.</p>
        <?php if ($errors): ?>
            <ul class="admin-form__errors">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="admin-form__success"><?= e($success) ?></p>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <label>Page title
                <input type="text" name="title" value="<?= e($_POST['title'] ?? $about['title']) ?>" required>
            </label>
            <label>Intro (optional)
                <textarea name="intro" rows="3" placeholder="A quick opening line that introduces you."><?= e($_POST['intro'] ?? $about['intro']) ?></textarea>
            </label>
            <label>Main story
                <textarea name="body" rows="12" placeholder="Share your journey, highlights, fun facts, and goals."><?= e($_POST['body'] ?? $about['body']) ?></textarea>
            </label>
            <label>Signature / Closing line (optional)
                <input type="text" name="signature" value="<?= e($_POST['signature'] ?? $about['signature']) ?>" placeholder="Kind regards, Larisa Elena Bucos">
            </label>
            <label>GitHub URL (optional)
                <input type="url" name="github_url" value="<?= e($_POST['github_url'] ?? $about['github_url']) ?>" placeholder="https://github.com/Larisa-E">
            </label>
            <label>LinkedIn URL (optional)
                <input type="url" name="linkedin_url" value="<?= e($_POST['linkedin_url'] ?? $about['linkedin_url']) ?>" placeholder="https://www.linkedin.com/in/larisa-elena-bucos/">
            </label>
            <fieldset class="admin-form__fieldset">
                <legend>Profile photo (optional)</legend>
                <?php if (!empty($about['profile_image'])): ?>
                    <div class="admin-form__preview">
                        <img src="../public/uploads/profile/<?= e($about['profile_image']) ?>" alt="Current profile photo" loading="lazy">
                    </div>
                <?php endif; ?>
                <label>Upload new image
                    <input type="file" name="profile_image" accept="image/jpeg,image/png,image/webp,image/gif">
                </label>
                <?php if (!empty($about['profile_image'])): ?>
                    <label class="admin-form__checkbox">
                        <input type="checkbox" name="remove_image" value="1">
                        Remove current photo
                    </label>
                <?php endif; ?>
            </fieldset>
            <button type="submit">Save About Page</button>
        </form>
    </main>
</body>
</html>
