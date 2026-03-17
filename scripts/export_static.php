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

function extract_first_sentence(string $text): string
{
    $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');
    if ($text === '') {
        return '';
    }

    $parts = preg_split('/(?<=[.!?])\s+/', $text) ?: [];
    $sentence = trim($parts[0] ?? $text);

    return $sentence === '' ? $text : $sentence;
}

function project_outcome_line(array $project): string
{
    $short = trim((string) ($project['short_description'] ?? ''));
    $description = trim((string) ($project['description'] ?? ''));
    $base = $short !== '' ? $short : $description;
    if ($base === '') {
        return '';
    }

    $sentence = extract_first_sentence($base);
    return $sentence;
}

function build_project_highlights(array $project): array
{
    $tech = trim((string) ($project['tech'] ?? ''));
    $description = trim((string) ($project['description'] ?? ''));

    $stack = $tech !== '' ? $tech : 'practical full-stack tooling';
    $challenge = 'Translated requirements into a clean, maintainable implementation with reliable user flows.';

    $techUpper = strtoupper($tech);
    if (strpos($techUpper, 'LDAP') !== false || strpos($techUpper, 'ACTIVE DIRECTORY') !== false) {
        $challenge = 'Handled authentication and identity constraints while keeping session handling secure and predictable.';
    } elseif (strpos($techUpper, 'SQL') !== false || strpos($techUpper, 'ORACLE') !== false || strpos($techUpper, 'MYSQL') !== false) {
        $challenge = 'Modeled relational data carefully to preserve integrity and support realistic reporting/query scenarios.';
    } elseif (strpos($techUpper, 'SIGNALR') !== false || strpos($techUpper, '.NET MAUI') !== false) {
        $challenge = 'Balanced real-time behavior and UI responsiveness to keep the experience stable across user actions.';
    }

    $learning = 'Strengthened architecture decisions, debugging discipline, and production-minded delivery from prototype to working result.';
    if ($description !== '') {
        $learning = 'Strengthened architecture decisions and debugging discipline while turning complex requirements into maintainable output.';
    }

    return [
        'Architecture: Built with ' . $stack . ' and structured for readability and future extension.',
        'Challenge: ' . $challenge,
        'Learning: ' . $learning,
    ];
}

function build_meta_description(string $fallback, string $body): string
{
    $description = trim($fallback);
    if ($description === '') {
        $description = extract_first_sentence(strip_tags($body));
    }

    if (strlen($description) > 160) {
        $description = rtrim(substr($description, 0, 157)) . '...';
    }

    return $description;
}

