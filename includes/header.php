<?php
if (!isset($config)) {
    $configPath = __DIR__ . '/../config/config.php';
    if (!file_exists($configPath)) {
        http_response_code(500);
        die('Configuration missing. Copy config/config.sample.php to config/config.php and update credentials.');
    }

    $config = include $configPath;
}

$baseUrl = rtrim($config['base_url'], '/');
$rootUrl = preg_replace('#/public$#', '', $baseUrl) ?: $baseUrl;
$pageTitle = isset($pageTitle) ? $pageTitle . ' | ' : '';
$stylePath = __DIR__ . '/../public/assets/css/style.css';
$styleVersion = file_exists($stylePath) ? filemtime($stylePath) : time();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?>Larisa Portfolio</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/style.css?v=<?= $styleVersion ?>">
</head>
<body>
<header class="site-header">
    <nav class="nav">
        <a class="nav__brand" href="<?= $baseUrl ?>/">Larisa Portfolio</a>
        <ul class="nav__links">
            <li><a href="<?= $baseUrl ?>/">Projects</a></li>
            <li><a href="<?= $baseUrl ?>/about.php">About</a></li>
            <li><a href="<?= $baseUrl ?>/contact.php">Contact</a></li>
            <li><a href="<?= $rootUrl ?>/admin/login.php">Admin</a></li>
        </ul>
    </nav>
</header>
<main class="site-main">
