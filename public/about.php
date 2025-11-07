<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$baseUrl = rtrim($config['base_url'] ?? '', '/');
$pageTitle = 'About';

include __DIR__ . '/../includes/header.php';
?>
<section class="about">
    <h1>About Larisa</h1>
    <p>This page is ready for your biography, mission, and the story behind each project. Update the markup to pull dynamic content later if needed.</p>
</section>
<?php include __DIR__ . '/../includes/footer.php';
