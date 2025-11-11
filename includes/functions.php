<?php
/**
 * Generate a URL-friendly slug from a title.
 */
function make_slug(string $value): string
{
    $value = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
    $value = preg_replace('/[^a-zA-Z0-9\s-]/', '', $value);
    $value = strtolower(trim($value));
    $value = preg_replace('/[\s-]+/', '-', $value);

    return $value ?: uniqid('project-', true);
}

/**
 * Escape output for HTML contexts.
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function linkify_text(string $value): string
{
    $escaped = e($value);

    $escaped = preg_replace_callback('/\b(GitHub|LinkedIn)\s*\((https?:\/\/[^\s<)]+)\)/i', static function (array $match): string {
        $label = htmlspecialchars($match[1], ENT_QUOTES, 'UTF-8');
        $url = htmlspecialchars($match[2], ENT_QUOTES, 'UTF-8');

        return '<a href="' . $url . '" target="_blank" rel="noopener">' . $label . '</a>';
    }, $escaped) ?? $escaped;

    return preg_replace_callback('/(?<!href=")(https?:\/\/[^\s<]+)/i', static function (array $match): string {
        $url = $match[1];
        $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $label = preg_replace('/^https?:\/\/(www\.)?/i', '', $url);
        $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');

        return '<a href="' . $safeUrl . '" target="_blank" rel="noopener">' . $label . '</a>';
    }, $escaped) ?? $escaped;
}

function ensure_project_video_column(PDO $pdo): void
{
    static $checked = false;

    if ($checked) {
        return;
    }

    $checked = true;

    $columnQuery = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'projects'");
    $columnQuery->execute();
    $columns = $columnQuery->fetchAll(PDO::FETCH_COLUMN) ?: [];

    if (!in_array('demo_video_url', $columns, true)) {
        $pdo->exec("ALTER TABLE projects ADD COLUMN demo_video_url VARCHAR(255) NULL AFTER image");
    }
}

function render_project_video(?string $url): ?string
{
    $url = trim((string) $url);

    if ($url === '') {
        return null;
    }

    if (preg_match('~(?:youtube\.com/(?:watch\?v=|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~i', $url, $matches)) {
        $embedId = $matches[1];
        $embedUrl = 'https://www.youtube.com/embed/' . $embedId;

        return '<div class="project-detail__video"><iframe src="' . e($embedUrl) . '" title="Project video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy"></iframe></div>';
    }

    if (preg_match('~vimeo\.com/(?:video/)?([0-9]+)~i', $url, $matches)) {
        $embedUrl = 'https://player.vimeo.com/video/' . $matches[1];

        return '<div class="project-detail__video"><iframe src="' . e($embedUrl) . '" title="Project video" allow="autoplay; fullscreen" allowfullscreen loading="lazy"></iframe></div>';
    }

    if (preg_match('~\.(mp4|webm|ogg)(?:\?.*)?$~i', $url)) {
        $safe = e($url);

        return '<div class="project-detail__video"><video controls preload="metadata"><source src="' . $safe . '"></video></div>';
    }

    $safeUrl = e($url);

    return '<p class="project-detail__video-link"><a href="' . $safeUrl . '" target="_blank" rel="noopener">Watch project demo</a></p>';
}