function shell_page(string $title, string $body, int $depth, string $active, string $description = '', string $canonicalPath = '', string $preloadImage = ''): string
{
    $prefix = str_repeat('../', $depth);
    $css = $prefix . 'assets/css/style.css';
    $js = $prefix . 'assets/js/script.js';
    $home = $prefix . 'index.html';
    $about = $prefix . 'about.html';
    $contact = $prefix . 'contact.html';
    $cv = $prefix . 'cv.html';
    $baseUrl = 'https://larisa-e.github.io/dynamiskPortfolio_website/';
    $normalizedPath = ltrim($canonicalPath, '/');
    if ($normalizedPath === '') {
        $normalizedPath = $depth > 0 ? $prefix . 'index.html' : 'index.html';
    }

    $canonicalUrl = $baseUrl . ltrim(str_replace('../', '', $normalizedPath), '/');
    $metaDescription = build_meta_description($description, $body);
    $defaultImage = $baseUrl . 'assets/images/placeholder.svg';
    $ogImage = $defaultImage;
    if ($preloadImage !== '') {
        $normalizedImage = str_replace(['../', './'], '', $preloadImage);
        $ogImage = $baseUrl . ltrim($normalizedImage, '/');
    }

    $year = date('Y');

    $projectsClass = $active === 'projects' ? ' class="is-active"' : '';
    $aboutClass = $active === 'about' ? ' class="is-active"' : '';
    $contactClass = $active === 'contact' ? ' class="is-active"' : '';
    $cvClass = $active === 'cv' ? ' class="nav__cta is-active"' : ' class="nav__cta"';

    $preloadTag = '';
    if ($preloadImage !== '') {
        $preloadTag = '    <link rel="preload" as="image" href="' . e($preloadImage) . '">\n';
    }

    return '<!DOCTYPE html>\n'
        . '<html lang="en">\n<head>\n'
        . '    <meta charset="utf-8">\n'
        . '    <meta name="viewport" content="width=device-width, initial-scale=1">\n'
        . '    <title>' . e($title) . ' | Larisa Portfolio</title>\n'
        . '    <meta name="description" content="' . e($metaDescription) . '">\n'
        . '    <link rel="canonical" href="' . e($canonicalUrl) . '">\n'
        . '    <meta property="og:type" content="website">\n'
        . '    <meta property="og:title" content="' . e($title) . ' | Larisa Portfolio">\n'
        . '    <meta property="og:description" content="' . e($metaDescription) . '">\n'
        . '    <meta property="og:url" content="' . e($canonicalUrl) . '">\n'
        . '    <meta property="og:image" content="' . e($ogImage) . '">\n'
        . '    <meta name="twitter:card" content="summary_large_image">\n'
        . $preloadTag
        . '    <link rel="stylesheet" href="' . e($css) . '">\n'
        . '</head>\n<body>\n'
        . '<header class="site-header">\n'
        . '    <nav class="nav">\n'
        . '        <a class="nav__brand" href="' . e($home) . '">Larisa Portfolio</a>\n'
        . '        <ul class="nav__links">\n'
        . '            <li><a href="' . e($home) . '"' . $projectsClass . '>Projects</a></li>\n'
        . '            <li><a href="' . e($about) . '"' . $aboutClass . '>About</a></li>\n'
        . '            <li><a href="' . e($contact) . '"' . $contactClass . '>Contact</a></li>\n'
        . '            <li><a href="' . e($cv) . '"' . $cvClass . '>CV</a></li>\n'
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
    . '    <p class="home-hero__text">Seeking student and junior full-stack opportunities with backend and data focus. Data Technician student specialized in Programming at Syddansk Erhvervsskole, building across frontend, backend, SQL systems, and application architecture.</p>\n'
    . '    <div class="home-hero__actions">\n'
    . '        <a class="project-card__button" href="./contact.html">Contact me</a>\n'
    . '        <a class="project-card__button project-card__button--ghost" href="./about.html">Read my profile</a>\n'
    . '        <a class="project-card__button project-card__button--ghost" href="./cv.html">Open CV</a>\n'
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

$featuredProjects = array_slice($projects, 0, 3);
if ($featuredProjects !== []) {
    $indexBody .= '<section class="projects-featured">\n'
        . '    <h2 class="projects-featured__title">Featured Projects</h2>\n'
        . '    <div class="projects-featured__grid">\n';

    foreach ($featuredProjects as $project) {
        $slug = $project['slug'];
        $title = $project['title'];
        $impact = project_outcome_line($project);

        $indexBody .= '        <article class="featured-project">\n'
            . '            <h3><a href="./projects/' . rawurlencode($slug) . '/">' . e($title) . '</a></h3>\n';

        if ($impact !== '') {
            $indexBody .= '            <p class="featured-project__impact">Outcome: ' . e($impact) . '</p>\n';
        }

        $indexBody .= '            <a class="project-card__button project-card__button--ghost" href="./projects/' . rawurlencode($slug) . '/">View details</a>\n'
            . '        </article>\n';
    }

    $indexBody .= '    </div>\n'
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
        $media = '<img class="project-card__image" src="' . e($img) . '" alt="Preview image for ' . e($title) . '" loading="lazy" decoding="async">';
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

    $impact = project_outcome_line($project);
    if ($impact !== '') {
        $indexBody .= '            <p class="project-card__impact">Outcome: ' . e($impact) . '</p>\n';
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
write_html($docsDir . DIRECTORY_SEPARATOR . 'index.html', shell_page('Projects', $indexBody, 0, 'projects', 'Full-stack portfolio with featured projects across web, backend, and database systems.', 'index.html'));

// about.html
$aboutBody = '<section class="about">\n';
$portfolioEmail = 'larisaeb0289@gmail.com';
$aboutPreloadImage = '';

if ($about) {
    if (!empty($about['profile_image'])) {
        $profileImage = './uploads/profile/' . rawurlencode($about['profile_image']);
        $aboutPreloadImage = $profileImage;
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
        . '            <p>My project work spans PHP, JavaScript, SQL, C#, and .NET MAUI, and I enjoy debugging, improving architecture, and turning ideas into maintainable products.</p>\n'
        . '            <h2 class="about__section-title">What I Bring</h2>\n'
        . '            <ul class="about__list">\n'
        . '                <li>Full-stack mindset across UI, backend logic, and relational data modeling</li>\n'
        . '                <li>Hands-on project delivery with authentication, APIs, and SQL workflows</li>\n'
        . '                <li>Learning-oriented approach with focus on quality, structure, and team contribution</li>\n'
        . '            </ul>\n'
        . '        </div>\n'
        . '        <aside class="about__sidebar">\n'
        . '            <p class="about__sidebar-text">I am a Data Technician student specialized in Programming at Syddansk Erhvervsskole, currently focused on building complete solutions from interface to database.</p>\n'
        . '            <dl class="about__sidebar-facts">\n'
        . '                <div class="about__fact">\n'
        . '                    <dt>Education</dt>\n'
        . '                    <dd>Data Technician with specialization in Programming, Syddansk Erhvervsskole (expected graduation: September 2028).</dd>\n'
        . '                </div>\n'
        . '                <div class="about__fact">\n'
        . '                    <dt>Languages</dt>\n'
        . '                    <dd>Romanian (native), English (fluent), Danish (improving in written and spoken communication).</dd>\n'
        . '                </div>\n'
        . '            </dl>\n'
        . '        </aside>\n'
        . '        <div class="about__focus">\n'
        . '            <h2 class="about__focus-title">Looking For</h2>\n'
        . '            <p>Student or junior full-stack opportunities where I can contribute to web product development, backend services, and data-driven features while growing in CI/CD and cloud practices.</p>\n'
        . '        </div>\n'
        . '    </div>\n';

    $aboutBody .= '    <footer class="about__footer">Open to student and junior opportunities in software development, cloud, and data-focused roles.</footer>\n';

    $github = trim((string) ($about['github_url'] ?? ''));
    $linkedin = trim((string) ($about['linkedin_url'] ?? ''));
    if ($github !== '' || $linkedin !== '' || $portfolioEmail !== '') {
        $aboutBody .= '    <div class="about__social" aria-label="Find Larisa online">\n';
        if ($portfolioEmail !== '') {
            $aboutBody .= '        <a class="about__social-link" href="mailto:' . e($portfolioEmail) . '">Email</a>\n';
            $aboutBody .= '        <a class="about__social-link" href="./cv.html">CV</a>\n';
        }
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
write_html($docsDir . DIRECTORY_SEPARATOR . 'about.html', shell_page('About', $aboutBody, 0, 'about', 'About Larisa Elena Bucos: aspiring full-stack developer focused on backend, SQL, and maintainable web solutions.', 'about.html', $aboutPreloadImage));

// contact.html (static)
$contactBody = '<section class="contact">\n'
    . '    <h1>Contact</h1>\n'
    . '    <p class="contact__reply-time">I usually reply within 24-48 hours.</p>\n'
    . '    <p id="sent" class="contact__sent">Thank you. Your message has been sent successfully.</p>\n'
    . '    <form action="https://formsubmit.co/' . e($portfolioEmail) . '" method="POST" class="contact__form">\n'
    . '        <input type="hidden" name="_subject" value="New portfolio contact message">\n'
    . '        <input type="hidden" name="_captcha" value="false">\n'
    . '        <input type="hidden" name="_template" value="table">\n'
    . '        <input type="text" name="_honey" class="visually-hidden" tabindex="-1" autocomplete="off">\n'
    . '        <input type="hidden" name="_next" id="contact-next" value="">\n'
    . '        <label>Name<input type="text" name="name" required></label>\n'
    . '        <label>Email<input type="email" name="email" required></label>\n'
    . '        <label>Message<textarea name="message" rows="5" required></textarea></label>\n'
    . '        <button type="submit">Send message</button>\n'
    . '    </form>\n'
    . '    <script>\n'
    . '        (function () {\n'
    . '            var nextInput = document.getElementById("contact-next");\n'
    . '            if (!nextInput) return;\n'
    . '            nextInput.value = window.location.origin + window.location.pathname + "#sent";\n'
    . '            if (window.location.hash === "#sent") {\n'
    . '                window.setTimeout(function () {\n'
    . '                    history.replaceState(null, "", window.location.pathname);\n'
    . '                }, 5000);\n'
    . '            }\n'
    . '        })();\n'
    . '    </script>\n'
    . '</section>\n';
write_html($docsDir . DIRECTORY_SEPARATOR . 'contact.html', shell_page('Contact', $contactBody, 0, 'contact', 'Contact Larisa for student and junior full-stack opportunities and collaboration.', 'contact.html'));

// cv.html
$cvPdfHref = './Larisa%20Elena%20Bucos_CV%20Resume.pdf';
$cvBody = '<section class="cv">\n'
    . '    <header class="cv__header">\n'
    . '        <h1>Curriculum Vitae</h1>\n'
    . '        <p>Larisa Elena Bucos - Aspiring Full-Stack Developer</p>\n'
    . '        <p class="cv__summary">Quick recruiter version: backend and data-focused full-stack profile with hands-on delivery across PHP, SQL, C#, and .NET MAUI.</p>\n'
    . '    </header>\n'
    . '    <div class="cv__actions">\n'
    . '        <a class="project-card__button" href="' . e($cvPdfHref) . '" target="_blank" rel="noopener" download>Download PDF CV</a>\n'
    . '        <a class="project-card__button project-card__button--ghost" href="' . e($cvPdfHref) . '" target="_blank" rel="noopener">Open PDF in browser</a>\n'
    . '    </div>\n'
    . '    <article class="cv__panel">\n'
    . '        <h2>Snapshot</h2>\n'
    . '        <ul class="cv__highlights">\n'
    . '            <li>Builds practical web products with strong backend and relational database structure.</li>\n'
    . '            <li>Experience across PHP, JavaScript, SQL Server, Oracle SQL, MySQL, C#, and .NET MAUI.</li>\n'
    . '            <li>Data Technician specialization in Programming, Syddansk Erhvervsskole (expected graduation: September 2028).</li>\n'
    . '        </ul>\n'
    . '        <p class="cv__links"><a href="mailto:' . e($portfolioEmail) . '">Email</a> | <a href="https://github.com/Larisa-E" target="_blank" rel="noopener">GitHub</a> | <a href="https://www.linkedin.com/" target="_blank" rel="noopener">LinkedIn</a></p>\n'
    . '    </article>\n'
    . '</section>\n';
write_html($docsDir . DIRECTORY_SEPARATOR . 'cv.html', shell_page('CV', $cvBody, 0, 'cv', 'CV and professional summary for Larisa Elena Bucos.', 'cv.html'));

// Per-project pages
foreach ($projects as $project) {
    $slug = $project['slug'];
    $title = $project['title'];
    $tech = trim((string) ($project['tech'] ?? ''));
    $description = (string) ($project['description'] ?? '');
    $url = trim((string) ($project['url'] ?? ''));
    $demoVideoUrl = trim((string) ($project['demo_video_url'] ?? ''));

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

    $body .= '        <div class="project-detail__actions">\n';
    if ($url !== '') {
        $body .= '            <a class="project-card__button" href="' . e($url) . '" target="_blank" rel="noopener">GitHub repository</a>\n';
    }
    if ($demoVideoUrl !== '') {
        $body .= '            <a class="project-card__button project-card__button--ghost" href="' . e($demoVideoUrl) . '" target="_blank" rel="noopener">Demo / walkthrough</a>\n';
    }
    $body .= '        </div>\n';

    $body .= '    </header>\n';

    $highlights = build_project_highlights($project);
    if ($highlights !== []) {
        $body .= '    <section class="project-detail__highlights">\n'
            . '        <h2>Highlights</h2>\n'
            . '        <ul>\n';
        foreach ($highlights as $highlight) {
            $body .= '            <li>' . e($highlight) . '</li>\n';
        }
        $body .= '        </ul>\n'
            . '    </section>\n';
    }

    if ($videoMarkup !== '') {
        $body .= '    ' . $videoMarkup . '\n';
    }

    if (!empty($project['image'])) {
        $body .= '    <a class="project-detail__image-link" href="' . e($imagePath) . '" target="_blank" rel="noopener">\n'
                . '        <img class="project-detail__image" src="' . e($imagePath) . '" alt="Preview image for ' . e($title) . '" loading="lazy" decoding="async">\n'
            . '    </a>\n';
    }

    if ($url !== '') {
        $body .= '    <p class="project-detail__link"><a href="' . e($url) . '" target="_blank" rel="noopener">View project</a></p>\n';
    }

    $body .= '    <div class="project-detail__content">' . nl2br(e($description)) . '</div>\n'
        . '    <p><a href="../../index.html">&larr; Back to projects</a></p>\n'
        . '</article>\n';

    write_html(
        $projectDir . DIRECTORY_SEPARATOR . 'index.html',
        shell_page($title, $body, 2, 'projects', extract_first_sentence($description), 'projects/' . $slug . '/index.html', $imagePath)
    );
}

echo "Static export complete: docs/index.html, docs/about.html, docs/contact.html and project pages generated.\n";
