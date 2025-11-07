<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$baseUrl = rtrim($config['base_url'] ?? '', '/');
$pageTitle = 'Projects';

try {
    $stmt = $pdo->query('SELECT id, title, short_description, slug, image, tech FROM projects ORDER BY created_at DESC');
    $projects = $stmt->fetchAll();
} catch (Throwable $e) {
    $projects = [];
    $loadError = 'Unable to load projects right now.';
}

include __DIR__ . '/../includes/header.php';
?>
<section class="projects">
    <h1 class="projects__title">Latest Projects</h1>
    <?php if (!empty($loadError)): ?>
        <p class="projects__error"><?= e($loadError) ?></p>
    <?php endif; ?>

    <?php if (empty($projects)): ?>
        <p class="projects__empty">No projects found yet. Use the admin area to add your first project.</p>
    <?php else: ?>
        <div class="projects__grid">
            <?php foreach ($projects as $project):
                $imagePath = $project['image']
                    ? $baseUrl . '/uploads/' . e($project['image'])
                    : $baseUrl . '/assets/images/placeholder.svg';
            ?>
                <article class="project-card">
                    <a class="project-card__link" href="<?= $baseUrl ?>/project.php?slug=<?= e($project['slug']) ?>">
                        <img class="project-card__image" src="<?= $imagePath ?>" alt="<?= e($project['title']) ?>">
                        <h2 class="project-card__title"><?= e($project['title']) ?></h2>
                    </a>
                    <?php if (!empty($project['tech'])): ?>
                        <p class="project-card__tech">Tech: <?= e($project['tech']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($project['short_description'])): ?>
                        <p class="project-card__summary"><?= e($project['short_description']) ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../includes/footer.php';
