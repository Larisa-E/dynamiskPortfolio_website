<?php $baseUrl = isset($baseUrl) ? $baseUrl : rtrim($config['base_url'] ?? '', '/'); ?>
</main>
<footer class="site-footer">
    <p>&copy; <?= date('Y') ?> Larisa Portfolio. All rights reserved.</p>
</footer>
<script src="<?= $baseUrl ?>/assets/js/script.js"></script>
</body>
</html>
