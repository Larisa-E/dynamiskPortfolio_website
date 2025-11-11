<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$baseUrl = rtrim($config['base_url'] ?? '', '/');
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int) $_POST['delete_id'];

    if ($deleteId > 0) {
        $stmt = $pdo->prepare('DELETE FROM messages WHERE id = :id');
        $stmt->execute(['id' => $deleteId]);

        if ($stmt->rowCount() > 0) {
            $success = 'Message deleted.';
        } else {
            $success = 'Message already removed or not found.';
        }
    }
}

$stmt = $pdo->query('SELECT id, name, email, message, created_at FROM messages ORDER BY created_at DESC');
$messages = $stmt->fetchAll();
$totalMessages = count($messages);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Messages</title>
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
    <main class="admin-messages">
        <header class="admin-messages__header">
            <h1>Messages</h1>
            <span><?= $totalMessages ?> total</span>
        </header>

        <?php if ($success): ?>
            <p class="admin-form__success"><?= e($success) ?></p>
        <?php endif; ?>

        <?php if (!$messages): ?>
            <p class="admin-messages__empty">No messages yet. Once someone fills out the contact form, their note will appear here.</p>
        <?php else: ?>
            <div class="admin-messages__list">
                <?php foreach ($messages as $message): ?>
                    <article class="admin-messages__item">
                        <div class="admin-messages__meta">
                            <span><strong><?= e($message['name']) ?></strong></span>
                            <span><a href="mailto:<?= e($message['email']) ?>"><?= e($message['email']) ?></a></span>
                            <span><?= e(date('M j, Y H:i', strtotime($message['created_at']))) ?></span>
                        </div>
                        <div class="admin-messages__body">
                            <?= nl2br(e($message['message'])) ?>
                        </div>
                        <div class="admin-messages__actions">
                            <form method="post">
                                <input type="hidden" name="delete_id" value="<?= (int) $message['id'] ?>">
                                <button type="submit" onclick="return confirm('Delete this message?');">Delete</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
