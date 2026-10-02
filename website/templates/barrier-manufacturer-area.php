<?php
/**
 * One barrier manufacturer in one town.
 * Vars from renderBarrierManufacturerAreaPage(): $brand, $areaName, $pageTitle, $metaDesc, $metaKeywords, $canonicalUrl, $ogImage.
 */
require SITE_ROOT . '/includes/header.php';
echo barrierManufacturerAreaInnerHtml($brand, $areaName);
if (!empty($brand['partner'])) {
    echo cameLaneWizardHtml();
}
require SITE_ROOT . '/includes/footer.php';
