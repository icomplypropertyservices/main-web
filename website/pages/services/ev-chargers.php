<?php
/**
 * EV chargers service hub.
 * Clean URL /pages/services/ev-chargers is also dispatched by the router
 * to renderServiceHubPage() once the slug is in services.json.
 */
require_once __DIR__ . '/../../includes/render.php';
renderServiceHubPage('ev-chargers');
