<?php

declare(strict_types=1);

$configPath = __DIR__ . '/../config/config.php';
if (!file_exists($configPath)) {
    fwrite(STDERR, "Missing config/config.php\n");
    exit(1);
}

$config = include $configPath;

$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    $config['db']['host'],
    $config['db']['dbname'],
    $config['db']['charset']
);

$pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$docsDir = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'docs';
if (!is_dir($docsDir)) {
    mkdir($docsDir, 0755, true);
}

$projectsDir = $docsDir . DIRECTORY_SEPARATOR . 'projects';
if (!is_dir($projectsDir)) {
    mkdir($projectsDir, 0755, true);
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function linkify_text(string $value): string
{
    $escaped = e($value);

    $escaped = preg_replace_callback('/\b(GitHub|LinkedIn)\s*\((https?:\/\/[^\s<)]+)\)/i', static function (array $m): string {
        return '<a href="' . e($m[2]) . '" target="_blank" rel="noopener">' . e($m[1]) . '</a>';
    }, $escaped) ?? $escaped;

    return preg_replace_callback('/(?<!href=")(https?:\/\/[^\s<]+)/i', static function (array $m): string {
        $url = $m[1];
        $label = preg_replace('/^https?:\/\/(www\.)?/i', '', $url) ?? $url;

        return '<a href="' . e($url) . '" target="_blank" rel="noopener">' . e($label) . '</a>';
    }, $escaped) ?? $escaped;
}

function is_direct_video_url(?string $url): bool
{
    $url = trim((string) $url);
    if ($url === '') {
        return false;
    }

    if (preg_match('~\.(mp4|webm|ogg)(?:\?.*)?$~i', $url)) {
        return true;
    }

    return (bool) preg_match('~^https?://github\.com/user-attachments/assets/[A-Za-z0-9-]+$~i', $url);
}

function normalize_media_url(string $url, int $depth): string
{
    $url = trim($url);
    $prefix = str_repeat('../', $depth);

    $localPrefixes = [
        'http://localhost:8080/uploads/',
        'http://localhost/uploads/',
        '/uploads/',
    ];

    foreach ($localPrefixes as $start) {
        if (stripos($url, $start) === 0) {
            $file = ltrim(substr($url, strlen($start)), '/');
            return $prefix . 'uploads/' . $file;
        }
    }

    return $url;
}

function render_video_markup(?string $url, int $depth): string
{
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }

    if (preg_match('~(?:youtube\.com/(?:watch\?v=|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~i', $url, $m)) {
        $embed = 'https://www.youtube.com/embed/' . $m[1];
        return '<div class="project-detail__video"><iframe src="' . e($embed) . '" title="Project video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy"></iframe></div>';
    }

    if (preg_match('~vimeo\.com/(?:video/)?([0-9]+)~i', $url, $m)) {
        $embed = 'https://player.vimeo.com/video/' . $m[1];
        return '<div class="project-detail__video"><iframe src="' . e($embed) . '" title="Project video" allow="autoplay; fullscreen" allowfullscreen loading="lazy"></iframe></div>';
    }

    if (is_direct_video_url($url)) {
        $src = normalize_media_url($url, $depth);
        return '<div class="project-detail__video"><video controls preload="metadata"><source src="' . e($src) . '"></video></div>';
    }

    return '<p class="project-detail__video-link"><a href="' . e($url) . '" target="_blank" rel="noopener">Watch project demo</a></p>';
}

