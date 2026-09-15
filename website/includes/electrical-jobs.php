<?php
/**
 * Electrical master catalogue — exactly 340 unique slugs under /pages/keywords/<slug>.
 * Pages are emitted by bin/generate-electrical-pages.php from this data + the keyword template.
 */
declare(strict_types=1);

function electricalJobsExpectedCount(): int
{
    return 340;
}

function electricalJobsFile(): string
{
    return SITE_ROOT . '/data/electrical-jobs.json';
}

/** @return array{count?:int,jobs?:list<array<string,mixed>>} */
function electricalJobsData(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $file = electricalJobsFile();
    if (!is_file($file)) {
        return $data = [];
    }
    $decoded = json_decode((string)file_get_contents($file), true);
    return $data = (is_array($decoded) ? $decoded : []);
}

/** @return list<array{slug:string,name:string,service:string,related?:string}> */
function electricalJobsJobs(): array
{
    $jobs = electricalJobsData()['jobs'] ?? [];
    return is_array($jobs) ? $jobs : [];
}

/** @return list<string> */
function electricalJobsSlugs(): array
{
    $out = [];
    foreach (electricalJobsJobs() as $job) {
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

function electricalJobsStubPath(string $slug): string
{
    return SITE_ROOT . '/pages/keywords/' . keywordSlug($slug) . '.php';
}

/**
 * Merge the 340 Electrical jobs into the live keyword catalogue with unique SEO fields.
 *
 * @param array<string, array<string, mixed>> $keywords
 * @return array<string, array<string, mixed>>
 */
function electricalJobsApply(array $keywords): array
{
    foreach (electricalJobsJobs() as $job) {
        if (!is_array($job)) {
            continue;
        }
        $slug = keywordSlug((string)($job['slug'] ?? ''));
        if ($slug === '') {
            continue;
        }
        $job['service'] = 'electrical';
        $base = $keywords[$slug] ?? [];
        if (!is_array($base)) {
            $base = [];
        }
        $synth = function_exists('jobTypesSynthesize')
            ? jobTypesSynthesize($job, $base)
            : electricalJobsFallbackSynth($job, $slug);
        $synth = electricalJobsEnrich($job, $synth, $slug);
        $merged = function_exists('jobTypesMergePreferExisting')
            ? jobTypesMergePreferExisting($base, $synth)
            : array_merge($synth, array_filter($base, static fn($v) => $v !== null && $v !== '' && $v !== []));
        $merged['service'] = 'electrical';
        $merged['electrical_master'] = true;
        $keywords[$slug] = $merged;
    }

    $seenTitle = [];
    $seenH1 = [];
    $seenMeta = [];
    foreach ($keywords as $slug => &$row) {
        if (empty($row['electrical_master'])) {
            $title = trim((string)($row['seo_title'] ?? $row['name'] ?? $slug));
            $h1 = trim((string)($row['h1'] ?? $row['name'] ?? $slug));
            $meta = trim((string)($row['meta_desc'] ?? ''));
            if ($title !== '') {
                $seenTitle[$title] = true;
            }
            if ($h1 !== '') {
                $seenH1[$h1] = true;
            }
            if ($meta !== '') {
                $seenMeta[$meta] = true;
            }
        }
    }
    unset($row);

    foreach ($keywords as $slug => &$row) {
        if (empty($row['electrical_master'])) {
            continue;
        }
        $name = (string)($row['name'] ?? keywordDisplayName((string)$slug));
        $title = trim((string)($row['seo_title'] ?? $name));
        if ($title === '' || isset($seenTitle[$title])) {
            $title = $name . ' · Electrical · ' . $slug . ' | iComply';
            $row['seo_title'] = $title;
        }
        $seenTitle[$title] = true;
        $h1 = trim((string)($row['h1'] ?? $name));
        if ($h1 === '' || isset($seenH1[$h1])) {
            $h1 = $name . ' (' . $slug . ')';
            $row['h1'] = $h1;
        }
        $seenH1[$h1] = true;
        $meta = trim((string)($row['meta_desc'] ?? ''));
        if ($meta === '' || isset($seenMeta[$meta])) {
            $meta = $name . ' — Electrical works from Stockport across the North West. POA / enquire after scope. No invented £ prices.';
            $row['meta_desc'] = $meta;
        }
        $seenMeta[$meta] = true;
    }
    unset($row);
    return $keywords;
}

/**
 * @param array<string, mixed> $job
 * @param array<string, mixed> $synth
 * @return array<string, mixed>
 */
function electricalJobsEnrich(array $job, array $synth, string $slug): array
{
    $name = trim((string)($job['name'] ?? $synth['name'] ?? keywordDisplayName($slug)));
    $hay = strtolower($slug . ' ' . $name);
    $angle = electricalJobsAngle($hay);
    $synth['name'] = $name;
    $synth['service'] = 'electrical';
    $synth['seo_keywords'] = $name . ', Electrical, EICR, BS 7671, North West, Stockport, POA';

    $faqs = $synth['faq'] ?? [];
    if (!is_array($faqs)) {
        $faqs = [];
    }
    $extra = [
        ['Is ' . $name . ' quoted as POA?', 'Yes. We enquire on access, standards and materials first, then send a written POA figure. This page never publishes an invented £ price.'],
        ['What standard covers ' . $name . '?', $angle['standard'] . ' Scope is confirmed against the installation you actually have, not a generic package.'],
    ];
    foreach ($extra as $pair) {
        $q = $pair[0];
        $dup = false;
        foreach ($faqs as $existing) {
            if (is_array($existing) && strcasecmp((string)($existing[0] ?? ''), $q) === 0) {
                $dup = true;
                break;
            }
        }
        if (!$dup) {
            $faqs[] = $pair;
        }
    }
    $synth['faq'] = array_values($faqs);
    if (empty($synth['focus_points']) || !is_array($synth['focus_points'])) {
        $synth['focus_points'] = [];
    }
    $synth['focus_points'][] = $angle['focus'];
    $synth['focus_points'][] = 'POA / enquire CTA only — no invented £';
    $synth['focus_points'] = array_values(array_unique(array_map('strval', $synth['focus_points'])));
    return $synth;
}

/** @return array{standard:string,focus:string} */
function electricalJobsAngle(string $hay): array
{
    if (preg_match('/\beicr\b|condition report|periodic/', $hay)) {
        return ['standard' => 'BS 7671 Electrical Installation Condition Report (EICR).', 'focus' => 'EICR coding (C1/C2/C3/FI) with a clear remedial path'];
    }
    if (preg_match('/rewire/', $hay)) {
        return ['standard' => 'BS 7671 rewire and Part P notification where the work is notifiable.', 'focus' => 'Full or partial rewire scoped against existing containment'];
    }
    if (preg_match('/consumer unit|fuse board|fuse box|rcbo|rcd|afdd|spd/', $hay)) {
        return ['standard' => 'BS 7671 Amendment 2 consumer-unit rules (RCD/RCBO, SPD, enclosure).', 'focus' => 'Board replacement labelled and tested before energising'];
    }
    if (preg_match('/\bpat\b|portable appliance/', $hay)) {
        return ['standard' => 'IETS Code of Practice for in-service inspection and testing of electrical equipment.', 'focus' => 'PAT labelling and a register — failures quoted as remedials, POA'];
    }
    if (preg_match('/ev charger|charge point|ev charge/', $hay)) {
        return ['standard' => 'BS 7671 Section 722 and the current EV charge-point installation guidance.', 'focus' => 'Supply capacity, earthing and isolator checked before an EV point'];
    }
    if (preg_match('/three phase|3 phase/', $hay)) {
        return ['standard' => 'BS 7671 three-phase distribution, isolation and testing.', 'focus' => 'Three-phase tails, isolation and Ze/Zs recorded'];
    }
    if (preg_match('/socket|fused spur|cooker|commando/', $hay)) {
        return ['standard' => 'BS 7671 final-circuit accessories and IP ratings.', 'focus' => 'Circuit capacity and RCD protection confirmed before extra sockets'];
    }
    if (preg_match('/lighting circuit|lighting|downlight|floodlight/', $hay)) {
        return ['standard' => 'BS 7671 lighting circuits; emergency luminaires follow BS 5266 when specified.', 'focus' => 'Switching, containment and emergency-lighting interfaces scoped on site'];
    }
    if (preg_match('/emergency light/', $hay)) {
        return ['standard' => 'Electrical supply and wiring for BS 5266 emergency lighting — quoted as electrical works.', 'focus' => 'Emergency-lighting circuits wired and tested as electrical works'];
    }
    if (preg_match('/landlord|hmo|tenanc|void|let ready/', $hay)) {
        return ['standard' => 'Landlord electrical safety (EICR cycle, remedials, HMO licence evidence).', 'focus' => 'Let-ready electrical evidence for agents and local authorities'];
    }
    return ['standard' => 'BS 7671 (18th Edition) and Part P where the work is notifiable.', 'focus' => 'Electrical works scoped to BS 7671 from our Stockport SK2 base'];
}

/**
 * @param array<string, mixed> $job
 * @return array<string, mixed>
 */
function electricalJobsFallbackSynth(array $job, string $slug): array
{
    $name = trim((string)($job['name'] ?? keywordDisplayName($slug)));
    return [
        'name' => $name,
        'service' => 'electrical',
        'related' => keywordSlug((string)($job['related'] ?? 'eicr')),
        'seo_title' => $name . ' | Electrical North West',
        'h1' => $name,
        'intro' => $name . ' from iComply electrical engineers based in Stockport. Scope first, then a written POA figure.',
        'body' => 'We survey the installation, confirm BS 7671 requirements and issue the paperwork that this electrical job actually needs. Enquire with postcode and property type — we do not invent a catalogue £ price.',
        'meta_desc' => $name . ' across the North West. POA after scope from Stockport engineers.',
        'faq' => [
            ['How do you price ' . $name . '?', 'Price on application after we confirm access, standards and materials. No invented £ on this page.'],
        ],
        'focus_points' => ['POA / enquire after scope'],
    ];
}

function electricalJobsStubPhp(string $slug): string
{
    $slugExport = var_export(keywordSlug($slug), true);
    return "<?php\n"
        . "/** AUTO-GENERATED Electrical stub — php website/bin/generate-electrical-pages.php */\n"
        . "require_once __DIR__ . '/../../includes/render.php';\n"
        . "renderKeywordPage({$slugExport});\n";
}
