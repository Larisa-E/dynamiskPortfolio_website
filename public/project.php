<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$baseUrl = rtrim($config['base_url'] ?? '', '/');
$slug = $_GET['slug'] ?? '';

if ($slug === '') {
    header('Location: ' . $baseUrl . '/');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM projects WHERE slug = :slug');
$stmt->execute(['slug' => $slug]);
$project = $stmt->fetch();

if (!$project) {
    http_response_code(404);
    $pageTitle = 'Project Not Found';
    include __DIR__ . '/../includes/header.php';
    echo '<section class="project"><p>Project not found.</p><p><a href="' . e($baseUrl) . '/">Return to projects</a></p></section>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$pageTitle = $project['title'];
include __DIR__ . '/../includes/header.php';

$imagePath = $project['image']
    ? $baseUrl . '/uploads/' . e($project['image'])
    : $baseUrl . '/assets/images/placeholder.svg';
?>
<article class="project-detail">
    <header class="project-detail__header">
        <h1><?= e($project['title']) ?></h1>
        <?php if (!empty($project['tech'])): ?>
            <p class="project-detail__tech">Tech stack: <?= e($project['tech']) ?></p>
        <?php endif; ?>
    </header>

    <img class="project-detail__image" src="<?= $imagePath ?>" alt="<?= e($project['title']) ?>">

    <?php if (!empty($project['url'])): ?>
        <p class="project-detail__link"><a href="<?= e($project['url']) ?>" target="_blank" rel="noopener">View project</a></p>
    <?php endif; ?>

    <div class="project-detail__content">
        <?= nl2br(e($project['description'])) ?>
    </div>

    <p><a href="<?= $baseUrl ?>/">← Back to projects</a></p>
</article>
<?php include __DIR__ . '/../includes/footer.php';
