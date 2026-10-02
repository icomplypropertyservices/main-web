<?php
/**
 * KEYWORDS_INDEX — directory index for /pages/keywords/index.
 * Vercel ignores website/pages/keywords/** (.vercelignore). The published hub
 * is pages/keywords-hub.php, required by pages/keywords.php. Keep this file
 * as a forwarder so the two URLs cannot drift.
 */
require dirname(__DIR__) . '/keywords-hub.php';
