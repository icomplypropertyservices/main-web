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
- **Fire protection** (`fire-safety` category, AOV included) has a service×area page for **every** area slug, and those URLs **are** in `sitemap.xml`. AOV is the quality page. Do **not** mass-generate thin fire keyword×town copies.
- **Non-fire** service×area is Manchester and Burnley only, and those URLs stay **out** of `sitemap.xml`.
- **Never** list `/pages/electrical/{town}` or `/pages/gas-systems/{town}`. Electrical and gas stay on keyword×town pages.
- Do **not** mass-include thin keyword×area doorways in the sitemap. Featured electrical/gas × a handful of towns is allowed because those files exist and return 200.
- The full ~36k keyword×town matrix stays in `dist/` for Jack; it stays **out** of `sitemap.xml`.

## Copy / POA

- Cost, price, quote and “how much” keywords always say **POA**. Never invent a £ figure.
- Exception Jack set: a standard written fire risk assessment is **£350**. Do not copy that fee onto other services.
- UK English, Stockport / North West. No fake accreditations, reviews or prices.

## HMO packages

- HMO **package** landings stay out (`/pages/packages/hmo`, `/pages/packages/hmo-compliance`, and the PR #3 stubs).
- Keyword hubs such as `hmo-electrical-certificate` or `hmo-gas-safety` are ordinary guides, not package pages.

## Out of scope

- Legionella / CSS stays on its own PR.
- No `--prod` until Ellie.
MD;

file_put_contents($dataDir . '/seo-matrix-rollout-notes.md', $notes);

echo "Wrote seo-matrix-electrical.md ({$elecN})\n";
echo "Wrote seo-matrix-gas.md ({$gasN})\n";
echo "Wrote seo-matrix-rollout-notes.md\n";
