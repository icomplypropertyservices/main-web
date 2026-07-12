<?php
/**
 * Legacy path kept for local clarity — Vercel uses /api/index.php at repo root.
 * If hit directly, forward into the same front-controller behaviour.
 */
require dirname(__DIR__, 2) . '/api/index.php';
