<?php
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config.php';
}
require_once __DIR__ . '/share.php';
require_once __DIR__ . '/site-nav.php';
require_once __DIR__ . '/netlify-badge.php';
?>
</div><!-- /#main-content -->
<?= icomplyFooterHtml() ?>
<?php
require_once __DIR__ . '/cookie-banner.php';
require_once __DIR__ . '/lead-popup.php';
echo icomplyNetlifyBadgeStripHtml();
?>
</body>
</html>
