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

function normalize_skill_label(string $token): string
{
    $normalize = [
        'MICROSOFT SQL SERVER' => 'SQL Server',
        'SSMS' => 'SSMS',
        'MYSQLI' => 'MySQL',
        'MYSQL' => 'MySQL',
        'MARIADB' => 'MariaDB',
        'PL/SQL TRIGGERS' => 'PL/SQL',
        'HTTP APIS' => 'HTTP APIs',
        'FETCH API' => 'Fetch API',
        'T-SQL' => 'T-SQL',
        'ER MODELING' => 'ER Modeling',
        'HTML' => 'HTML',
        'CSS' => 'CSS',
        'JAVASCRIPT' => 'JavaScript',
        'PHP' => 'PHP',
        'JSON' => 'JSON',
        'LDAP' => 'LDAP',
        'LDAPS' => 'LDAPS',
        'C#' => 'C#',
        '.NET MAUI' => '.NET MAUI',
        'XAML' => 'XAML',
        'SIGNALR' => 'SignalR',
        'ACTIVE DIRECTORY' => 'Active Directory',
        'BOOTSTRAP 5' => 'Bootstrap',
        'BOOTSTRAP' => 'Bootstrap',
        'ORACLE SQL' => 'Oracle SQL',
        'SQL DEVELOPER' => 'SQL Developer',
        'RELATIONAL MODELING' => 'Relational Modeling',
        'VIEWS' => 'Views',
        'WINDOWS SERVER' => 'Windows Server',
        'XAMPP' => 'XAMPP',
    ];

    return $normalize[$token] ?? ucwords(strtolower($token));
}

