<?php
require_once dirname(__DIR__) . '/includes/product-pdp.php';
header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');
echo icomplyProductSitemapXml('/products/product');
