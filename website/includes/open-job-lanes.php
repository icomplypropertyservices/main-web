<?php
/**
 * Job pages still missing from main after the landed lanes.
 *
 * Building fabric (207) is specific copy from the fabric pack.
 * Building (247) and gas (223) fill gaps from the building+gas pack.
 * Electrical adds master slugs that were not already in the catalogue.
 * Seven core-compliance hubs that were not already published.
 *
 * New slugs are hub-only: they do not join the 170-town keyword matrix.
 * Existing electrical, gas, EICR (£249) and CP12 (£85) rows are left in place.
 */
declare(strict_types=1);

function openJobCopyIsThin(array $row): bool
{
    $intro = trim((string)($row['intro'] ?? ''));
    if ($intro === '') {
        return true;
    }
    return (bool)preg_match(
        '/Looking for expert|Searching for professional|Need reliable|delivered to current UK standards|Our team plan, install and finish|plan, install and finish|From first survey to handover/i',
        $intro
    );
}

/** @return array{count?:int,jobs?:list<array<string,mixed>>} */
function openJobLoad(string $file): array
{
    static $cache = [];
    if (isset($cache[$file])) {
        return $cache[$file];
    }
    if (!is_file($file)) {
        return $cache[$file] = [];
    }
    $decoded = json_decode((string)file_get_contents($file), true);
    return $cache[$file] = (is_array($decoded) ? $decoded : []);
}

/** @return list<array<string,mixed>> */
function openJobRows(string $file): array
{
    $jobs = openJobLoad($file)['jobs'] ?? [];
    return is_array($jobs) ? $jobs : [];
}

function openJobFabricFile(): string
{
    return SITE_ROOT . '/data/job-packs/building-fabric.json';
}

function openJobBuildingFile(): string
{
    return SITE_ROOT . '/data/job-types-building.json';
}

function openJobGasFile(): string
{
    return SITE_ROOT . '/data/job-types-gas.json';
}

function openJobElectricalFile(): string
{
    return SITE_ROOT . '/data/electrical-jobs.json';
}

function openJobCoreFile(): string
{
    return SITE_ROOT . '/data/job-packs/core-compliance-extra.json';
}

/**
 * @param array<string,mixed> $job
 * @param array<string,mixed> $base
 * @return array<string,mixed>
 */
function openJobCopyFields(array $job, array $base): array
{
    $row = $base;
    foreach (['name', 'service', 'seo_title', 'h1', 'intro', 'body', 'meta_desc', 'seo_keywords', 'related'] as $field) {
        if (!empty($job[$field]) && is_string($job[$field])) {
            $row[$field] = $field === 'related' ? keywordSlug($job[$field]) : $job[$field];
        }
    }
    if (!empty($job['focus_points']) && is_array($job['focus_points'])) {
        $row['focus_points'] = $job['focus_points'];
    }
    if (!empty($job['faq']) && is_array($job['faq'])) {
        $row['faq'] = $job['faq'];
    }
    return $row;
}

/**
 * @param array<string,array<string,mixed>> $keywords
 * @param list<array<string,mixed>> $jobs
 * @param 'thin'|'missing' $mode
 * @return array<string,array<string,mixed>>
 */
function openJobApplyJobs(array $keywords, array $jobs, string $mode, string $flag): array
{
    foreach ($jobs as $job) {
        if (!is_array($job)) {
            continue;
        }
        $slug = keywordSlug((string)($job['slug'] ?? ''));
        if ($slug === '' || $slug === 'tunstall-nurse-call') {
            continue;
        }
        $existed = isset($keywords[$slug]) && is_array($keywords[$slug]);
        $base = $existed ? $keywords[$slug] : [];
        if ($mode === 'missing' && $existed) {
            continue;
        }
        if ($existed && in_array((string)($base['service'] ?? ''), ['electrical', 'gas-systems'], true)) {
            continue;
        }
        if ($mode === 'thin' && $existed && !openJobCopyIsThin($base)) {
            continue;
        }
        $row = openJobCopyFields($job, $base);
        if (empty($row['name'])) {
            $row['name'] = (string)($job['name'] ?? keywordDisplayName($slug));
        }
        if (empty($row['service'])) {
            $row['service'] = (string)($job['service'] ?? 'electrical');
        }
        if (empty($row['related'])) {
            $row['related'] = keywordSlug((string)($job['related'] ?? $slug));
        }
        if (!$existed) {
            $row['hub_only'] = true;
        }
        $row[$flag] = true;
        $keywords[$slug] = $row;
    }
    return $keywords;
}

/**
 * @return array{standard:string,focus:string}
 */
