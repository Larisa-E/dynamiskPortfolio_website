<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$baseUrl = rtrim($config['base_url'] ?? '', '/');
$pageTitle = 'Contact';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '') {
        $errors[] = 'Name is required.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }

    if ($message === '') {
        $errors[] = 'Message cannot be empty.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO messages (name, email, message) VALUES (:name, :email, :message)');
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'message' => $message,
        ]);

        $success = 'Thanks for reaching out! I will get back to you soon.';
    }
}

include __DIR__ . '/../includes/header.php';
?>
<section class="contact">
    <h1>Contact</h1>

    <?php if ($errors): ?>
        <ul class="contact__errors">
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="contact__success"><?= e($success) ?></p>
    <?php endif; ?>

    <form action="" method="post" class="contact__form">
        <label>
            Name
            <input type="text" name="name" value="<?= e($_POST['name'] ?? '') ?>" required>
        </label>
        <label>
            Email
            <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
        </label>
        <label>
            Message
            <textarea name="message" rows="5" required><?= e($_POST['message'] ?? '') ?></textarea>
        </label>
        <button type="submit">Send message</button>
    </form>
</section>
<?php include __DIR__ . '/../includes/footer.php';
