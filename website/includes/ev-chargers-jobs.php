<?php
/**
 * EV chargers job lane.
 * Service hub: /pages/services/ev-chargers
 * Keyword hubs: /pages/keywords/<slug> from data/ev-chargers-jobs.json
 *
 * Existing keyword copy is kept when it is already filled in.
 * This lane always owns service=ev-chargers. POA only — no invented £.
 */
declare(strict_types=1);

function evChargersJobsFile(): string
{
    return SITE_ROOT . '/data/ev-chargers-jobs.json';
}

/** @return array{count?:int,service?:string,jobs?:list<array<string,mixed>>} */
function evChargersJobsData(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $file = evChargersJobsFile();
    if (!is_file($file)) {
        return $data = [];
    }
    $decoded = json_decode((string)file_get_contents($file), true);
    return $data = (is_array($decoded) ? $decoded : []);
}

function evChargersJobsExpectedCount(): int
{
    $count = evChargersJobsData()['count'] ?? null;
    if (is_int($count) && $count > 0) {
        return $count;
    }
    return count(evChargersJobsJobs());
}

/** @return list<array<string,mixed>> */
function evChargersJobsJobs(): array
{
    $jobs = evChargersJobsData()['jobs'] ?? [];
    return is_array($jobs) ? $jobs : [];
}

/** @return list<string> */
function evChargersJobsSlugs(): array
{
    $out = [];
    foreach (evChargersJobsJobs() as $job) {
        if (!is_array($job)) {
            continue;
        }
        $slug = keywordSlug((string)($job['slug'] ?? ''));
        if ($slug !== '') {
            $out[] = $slug;
        }
    }
    return $out;
}

function evChargersJobsStubPath(string $slug): string
{
    return SITE_ROOT . '/pages/keywords/' . keywordSlug($slug) . '.php';
}

function evChargersJobsStubPhp(string $slug): string
{
    $slug = keywordSlug($slug);
    return <<<PHP
<?php
/** EV chargers job lane — generated stub. */
require_once __DIR__ . '/../../includes/render.php';
renderKeywordPage('{$slug}');

PHP;
}

/**
 * @param array<string, array<string, mixed>> $keywords
 * @return array<string, array<string, mixed>>
 */
function evChargersJobsApply(array $keywords): array
{
    foreach (evChargersJobsJobs() as $job) {
        if (!is_array($job)) {
            continue;
        }
        $slug = keywordSlug((string)($job['slug'] ?? ''));
        if ($slug === '') {
            continue;
        }
        $base = $keywords[$slug] ?? [];
        if (!is_array($base)) {
            $base = [];
        }
        $merged = $base;
        foreach (['name', 'intro', 'body', 'meta_desc', 'seo_keywords', 'seo_title', 'h1', 'related', 'focus_points', 'faq'] as $field) {
            $existing = $base[$field] ?? null;
            $incoming = $job[$field] ?? null;
            $hasExisting = is_string($existing)
                ? trim($existing) !== ''
                : (is_array($existing) && $existing !== []);
            if ($hasExisting) {
                $merged[$field] = $existing;
                continue;
            }
            if (is_string($incoming) && trim($incoming) !== '') {
                $merged[$field] = $incoming;
            } elseif (is_array($incoming) && $incoming !== []) {
                $merged[$field] = $incoming;
            }
        }
        $existingService = trim((string)($base['service'] ?? ''));
        $merged['service'] = $existingService !== '' ? $existingService : 'ev-chargers';
        $merged['ev_chargers_lane'] = true;
        if (trim((string)($merged['name'] ?? '')) === '') {
            $merged['name'] = keywordDisplayName($slug);
        }
        if (trim((string)($merged['seo_title'] ?? '')) === '') {
            $merged['seo_title'] = $merged['name'] . ' | EV Chargers | North West';
        }
        if (trim((string)($merged['h1'] ?? '')) === '') {
            $merged['h1'] = (string)$merged['name'];
        }
        if (trim((string)($merged['related'] ?? '')) === '') {
            $merged['related'] = $slug === 'ev-charger-installation' ? 'home-ev-charger' : 'ev-charger-installation';
        }
        $faqs = $merged['faq'] ?? [];
        if (!is_array($faqs)) {
            $faqs = [];
        }
        $poaQ = 'Is ' . $merged['name'] . ' priced on this page?';
        $hasPoa = false;
        foreach ($faqs as $existingFaq) {
            if (is_array($existingFaq) && strcasecmp((string)($existingFaq[0] ?? ''), $poaQ) === 0) {
                $hasPoa = true;
                break;
            }
        }
        if (!$hasPoa) {
            $faqs[] = [
                $poaQ,
                'No. Enquire and we quote POA after survey. This page does not publish a fee.',
            ];
        }
        $merged['faq'] = array_values($faqs);
        $keywords[$slug] = $merged;
    }

    $seenTitle = [];
    $seenH1 = [];
    $seenMeta = [];
    foreach ($keywords as $slug => &$row) {
        if (empty($row['ev_chargers_lane'])) {
            continue;
        }
        $title = trim((string)($row['seo_title'] ?? ''));
        if ($title === '' || isset($seenTitle[$title])) {
            $title = (string)$row['name'] . ' · ' . $slug . ' | EV Chargers';
            $row['seo_title'] = $title;
        }
        $seenTitle[$title] = true;
        $h1 = trim((string)($row['h1'] ?? ''));
        if ($h1 === '' || isset($seenH1[$h1])) {
            $h1 = (string)$row['name'] . ' (' . $slug . ')';
            $row['h1'] = $h1;
        }
        $seenH1[$h1] = true;
        $meta = trim((string)($row['meta_desc'] ?? ''));
        if ($meta === '' || isset($seenMeta[$meta])) {
            $meta = (string)$row['name'] . ' — EV chargers from Stockport across the North West. POA / enquire after scope.';
            $row['meta_desc'] = $meta;
        }
        $seenMeta[$meta] = true;
    }
    unset($row);

    return $keywords;
}