function build_skill_groups(array $projects): array
{
    $groupMap = [
        'Frontend' => ['HTML', 'CSS', 'JavaScript', 'Bootstrap', 'JSON', 'Fetch API', 'XAML'],
        'Backend' => ['PHP', 'C#', '.NET MAUI', 'SignalR', 'HTTP APIs', 'Sessions', 'Active Directory', 'LDAP', 'LDAPS'],
        'Data' => ['MySQL', 'MariaDB', 'SQL Server', 'T-SQL', 'PL/SQL', 'Oracle SQL', 'SQL Developer', 'Relational Modeling', 'Data Modeler', 'ER Modeling', 'Views', 'SSMS'],
        'Tools' => ['Git', 'GitHub', 'XAMPP', 'Windows Server'],
    ];

    $skillsSeen = [];

    foreach ($projects as $project) {
        $techRaw = trim((string) ($project['tech'] ?? ''));
        if ($techRaw === '') {
            continue;
        }

        $parts = preg_split('/\s*,\s*/', $techRaw) ?: [];
        foreach ($parts as $part) {
            $token = strtoupper(trim($part));
            if ($token === '') {
                continue;
            }

            $label = normalize_skill_label($token);
            $skillsSeen[strtolower($label)] = $label;
        }
    }

    $grouped = [
        'Frontend' => [],
        'Backend' => [],
        'Data' => [],
        'Tools' => [],
        'Soft' => ['Structured', 'Curious', 'Proactive', 'Teamplayer', 'Learning-minded'],
    ];

    foreach ($skillsSeen as $label) {
        $placed = false;
        foreach ($groupMap as $group => $items) {
            if (in_array($label, $items, true)) {
                $grouped[$group][] = $label;
                $placed = true;
                break;
            }
        }

        if (!$placed) {
            $grouped['Tools'][] = $label;
        }
    }

    foreach ($grouped as $group => $items) {
        $unique = array_values(array_unique($items));
        sort($unique, SORT_NATURAL | SORT_FLAG_CASE);
        $grouped[$group] = $unique;
    }

    return $grouped;
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
$skillsByGroup = build_skill_groups($projects);

$aboutStmt = $pdo->prepare('SELECT title, intro, body, signature, profile_image, github_url, linkedin_url FROM about_profiles WHERE id = :id LIMIT 1');
$aboutStmt->execute(['id' => 1]);
$about = $aboutStmt->fetch() ?: null;

// index.html
$indexBody = '<section class="home-hero">\n'
    . '    <p class="home-hero__kicker">Full-Stack Developer Portfolio</p>\n'
    . '    <h1 class="home-hero__title">I design and build full-stack web projects with practical UX, strong backend structure, and production-minded workflows.</h1>\n'
    . '    <p class="home-hero__text">Data Technician student specialized in Programming at Syddansk Erhvervsskole, currently building across frontend, backend, SQL systems, and application architecture.</p>\n'
    . '    <div class="home-hero__actions">\n'
    . '        <a class="project-card__button" href="./contact.html">Contact me</a>\n'
    . '        <a class="project-card__button project-card__button--ghost" href="./about.html">Read my profile</a>\n'
    . '    </div>\n'
    . '</section>\n';

$indexBody .= '<section class="skills-board">\n'
    . '    <h2 class="skills-board__title">Skills</h2>\n'
    . '    <div class="skills-board__grid">\n';

foreach ($skillsByGroup as $groupName => $skills) {
    if ($skills === []) {
        continue;
    }

    $indexBody .= '        <article class="skills-column">\n'
        . '            <h3 class="skills-column__title">' . e($groupName) . '</h3>\n'
        . '            <div class="skills-column__chips">\n';

    foreach ($skills as $skill) {
        $indexBody .= '                <span class="skill-chip">' . e($skill) . '</span>\n';
    }

    $indexBody .= '            </div>\n'
        . '        </article>\n';
}

$indexBody .= '    </div>\n'
    . '</section>\n';

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
        . '        <h1 class="about__title">Larisa Elena Bucos</h1>\n'
        . '        <p class="about__intro">Aspiring Full-Stack Developer building practical web applications with strong frontend presentation, backend structure, and database foundations.</p>\n'
        . '    </header>\n';

    $aboutBody .= '    <div class="about__content">\n'
        . '        <div class="about__narrative">\n'
        . '            <p>I am a Data Technician student specialized in Programming at Syddansk Erhvervsskole, currently focused on building complete solutions from interface to database.</p>\n'
        . '            <p>My project work spans PHP, JavaScript, SQL, C#, and .NET MAUI, and I enjoy debugging, improving architecture, and turning ideas into maintainable products.</p>\n'
        . '            <h2 class="about__section-title">What I Bring</h2>\n'
        . '            <ul class="about__list">\n'
        . '                <li>Full-stack mindset across UI, backend logic, and relational data modeling</li>\n'
        . '                <li>Hands-on project delivery with authentication, APIs, and SQL workflows</li>\n'
        . '                <li>Learning-oriented approach with focus on quality, structure, and team contribution</li>\n'
        . '            </ul>\n'
        . '        </div>\n'
        . '        <dl class="about__facts">\n'
        . '            <div class="about__fact">\n'
        . '                <dt>Education</dt>\n'
        . '                <dd>Data Technician with specialization in Programming, Syddansk Erhvervsskole (expected graduation: September 2028).</dd>\n'
        . '            </div>\n'
        . '            <div class="about__fact">\n'
        . '                <dt>Looking For</dt>\n'
        . '                <dd>Student or junior full-stack opportunities where I can contribute to web product development, backend services, and data-driven features while growing in CI/CD and cloud practices.</dd>\n'
        . '            </div>\n'
        . '            <div class="about__fact">\n'
        . '                <dt>Languages</dt>\n'
        . '                <dd>Romanian (native), English (fluent), Danish (improving in written and spoken communication).</dd>\n'
        . '            </div>\n'
        . '        </dl>\n'
        . '    </div>\n';

    $aboutBody .= '    <footer class="about__footer">Open to student and junior opportunities in software development, cloud, and data-focused roles.</footer>\n';

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
    . '        <h1 class="about__title">Larisa Elena Bucos</h1>\n'
    . '        <p class="about__intro">Aspiring Full-Stack Developer</p>\n'
        . '    </header>\n'
        . '    <div class="about__body">\n'
    . '        <p>Recruiter-ready profile content is being prepared.</p>\n'
        . '    </div>\n';
}

$aboutBody .= '</section>\n';
write_html($docsDir . DIRECTORY_SEPARATOR . 'about.html', shell_page('About', $aboutBody, 0, 'about'));

// contact.html (static)
$contactBody = '<section class="contact">\n'
    . '    <h1>Contact</h1>\n'
    . '    <form action="https://formspree.io/f/YOUR_FORM_ID" method="POST" class="contact__form">\n'
    . '        <input type="hidden" name="_subject" value="New portfolio contact message">\n'
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
