#!/usr/bin/env php
<?php
/**
 * Write website/data/seo-matrix-*.md from the live electrical + gas catalogue.
 * Those markdown files are the Marketing source of truth for this family.
 *
 * Usage: php website/bin/generate-seo-matrix-docs.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$dataDir = SITE_ROOT . '/data';

$writeFamily = static function (string $service, string $file, string $title) use ($dataDir): int {
    $rows = getKeywordsForService($service);
    ksort($rows);
    $lines = [];
    $lines[] = '# ' . $title;
    $lines[] = '';
    $lines[] = 'Marketing source of truth for this keyword family.';
    $lines[] = '';
    $lines[] = 'Each slug **must** have:';
    $lines[] = '- a hub at `/pages/keywords/{slug}`';
    $lines[] = '- a keyword×area page at `/pages/keywords/{slug}/{town}` for **every** town in `areas.json` (static export)';
    $lines[] = '';
    $lines[] = 'Cost / price / quote slugs are **POA only**. Never invent a £ figure.';
    $lines[] = '';
    $lines[] = 'Sitemap must **not** list the full keyword×area matrix (P090 / anti-junk).';
    $lines[] = 'See `seo-matrix-rollout-notes.md`.';
    $lines[] = '';
    $lines[] = '## Keywords (' . count($rows) . ')';
    $lines[] = '';
    foreach ($rows as $slug => $meta) {
        $name = trim((string)($meta['name'] ?? $slug));
        $poa = isCostStyleKeyword((string)$slug, $name) ? ' — POA' : '';
        $lines[] = '- `' . $slug . '` — ' . $name . $poa;
    }
    $lines[] = '';
    $path = $dataDir . '/' . $file;
    file_put_contents($path, implode("\n", $lines));
    return count($rows);
};

$elecN = $writeFamily('electrical', 'seo-matrix-electrical.md', 'SEO matrix — electrical');
$gasN = $writeFamily('gas-systems', 'seo-matrix-gas.md', 'SEO matrix — gas');

$notes = <<<'MD'
# SEO matrix rollout notes (P090 / anti-junk)

Official rules for the electrical + gas keyword expansion.

## Jack / export

- **Each** electrical and gas keyword in `seo-matrix-electrical.md` and `seo-matrix-gas.md` gets a hub **and** a keyword×area page for the site’s **full** areas list.
- Default Netlify `static-export.php` writes that full matrix into `dist/` (not behind `--full`).
- Do not wipe wave-1 hubs or other service keyword families.

## Sitemap (P090 — priority over new volume)

- `sitemap.xml` must **only** list URLs that return HTTP **200**.
- **Never** list `/pages/{service}/{town}` (gas-systems, electrical, fire-alarms, …). Those landings are `--full` only and 404 on the default export. Do **not** mass-generate thin service×area doorway pages for all 168 towns.
- Do **not** mass-include thin keyword×area doorways in the sitemap. Featured electrical/gas × a handful of towns is allowed because those files exist and return 200.
- The full ~36k keyword×town matrix stays in `dist/` for Jack; it stays **out** of `sitemap.xml`.

## Copy / POA

- Cost, price, quote and “how much” keywords always say **POA**. Never invent a £ figure.
- UK English, Stockport / North West. No fake accreditations, reviews or prices.

## HMO packages

- HMO **package** landings are in: `/pages/packages/hmo`, `/pages/packages/hmo-compliance`, `/pages/packages/hmo-fire-safety` and `/pages/packages/hmo-occupancy`, plus the quality hubs (landlords, EICR, FRA, gas, fire alarms, emergency lighting, fire doors, licence checklist).
- Stockport and Manchester landings exist only for EICR, FRA and gas. Prices stay **POA**.
- The sitemap lists those package and hub URLs because the files return 200. It still must not list `/pages/{service}/{town}`.
- Keyword hubs such as `hmo-electrical-certificate` or `hmo-gas-safety` stay ordinary guides, distinct from the package landings.

## Out of scope

- Legionella / CSS stays on its own PR.
- No `--prod` until Ellie.
MD;

file_put_contents($dataDir . '/seo-matrix-rollout-notes.md', $notes);

echo "Wrote seo-matrix-electrical.md ({$elecN})\n";
echo "Wrote seo-matrix-gas.md ({$gasN})\n";
echo "Wrote seo-matrix-rollout-notes.md\n";
