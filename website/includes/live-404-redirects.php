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
