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
