<?php
/**
 * CCTV job lane: /pages/services/cctv plus every CCTV keyword guide.
 * Catalogue: data/cctv-jobs.json (73). Pages: pages/keywords/<slug>.php.
 */
declare(strict_types=1);

function cctvJobsData(): array
{
    $path = SITE_ROOT . '/data/cctv-jobs.json';
    if (!is_file($path)) {
        return ['count' => 0, 'service' => 'cctv', 'jobs' => []];
    }
    $decoded = json_decode((string)file_get_contents($path), true);
    return is_array($decoded) ? $decoded : ['count' => 0, 'service' => 'cctv', 'jobs' => []];
}

function cctvJobsExpectedCount(): int
{
    $data = cctvJobsData();
    $count = (int)($data['count'] ?? 0);
    return $count > 0 ? $count : 73;
}

/** @return list<array<string, mixed>> */
function cctvJobs(): array
{
    $jobs = cctvJobsData()['jobs'] ?? [];
    return is_array($jobs) ? array_values($jobs) : [];
}

/** @return list<string> */
function cctvJobsSlugs(): array
{
    $slugs = [];
    foreach (cctvJobs() as $job) {
        $slug = keywordSlug((string)($job['slug'] ?? ''));
        if ($slug !== '') {
            $slugs[] = $slug;
        }
    }
    return $slugs;
}

function cctvJobsStubPath(string $slug): string
{
    return SITE_ROOT . '/pages/keywords/' . keywordSlug($slug) . '.php';
}

function cctvJobsStubPhp(string $slug): string
{
    $slug = keywordSlug($slug);
    $exported = var_export($slug, true);
    return "<?php\n"
        . "/** CCTV job lane — php website/bin/generate-cctv-pages.php */\n"
        . "require_once __DIR__ . '/../../includes/render.php';\n"
        . "renderKeywordPage({$exported});\n";
}

/**
 * Service-hub copy for the CCTV lane only.
 *
 * @return array{intro:list<string>,pillars:list<array{title:string,text:string}>,sections:list<array{h2:string,p:list<string>}>}|null
 */
function cctvServiceCopy(string $slug): ?array
{
    if (areaSlug($slug) !== 'cctv') {
        return null;
    }
    return [
        'intro' => [
            'Icomply Property Services designs, installs and looks after CCTV for shops, warehouses, yards, offices, care settings and landlord common parts across Greater Manchester and the North West.',
            'The work follows the camera and the recorder you actually need: IP and HD views, NVRs and DVRs, ANPR lanes, remote viewing, and extra cameras on systems that still have channels. BS EN 62676 is the reference we design against. Camera aims are discussed with UK data-protection practice in mind, including signage and privacy masks where a view would otherwise cover neighbours.',
            'This page is the CCTV job lane. Every CCTV keyword guide is listed below and links back here. Quotes are written after a survey or a clear photo set. We do not publish a made-up camera or day rate on this page.',
        ],
        'pillars' => [
            ['title' => 'Design and install', 'text' => 'New IP and HD systems, ANPR lanes, cabling and recorders sized for the retention you asked for, commissioned with remote viewing for the people who need it.'],
            ['title' => 'Maintenance and repair', 'text' => 'Recorder faults, full disks, PoE drops, IR lamps, incident exports and cameras added to an existing system without guessing at spare capacity.'],
            ['title' => 'Siting and handover', 'text' => 'Privacy masks, GDPR-style siting reviews and operator training so staff can play footage back without sharing the installer password.'],
        ],
        'sections' => [
            [
                'h2' => 'What this CCTV lane covers',
                'p' => [
                    'Installs and upgrades (4K, HD, wireless, dome, bullet and PTZ), brand work for Hikvision, Axis, Dahua and the other systems already on site, and the practical jobs that follow: network changes, switch and PoE checks, hard-drive and NVR changes, loading-bay adds and landlord common-part cameras.',
                    'ANPR is scoped as its own view. A car-park overview is not treated as a plate camera unless the survey shows it can hold a plate. Monitoring and remote apps are set up for your staff. We do not invent a monitoring-centre contract on this page.',
                ],
            ],
        ],
    ];
}