function shell_page(string $title, string $body, int $depth, string $active): string
{
    $prefix = str_repeat('../', $depth);
    $css = $prefix . 'assets/css/style.css';
    $js = $prefix . 'assets/js/script.js';
    $home = $prefix . 'index.html';
    $about = $prefix . 'about.html';
    $contact = $prefix . 'contact.html';

    $year = date('Y');

    $projectsClass = $active === 'projects' ? ' class="is-active"' : '';
    $aboutClass = $active === 'about' ? ' class="is-active"' : '';
    $contactClass = $active === 'contact' ? ' class="is-active"' : '';

    return '<!DOCTYPE html>\n'
        . '<html lang="en">\n<head>\n'
        . '    <meta charset="utf-8">\n'
        . '    <meta name="viewport" content="width=device-width, initial-scale=1">\n'
        . '    <title>' . e($title) . ' | Larisa Portfolio</title>\n'
        . '    <link rel="stylesheet" href="' . e($css) . '">\n'
        . '</head>\n<body>\n'
        . '<header class="site-header">\n'
        . '    <nav class="nav">\n'
        . '        <a class="nav__brand" href="' . e($home) . '">Larisa Portfolio</a>\n'
        . '        <ul class="nav__links">\n'
        . '            <li><a href="' . e($home) . '"' . $projectsClass . '>Projects</a></li>\n'
        . '            <li><a href="' . e($about) . '"' . $aboutClass . '>About</a></li>\n'
        . '            <li><a href="' . e($contact) . '"' . $contactClass . '>Contact</a></li>\n'
        . '        </ul>\n'
        . '    </nav>\n'
        . '</header>\n'
        . '<main class="site-main">\n'
        . $body
        . '</main>\n'
        . '<footer class="site-footer">\n'
        . '    <p>&copy; ' . $year . ' Larisa Portfolio. All rights reserved.</p>\n'
        . '</footer>\n'
        . '<script src="' . e($js) . '"></script>\n'
        . '</body>\n</html>\n';
}

function write_html(string $path, string $content): void
{
    // Content builders use "\\n" markers for readability; convert before writing.
    file_put_contents($path, str_replace('\\n', PHP_EOL, $content));
}

$projects = $pdo->query('SELECT id, title, slug, short_description, description, image, demo_video_url, url, tech FROM projects ORDER BY created_at DESC')->fetchAll();
$featuredProject = $projects[0] ?? null;

$aboutStmt = $pdo->prepare('SELECT title, intro, body, signature, profile_image, github_url, linkedin_url FROM about_profiles WHERE id = :id LIMIT 1');
$aboutStmt->execute(['id' => 1]);
$about = $aboutStmt->fetch() ?: null;

// index.html
$indexBody = '<section class="home-hero">\n'
    . '    <p class="home-hero__kicker">Frontend Developer Portfolio</p>\n'
    . '    <h1 class="home-hero__title">I build practical web projects with strong UI, clean structure, and real-world workflows.</h1>\n'
    . '    <p class="home-hero__text">This public site is the static version of my portfolio. It highlights selected projects with visuals, technology stacks, and implementation details.</p>\n'
    . '</section>\n';

if ($featuredProject) {
    $fSlug = $featuredProject['slug'];
    $fTitle = $featuredProject['title'];
    $fTech = trim((string) ($featuredProject['tech'] ?? ''));
    $fShort = trim((string) ($featuredProject['short_description'] ?? ''));
    $fVideo = trim((string) ($featuredProject['demo_video_url'] ?? ''));
    $fRepo = trim((string) ($featuredProject['url'] ?? ''));
    $fImg = !empty($featuredProject['image'])
        ? './uploads/' . rawurlencode($featuredProject['image'])
        : './assets/images/placeholder.svg';

    if (is_direct_video_url($fVideo)) {
        $fSrc = normalize_media_url($fVideo, 0);
        $fPoster = !empty($featuredProject['image']) ? ' poster="' . e($fImg) . '"' : '';
        $featuredMedia = '<video class="featured-project__media" autoplay muted loop playsinline preload="metadata"' . $fPoster . ' aria-label="' . e($fTitle) . ' preview"><source src="' . e($fSrc) . '"></video>';
    } else {
        $featuredMedia = '<img class="featured-project__media" src="' . e($fImg) . '" alt="' . e($fTitle) . '">';
    }

    $indexBody .= '<section class="featured-project">\n'
        . '    <p class="featured-project__label">Featured Project</p>\n'
        . '    <div class="featured-project__panel">\n'
        . '        <a class="featured-project__link" href="./projects/' . rawurlencode($fSlug) . '/">' . $featuredMedia . '</a>\n'
        . '        <div class="featured-project__content">\n'
        . '            <h2><a href="./projects/' . rawurlencode($fSlug) . '/">' . e($fTitle) . '</a></h2>\n';

    if ($fTech !== '') {
        $indexBody .= '            <p class="project-card__tech">Tech: ' . e($fTech) . '</p>\n';
    }

    if ($fShort !== '') {
        $indexBody .= '            <p class="project-card__summary">' . e($fShort) . '</p>\n';
    }

    $indexBody .= '            <div class="project-card__actions">\n'
        . '                <a class="project-card__button" href="./projects/' . rawurlencode($fSlug) . '/">View details</a>\n';

    if ($fRepo !== '') {
        $indexBody .= '                <a class="project-card__button project-card__button--ghost" href="' . e($fRepo) . '" target="_blank" rel="noopener">GitHub</a>\n';
    }

    $indexBody .= '            </div>\n'
        . '        </div>\n'
        . '    </div>\n'
        . '</section>\n';
}

