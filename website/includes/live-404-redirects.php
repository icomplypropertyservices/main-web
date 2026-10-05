<?php
/**
 * Exact 301s for the October 2026 live-404 probe.
 * Town pages are real files. These rows are aliases, typos and root vanity paths.
 * Locksmith is not a service iComply sells.
 */
declare(strict_types=1);

/**
 * @return array<string,string> request path => destination path
 */
function icomplyLive404Redirects(): array
{
    $keywords = [
        '24-hour-electrician' => '/pages/keywords/24-hour-electrician',
        '24-hour-plumber' => '/pages/keywords/24-hour-plumber',
        '24hr-electrician' => '/pages/keywords/24-hour-electrician',
        'emergency-aov' => '/pages/keywords/emergency-aov',
        'emergency-barrier-repair' => '/pages/keywords/emergency-barrier-repair',
        'emergency-boiler-repair' => '/pages/keywords/emergency-boiler-repair',
        'emergency-eicr' => '/pages/keywords/emergency-eicr',
        'emergency-electrician' => '/pages/keywords/emergency-electrician',
        'emergency-fire-alarm' => '/pages/keywords/emergency-fire-alarm',
        'emergency-gas-engineer' => '/pages/keywords/emergency-gas-engineer',
        'emergency-heating' => '/pages/keywords/emergency-heating',
        'emergency-locksmith' => '/contact',
        'emergency-plumber' => '/pages/keywords/emergency-plumber',
        'next-day-aov' => '/pages/keywords/next-day-aov',
        'next-day-boiler-repair' => '/pages/keywords/next-day-boiler-repair',
        'next-day-eicr' => '/pages/keywords/next-day-eicr',
        'next-day-electrician' => '/pages/keywords/next-day-electrician',
        'next-day-fire-door' => '/pages/keywords/next-day-fire-door',
        'next-day-gas-engineer' => '/pages/keywords/next-day-gas-engineer',
        'next-day-heating-engineer' => '/pages/keywords/next-day-heating-engineer',
        'next-day-plumber' => '/pages/keywords/next-day-plumber',
        'same-day-aov-repair' => '/pages/keywords/same-day-aov-repair',
        'same-day-boiler-repair' => '/pages/keywords/same-day-boiler-repair',
        'same-day-eicr' => '/pages/keywords/same-day-eicr',
        'same-day-electrician' => '/pages/keywords/same-day-electrician',
        'same-day-gas-engineer' => '/pages/keywords/same-day-gas-engineer',
        'same-day-plumber' => '/pages/keywords/same-day-plumber',
    ];
    $map = [
        '/pages/cookies' => '/privacy',
        '/pages/thanks' => '/thank-you',
        '/pages/keywords/24hr-electrician' => '/pages/keywords/24-hour-electrician',
        '/pages/keywords/emergency-locksmith' => '/contact',
        '/pages/aov/redish' => '/pages/aov/reddish',
        '/pages/barriers/redish' => '/pages/barriers/reddish',
        '/pages/aov/newcastle' => '/pages/aov/newcastle-upon-tyne',
        '/pages/keywords/24hr-aov' => '/pages/keywords/24-hour-aov',
        '/pages/keywords/24hr-gas-engineer' => '/pages/keywords/emergency-gas-engineer',
        '/pages/keywords/24hr-plumber' => '/pages/keywords/24-hour-plumber',
        '/pages/keywords/emergency-emergency-lighting' => '/pages/services/emergency-lighting',
        '/pages/keywords/next-day-locksmith' => '/contact',
        '/pages/keywords/same-day-locksmith' => '/contact',
        '/pages/fire-alarms/redish' => '/pages/fire-alarms/reddish',
        '/pages/emergency-lighting/redish' => '/pages/emergency-lighting/reddish',
        '/pages/fire-doors/redish' => '/pages/fire-doors/reddish',
        // Pack A+C wave2 — fire hub indexes, section roots, vanity aliases (2026-10-05)
        '/24-hour-aov' => '/pages/keywords/24-hour-aov',
        '/24-hour-barrier' => '/pages/keywords/24-hour-barrier',
        '/24-hour-emergency-electrician' => '/pages/keywords/24-hour-emergency-electrician',
        '/24-hour-gas-engineer' => '/pages/keywords/emergency-gas-engineer',
        '/24hr-aov' => '/pages/keywords/24-hour-aov',
        '/24hr-gas-engineer' => '/pages/keywords/emergency-gas-engineer',
        '/24hr-plumber' => '/pages/keywords/24-hour-plumber',
        '/aov' => '/pages/aov',
        '/aov-air-handling' => '/pages/services/aov-air-handling',
        '/areas' => '/pages/areas',
        '/barriers' => '/pages/services/barriers',
        '/boiler-repair' => '/pages/keywords/boiler-repair',
        '/eicr-certificate' => '/pages/keywords/eicr-certificate',
        '/eicr-near-me' => '/pages/keywords/eicr-near-me',
        '/electrician-near-me' => '/pages/keywords/electrician-near-me',
        '/emergency' => '/pages/emergency',
        '/emergency-barrier' => '/pages/keywords/emergency-barrier',
        '/emergency-cctv' => '/pages/keywords/emergency-cctv',
        '/emergency-emergency-lighting' => '/pages/services/emergency-lighting',
        '/emergency-fire-door' => '/pages/keywords/emergency-fire-door',
        '/emergency-lighting' => '/pages/services/emergency-lighting',
        '/emergency-sprinkler' => '/pages/keywords/emergency-sprinkler',
        '/fire' => '/pages/services/fire-alarms',
        '/fire-alarm' => '/pages/services/fire-alarms',
        '/fire-alarm-engineer-near-me' => '/pages/keywords/fire-alarm-engineer-near-me',
        '/fire-alarms' => '/pages/services/fire-alarms',
        '/fire-risk-assessment-near-me' => '/pages/keywords/fire-risk-assessment-near-me',
        '/gas-engineer-near-me' => '/pages/keywords/gas-engineer-near-me',
        '/jobs' => '/pages/jobs',
        '/keywords' => '/pages/keywords',
        '/maintenance' => '/pages/maintenance',
        '/manufacturers' => '/pages/manufacturers',
        '/next-day-barrier' => '/pages/keywords/next-day-barrier',
        '/next-day-heating' => '/pages/keywords/next-day-heating',
        '/next-day-locksmith' => '/contact',
        '/pages/aov-air-handling' => '/pages/services/aov-air-handling',
        '/pages/dry-risers' => '/pages/services/dry-risers',
        '/pages/emergency-lighting' => '/pages/services/emergency-lighting',
        '/pages/emergency-lighting/tameside' => '/pages/emergency-lighting/manchester',
        '/pages/emergency-lighting/trafford' => '/pages/emergency-lighting/manchester',
        '/pages/evacuation-alerts' => '/pages/services/evacuation-alerts',
        '/pages/fire' => '/pages/services/fire-alarms',
        '/pages/fire-alarm' => '/pages/services/fire-alarms',
        '/pages/fire-alarms' => '/pages/services/fire-alarms',
        '/pages/fire-alarms/tameside' => '/pages/fire-alarms/manchester',
        '/pages/fire-alarms/trafford' => '/pages/fire-alarms/manchester',
        '/pages/fire-compartmentation' => '/pages/services/fire-compartmentation',
        '/pages/fire-doors' => '/pages/services/fire-doors',
        '/pages/fire-doors/tameside' => '/pages/fire-doors/manchester',
        '/pages/fire-doors/trafford' => '/pages/fire-doors/manchester',
        '/pages/fire-extinguishers' => '/pages/services/fire-extinguishers',
        '/pages/fire-risk-assessments' => '/pages/services/fire-risk-assessments',
        '/pages/fire-signage' => '/pages/services/fire-signage',
        '/pages/fire-stopping' => '/pages/services/fire-stopping',
        '/pages/fire-suppression' => '/pages/services/fire-suppression',
        '/pages/firealarms' => '/pages/services/fire-alarms',
        '/pages/kitchen-fire-suppression' => '/pages/services/kitchen-fire-suppression',
        '/pages/smoke-co-alarms' => '/pages/services/smoke-co-alarms',
        '/pages/sprinkler-systems' => '/pages/services/sprinkler-systems',
        '/pat-testing-near-me' => '/pages/keywords/pat-testing-near-me',
        '/plumber-near-me' => '/pages/keywords/plumber-near-me',
        '/pricing' => '/pages/pricing',
        '/resources' => '/pages/resources',
        '/reviews' => '/pages/reviews',
        '/same-day-barrier' => '/pages/keywords/same-day-barrier',
        '/same-day-boiler' => '/pages/keywords/same-day-boiler',
        '/same-day-fire-alarm' => '/pages/keywords/same-day-fire-alarm',
        '/same-day-heating' => '/pages/keywords/same-day-heating',
        '/same-day-heating-engineer' => '/pages/keywords/same-day-heating-engineer',
        '/same-day-locksmith' => '/contact',
        '/services' => '/pages/services',
        '/site-map' => '/pages/site-map',
        '/sitemap' => '/sitemap.xml',
        // Pack D keyword aliases + redish fire typos
        '/pages/emergency-lighting/redish' => '/pages/emergency-lighting/reddish',
        '/pages/fire-alarms/redish' => '/pages/fire-alarms/reddish',
        '/pages/fire-doors/redish' => '/pages/fire-doors/reddish',
        '/pages/keywords/24hr-aov' => '/pages/keywords/24-hour-aov',
        '/pages/keywords/24hr-gas-engineer' => '/pages/keywords/emergency-gas-engineer',
        '/pages/keywords/24hr-plumber' => '/pages/keywords/24-hour-plumber',
        '/pages/keywords/emergency-barrier' => '/pages/keywords/emergency-barrier-repair',
        '/pages/keywords/emergency-emergency-lighting' => '/pages/services/emergency-lighting',
        '/pages/keywords/next-day-locksmith' => '/contact',
        '/pages/keywords/same-day-boiler' => '/pages/keywords/same-day-boiler-repair',
        '/pages/keywords/same-day-heating' => '/pages/keywords/same-day-heating-engineer',
        '/pages/keywords/same-day-locksmith' => '/contact',
    ];
    foreach ($keywords as $slug => $dest) {
        $map['/' . $slug] = $dest;
    }
    return $map;
}

function icomplyLive404RedirectLines(): string
{
    $lines = [
        '# Live 404 aliases: cookies/thanks, keyword typos, root vanity paths.',
        '# Exact paths only. Keyword x town is not redirected.',
    ];
    foreach (icomplyLive404Redirects() as $from => $to) {
        $lines[] = $from . '  ' . $to . '  301';
        $lines[] = rtrim($from, '/') . '/  ' . $to . '  301';
    }
    return implode("\n", $lines) . "\n";
}
