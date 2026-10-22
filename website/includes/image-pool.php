<?php
/**
 * Per-service photograph pools. company.jpg and home.jpg are not pool members.
 * AOV pages draw a smoke-vent file and a separate AOV file.
 */

function icomplyImagePools(): array
{
    static $doc = null;
    if ($doc !== null) {
        return $doc;
    }
    $file = dirname(__DIR__) . '/data/service-image-pools.json';
    $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
    $doc = is_array($decoded) ? $decoded : [];
    return $doc;
}

function icomplyImageAlias(string $family): string
{
    return $family === 'aov' ? 'aov-air-handling' : $family;
}

function icomplyImageHash(string $path, int $slot): int
{
    return (int)hexdec(substr(hash('sha256', $path . '|' . $slot), 0, 8));
}

/** @param list<string> $list */
function icomplyImageHashed(array $list, string $path, int $slot): string
{
    if ($list === []) {
        return '/assets/images/real-job/resources.jpg';
    }
    return $list[icomplyImageHash($path, $slot) % count($list)];
}

/** @return list<string> */
function icomplyImagePick(string $path, string $family, int $count = 3): array
{
    $count = max(1, $count);
    $family = icomplyImageAlias($family);
    $entry = icomplyImagePools()[$family] ?? [];
    $smoke = array_values(is_array($entry['smoke'] ?? null) ? $entry['smoke'] : []);
    $aov = array_values(is_array($entry['aov'] ?? null) ? $entry['aov'] : []);
    $own = array_values(is_array($entry['own'] ?? null) ? $entry['own'] : []);
    if ($family === 'aov-air-handling' && $smoke !== [] && $aov !== []) {
        $first = icomplyImageHashed($smoke, $path, 0);
        $second = icomplyImageHashed($aov, $path, 1);
        $rest = array_values(array_filter(
            array_merge($smoke, $aov),
            static fn (string $src): bool => $src !== $first && $src !== $second
        ));
        $third = icomplyImageHashed($rest !== [] ? $rest : $aov, $path, 2);
        $picked = [$first, $second, $third];
        return array_slice($picked, 0, $count);
    }
    if ($own === []) {
        $rel = '/assets/images/services/' . $family . '.jpg';
        $root = dirname(__DIR__);
        $own = is_file($root . $rel) ? [$rel] : ['/assets/images/real-job/resources.jpg'];
    }
    $picked = [];
    $used = [];
    for ($slot = 0; $slot < $count; $slot++) {
        $choice = icomplyImageHashed($own, $path, $slot);
        if (isset($used[$choice]) && count($used) < count($own)) {
            foreach ($own as $offset => $candidate) {
                $alt = $own[($offset + $slot) % count($own)];
                if (!isset($used[$alt])) {
                    $choice = $alt;
                    break;
                }
            }
        }
        $used[$choice] = true;
        $picked[] = $choice;
    }
    return $picked;
}

function icomplyImageCollapsedAov(string $src): bool
{
    $path = (string)strtok($src, '?');
    if (preg_match('#/opt/a055da46974c-#i', $path)) {
        return true;
    }
    return (bool)preg_match('#/assets/images/services/aov-air-handling\.jpe?g$#i', $path);
}
