<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$baseUrl = rtrim($config['base_url'] ?? '', '/');
$pageTitle = 'About';

try {
    $stmt = $pdo->prepare('SELECT title, intro, body, signature, profile_image, github_url, linkedin_url FROM about_profiles WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => 1]);
    $about = $stmt->fetch();
} catch (Throwable $e) {
    $about = null;
}

$bodyParagraphs = [];
if ($about && !empty(trim($about['body'] ?? ''))) {
    $chunks = preg_split('/\R{2,}/', trim($about['body']));
    foreach ($chunks as $chunk) {
        $trimmed = trim($chunk);
        if ($trimmed !== '') {
            $bodyParagraphs[] = $trimmed;
        }
    }
}

if ($about && !empty($about['profile_image'])) {
    $profileImageUrl = $baseUrl . '/uploads/profile/' . $about['profile_image'];
} else {
    $profileImageUrl = null;
}

$githubUrl = $about['github_url'] ?? '';
$linkedinUrl = $about['linkedin_url'] ?? '';

$hasSocialLinks = ($githubUrl !== '') || ($linkedinUrl !== '');

include __DIR__ . '/../includes/header.php';
?>
<section class="about">
    <?php if ($about): ?>
        <?php if ($profileImageUrl): ?>
            <div class="about__media">
                <img class="about__photo" src="<?= e($profileImageUrl) ?>" alt="Portrait of <?= e($about['title'] ?: 'Larisa') ?>" loading="lazy">
            </div>
        <?php endif; ?>
        <header class="about__header">
            <h1 class="about__title"><?= e($about['title'] ?: 'Meet Larisa') ?></h1>
            <?php if (!empty($about['intro'])): ?>
                <p class="about__intro"><?= nl2br(linkify_text($about['intro']), false) ?></p>
            <?php endif; ?>
        </header>
        <div class="about__body">
            <?php if ($bodyParagraphs): ?>
                <?php foreach ($bodyParagraphs as $paragraph): ?>
                    <p><?= nl2br(linkify_text($paragraph), false) ?></p>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Larisa is preparing a fresh biography. Check back soon!</p>
            <?php endif; ?>
        </div>
        <?php if (!empty($about['signature'])): ?>
            <footer class="about__footer">
                <?= e($about['signature']) ?>
            </footer>
        <?php endif; ?>
        <?php if ($hasSocialLinks): ?>
            <div class="about__social" aria-label="Find Larisa online">
                <?php if ($githubUrl): ?>
                    <a class="about__social-link" href="<?= e($githubUrl) ?>" target="_blank" rel="noopener">
                        <span class="about__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" role="presentation"><path d="M12 .5a12 12 0 0 0-3.79 23.41c.6.11.82-.26.82-.58v-2.02c-3.34.73-4.04-1.61-4.04-1.61-.55-1.42-1.34-1.8-1.34-1.8-1.09-.75.08-.74.08-.74 1.21.09 1.85 1.24 1.85 1.24 1.07 1.83 2.81 1.3 3.5.99.11-.78.42-1.3.76-1.6-2.67-.3-5.47-1.33-5.47-5.92 0-1.31.47-2.39 1.24-3.23-.12-.3-.54-1.52.12-3.17 0 0 1.01-.32 3.3 1.23a11.38 11.38 0 0 1 6 0c2.28-1.55 3.29-1.23 3.29-1.23.66 1.65.24 2.87.12 3.17.77.84 1.24 1.92 1.24 3.23 0 4.6-2.81 5.61-5.49 5.91.43.37.81 1.1.81 2.22v3.29c0 .32.22.7.82.58A12 12 0 0 0 12 .5"/></svg>
                        </span>
                        <span class="about__social-text">GitHub</span>
                    </a>
                <?php endif; ?>
                <?php if ($linkedinUrl): ?>
                    <a class="about__social-link" href="<?= e($linkedinUrl) ?>" target="_blank" rel="noopener">
                        <span class="about__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" role="presentation"><path d="M4.98 3.5A2.5 2.5 0 1 1 0 3.5a2.5 2.5 0 0 1 4.98 0zM.18 8.74h4.8V24h-4.8V8.74zM8.73 8.74h4.6v2.08h.07c.64-1.2 2.2-2.47 4.54-2.47 4.86 0 5.76 3.2 5.76 7.36V24h-4.8v-7.1c0-1.7-.03-3.88-2.37-3.88-2.37 0-2.74 1.85-2.74 3.76V24h-4.8V8.74z"/></svg>
                        </span>
                        <span class="about__social-text">LinkedIn</span>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <header class="about__header">
            <h1 class="about__title">Meet Larisa</h1>
        </header>
        <div class="about__body">
            <p>The story section isn&rsquo;t live yet. Log in to the admin area to add your biography.</p>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../includes/footer.php';
