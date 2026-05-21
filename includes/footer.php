</main>

<?php if ($user): ?>
<?php include INCLUDES_PATH . '/mobile_nav.php'; ?>
<?php include INCLUDES_PATH . '/sidebar.php'; ?>
<?php endif; ?>

<!-- Toast Container -->
<div id="toast-container" class="toast-container"></div>

<!-- Scripts -->
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script src="<?= BASE_URL ?>/assets/js/animations.js"></script>
<script src="<?= BASE_URL ?>/assets/js/animations-pro.js"></script>
<script src="<?= BASE_URL ?>/assets/js/popup.js"></script>
<script>
    // Remove splash after load
    window.addEventListener('load', () => {
        setTimeout(() => {
            document.getElementById('splash-screen')?.classList.add('hide');
            setTimeout(() => document.getElementById('splash-screen')?.remove(), 500);
        }, 800);
    });
</script>
<?php if (isset($extra_js)): ?>
<?= $extra_js ?>
<?php endif; ?>
</body>
</html>