$indexBody .= '<section class="projects">\n'
    . '    <h2 class="projects__title">Latest Projects</h2>\n'
    . '    <div class="projects__grid">\n';

foreach ($projects as $project) {
    $slug = $project['slug'];
    $title = $project['title'];
    $short = trim((string) ($project['short_description'] ?? ''));
    $tech = trim((string) ($project['tech'] ?? ''));
    $videoUrl = trim((string) ($project['demo_video_url'] ?? ''));
    $repoUrl = trim((string) ($project['url'] ?? ''));

    $img = !empty($project['image'])
        ? './uploads/' . rawurlencode($project['image'])
        : './assets/images/placeholder.svg';

    $media = '';
    if (is_direct_video_url($videoUrl)) {
        $src = normalize_media_url($videoUrl, 0);
        $posterAttr = !empty($project['image']) ? ' poster="' . e($img) . '"' : '';
        $media = '<video class="project-card__image" autoplay muted loop playsinline preload="metadata"' . $posterAttr . ' aria-label="' . e($title) . ' demo preview">'
            . '<source src="' . e($src) . '"></video>';
    } else {
        $media = '<img class="project-card__image" src="' . e($img) . '" alt="' . e($title) . '">';
    }

    $indexBody .= '        <article class="project-card">\n'
        . '            <a class="project-card__link" href="./projects/' . rawurlencode($slug) . '/">\n'
        . '                ' . $media . '\n'
        . '                <h2 class="project-card__title">' . e($title) . '</h2>\n'
        . '            </a>\n';

    if ($tech !== '') {
        $indexBody .= '            <p class="project-card__tech">Tech: ' . e($tech) . '</p>\n';
    }

    if ($short !== '') {
        $indexBody .= '            <p class="project-card__summary">' . e($short) . '</p>\n';
    }

    $indexBody .= '            <div class="project-card__actions">\n'
        . '                <a class="project-card__button" href="./projects/' . rawurlencode($slug) . '/">View details</a>\n';

    if ($repoUrl !== '') {
        $indexBody .= '                <a class="project-card__button project-card__button--ghost" href="' . e($repoUrl) . '" target="_blank" rel="noopener">GitHub</a>\n';
    }

    $indexBody .= '            </div>\n';

    $indexBody .= '        </article>\n';
}

$indexBody .= '    </div>\n</section>\n';
write_html($docsDir . DIRECTORY_SEPARATOR . 'index.html', shell_page('Projects', $indexBody, 0, 'projects'));

// about.html
$aboutBody = '<section class="about">\n';

if ($about) {
    if (!empty($about['profile_image'])) {
        $profileImage = './uploads/profile/' . rawurlencode($about['profile_image']);
        $aboutBody .= '    <div class="about__media">\n'
            . '        <img class="about__photo" src="' . e($profileImage) . '" alt="Portrait of ' . e($about['title'] ?: 'Larisa') . '" loading="lazy">\n'
            . '    </div>\n';
    }

    $aboutBody .= '    <header class="about__header">\n'
        . '        <h1 class="about__title">' . e($about['title'] ?: 'Meet Larisa') . '</h1>\n';

    if (!empty(trim((string) $about['intro']))) {
        $aboutBody .= '        <p class="about__intro">' . nl2br(linkify_text((string) $about['intro']), false) . '</p>\n';
    }

    $aboutBody .= '    </header>\n';

    $aboutBody .= '    <div class="about__body">\n';
    $paragraphs = preg_split('/\R{2,}/', trim((string) ($about['body'] ?? ''))) ?: [];
    $rendered = 0;
    foreach ($paragraphs as $p) {
        $p = trim($p);
        if ($p === '') {
            continue;
        }
        $rendered++;
        $aboutBody .= '        <p>' . nl2br(linkify_text($p), false) . '</p>\n';
    }
    if ($rendered === 0) {
        $aboutBody .= '        <p>Larisa is preparing a fresh biography. Check back soon!</p>\n';
    }
    $aboutBody .= '    </div>\n';

    if (!empty(trim((string) $about['signature']))) {
        $aboutBody .= '    <footer class="about__footer">' . e((string) $about['signature']) . '</footer>\n';
    }

    $github = trim((string) ($about['github_url'] ?? ''));
    $linkedin = trim((string) ($about['linkedin_url'] ?? ''));
    if ($github !== '' || $linkedin !== '') {
        $aboutBody .= '    <div class="about__social" aria-label="Find Larisa online">\n';
        if ($github !== '') {
            $aboutBody .= '        <a class="about__social-link" href="' . e($github) . '" target="_blank" rel="noopener">GitHub</a>\n';
        }
        if ($linkedin !== '') {
            $aboutBody .= '        <a class="about__social-link" href="' . e($linkedin) . '" target="_blank" rel="noopener">LinkedIn</a>\n';
        }
        $aboutBody .= '    </div>\n';
    }
} else {
    $aboutBody .= '    <header class="about__header">\n'
        . '        <h1 class="about__title">Meet Larisa</h1>\n'
        . '    </header>\n'
        . '    <div class="about__body">\n'
        . '        <p>The story section is not available yet.</p>\n'
        . '    </div>\n';
}