function openJobElectricalAngle(string $hay): array
{
    if (preg_match('/\beicr\b|condition report|periodic/', $hay)) {
        return [
            'standard' => 'BS 7671 Electrical Installation Condition Report (EICR).',
            'focus' => 'EICR coding (C1, C2, C3 and FI) with remedials quoted after the report',
            'price' => 'The published EICR list price is £249 for a typical North West 6-bed HMO. Other domestic sizes and commercial EICRs stay POA.',
        ];
    }
    if (preg_match('/rewire/', $hay)) {
        return [
            'standard' => 'BS 7671 rewire practice, with building-control notification where the work is notifiable.',
            'focus' => 'Full or partial rewire scoped against the containment that is already there',
            'price' => 'Rewires are POA after a look. This page does not publish a catalogue £ figure.',
        ];
    }
    if (preg_match('/consumer unit|fuse board|fuse box|rcbo|rcd|afdd|spd/', $hay)) {
        return [
            'standard' => 'BS 7671 consumer-unit rules, including enclosure, RCD or RCBO protection, and surge protection where required.',
            'focus' => 'Board replacement labelled and tested before it is energised',
            'price' => 'Consumer-unit work is POA after we see the board. This page does not publish a catalogue £ figure.',
        ];
    }
    if (preg_match('/\bpat\b|portable appliance/', $hay)) {
        return [
            'standard' => 'The IET Code of Practice for in-service inspection and testing of electrical equipment.',
            'focus' => 'PAT labelling and a register. Failures are quoted as remedials, POA',
            'price' => 'PAT is POA after we know the appliance count.',
        ];
    }
    if (preg_match('/landlord|hmo|tenanc|void|\blet\b/', $hay)) {
        return [
            'standard' => 'Landlord electrical safety: the EICR cycle, remedials, and the evidence a licence file usually asks for.',
            'focus' => 'Let-ready electrical evidence for agents and landlords',
            'price' => 'The published EICR list price is £249 for a typical North West 6-bed HMO. Other lets stay POA.',
        ];
    }
    return [
        'standard' => 'BS 7671, with notification where the work is notifiable.',
        'focus' => 'Electrical work scoped to the installation on site, from Stockport',
        'price' => 'The visit is POA after we know the property. This page does not publish a catalogue £ figure.',
    ];
}

/**
 * @param array<string,array<string,mixed>> $keywords
 * @return array<string,array<string,mixed>>
 */
function openJobApplyElectrical(array $keywords): array
{
    foreach (openJobRows(openJobElectricalFile()) as $job) {
        if (!is_array($job)) {
            continue;
        }
        $slug = keywordSlug((string)($job['slug'] ?? ''));
        if ($slug === '' || isset($keywords[$slug])) {
            continue;
        }
        $name = trim((string)($job['name'] ?? keywordDisplayName($slug)));
        $angle = openJobElectricalAngle(strtolower($slug . ' ' . $name));
        $related = keywordSlug((string)($job['related'] ?? 'eicr'));
        $keywords[$slug] = [
            'name' => $name,
            'service' => 'electrical',
            'related' => $related !== '' ? $related : 'eicr',
            'seo_title' => $name . ' | Electrical North West',
            'h1' => $name,
            'intro' => $name . ' is electrical work from our Stockport base. ' . $angle['standard'] . ' ' . $angle['price'],
            'body' => 'We look at the installation for ' . $name . ', confirm the scope against ' . $angle['standard'] . ' and send a written figure before we book the job. ' . $angle['price'] . ' Enquire with the postcode and whether the property is a house, an HMO or commercial.',
            'meta_desc' => $name . ' across the North West. POA after scope from Stockport. ' . $angle['price'],
            'seo_keywords' => $name . ', electrical, BS 7671, North West, Stockport, POA',
            'focus_points' => [
                $angle['focus'],
                $angle['price'],
                'Written scope before the visit is booked',
            ],
            'faq' => [
                ['How is ' . $name . ' priced?', $angle['price']],
                ['What standard covers ' . $name . '?', $angle['standard'] . ' The quote follows the installation you have, not a generic package.'],
            ],
            'hub_only' => true,
            'electrical_open_lane' => true,
        ];
    }
    return $keywords;
}

/**
 * @param array<string,array<string,mixed>> $keywords
 * @return array<string,array<string,mixed>>
 */
function openJobGasListPriceNote(string $slug): ?string
{
    if (!preg_match('/cp12|gas-safety|lgsr|landlord-gas/', $slug)) {
        return null;
    }
    return 'The published landlord gas safety record is £85 for a typical North West 6-bed HMO. Commercial plant rooms and repairs stay POA.';
}

/**
 * @param array<string,array<string,mixed>> $keywords
 * @return array<string,array<string,mixed>>
 */
function openJobApplyGasListPrice(array $keywords): array
{
    foreach ($keywords as $slug => $row) {
        if (empty($row['gas_open_lane']) || !is_array($row)) {
            continue;
        }
        $note = openJobGasListPriceNote((string)$slug);
        if ($note === null) {
            continue;
        }
        foreach (['intro', 'body', 'meta_desc'] as $field) {
            $text = (string)($row[$field] ?? '');
            $text = str_replace(
                'We do not publish a catalogue pound figure. Quotes stay POA / enquire.',
                $note,
                $text
            );
            $text = str_replace('We do not publish a catalogue pound figure.', $note, $text);
            $text = str_replace('There is no catalogue price for this. ', '', $text);
            $text = str_replace(
                'The figure is price on application after we know the appliance, the flue and the access.',
                'We confirm the £85 list against the property before booking.',
                $text
            );
            if (!str_contains($text, '£85')) {
                $text = rtrim($text, " \t.") . '. ' . $note;
            }
            $row[$field] = $text;
        }
        $keywords[$slug] = $row;
    }
    return $keywords;
}

/**
 * @param array<string,array<string,mixed>> $keywords
 * @return array<string,array<string,mixed>>
 */
function openJobLanesApply(array $keywords): array
{
    $keywords = openJobApplyJobs($keywords, openJobRows(openJobBuildingFile()), 'thin', 'building_lane');
    $keywords = openJobApplyJobs($keywords, openJobRows(openJobFabricFile()), 'thin', 'building_fabric');
    $keywords = openJobApplyJobs($keywords, openJobRows(openJobGasFile()), 'missing', 'gas_open_lane');
    $keywords = openJobApplyGasListPrice($keywords);
    $keywords = openJobApplyElectrical($keywords);
    $keywords = openJobApplyJobs($keywords, openJobRows(openJobCoreFile()), 'missing', 'core_open_lane');
    return $keywords;
}