$aboutBody .= '</section>\n';
write_html($docsDir . DIRECTORY_SEPARATOR . 'about.html', shell_page('About', $aboutBody, 0, 'about'));

// contact.html (static)
$contactBody = '<section class="contact">\n'
    . '    <h1>Contact</h1>\n'
    . '    <p class="contact__success">This static site does not save messages in a database. Use email below.</p>\n'
    . '    <form action="mailto:63336@edu.sde.dk" method="post" enctype="text/plain" class="contact__form">\n'
    . '        <label>Name<input type="text" name="name" required></label>\n'
    . '        <label>Email<input type="email" name="email" required></label>\n'
    . '        <label>Message<textarea name="message" rows="5" required></textarea></label>\n'
    . '        <button type="submit">Send message</button>\n'
    . '    </form>\n'
    . '</section>\n';
write_html($docsDir . DIRECTORY_SEPARATOR . 'contact.html', shell_page('Contact', $contactBody, 0, 'contact'));

// Per-project pages
foreach ($projects as $project) {
    $slug = $project['slug'];
    $title = $project['title'];
    $tech = trim((string) ($project['tech'] ?? ''));
    $description = (string) ($project['description'] ?? '');
    $url = trim((string) ($project['url'] ?? ''));

    $projectDir = $projectsDir . DIRECTORY_SEPARATOR . $slug;
    if (!is_dir($projectDir)) {
        mkdir($projectDir, 0755, true);
    }

    $imagePath = !empty($project['image'])
        ? '../../uploads/' . rawurlencode($project['image'])
        : '../../assets/images/placeholder.svg';

    $videoMarkup = render_video_markup($project['demo_video_url'] ?? '', 2);

    $body = '<article class="project-detail">\n'
        . '    <header class="project-detail__header">\n'
        . '        <h1>' . e($title) . '</h1>\n';

    if ($tech !== '') {
        $body .= '        <p class="project-detail__tech">Tech stack: ' . e($tech) . '</p>\n';
    }

    $body .= '    </header>\n';

    if ($videoMarkup !== '') {
        $body .= '    ' . $videoMarkup . '\n';
    }

    if (!empty($project['image'])) {
        $body .= '    <a class="project-detail__image-link" href="' . e($imagePath) . '" target="_blank" rel="noopener">\n'
            . '        <img class="project-detail__image" src="' . e($imagePath) . '" alt="' . e($title) . '">\n'
            . '    </a>\n';
    }

    if ($url !== '') {
        $body .= '    <p class="project-detail__link"><a href="' . e($url) . '" target="_blank" rel="noopener">View project</a></p>\n';
    }

    $body .= '    <div class="project-detail__content">' . nl2br(e($description)) . '</div>\n'
        . '    <p><a href="../../index.html">&larr; Back to projects</a></p>\n'
        . '</article>\n';

    write_html($projectDir . DIRECTORY_SEPARATOR . 'index.html', shell_page($title, $body, 2, 'projects'));
}

echo "Static export complete: docs/index.html, docs/about.html, docs/contact.html and project pages generated.\n";
