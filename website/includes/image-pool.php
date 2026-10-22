<?php
/**
 * Per-service photograph pools. company.jpg and home.jpg are not pool members.
 * /pages/aov draws a smoke-vent file and a separate AOV file.
 * AOV air-handling, keyword, job and resource pages use their own sets.
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
    return $family;
}

function icomplyImageHash(string $path, int $slot): int
{
    return (int)hexdec(substr(hash('sha256', $path . '|' . $slot), 0, 8));
}

/** @param list<string> $list */
function icomplyImageHashed(array $list, string $path, int $slot): string
{
    if ($list === []) {
        return '/assets/images/real-job/directory-hub.jpg';
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
    if ($family === 'aov' && $smoke !== [] && $aov !== []) {
        // Hero rotates across the AOV equipment pool. The second slot comes from the other group.
        $first = icomplyImageHashed(array_merge($smoke, $aov), $path, 0);
        $second = icomplyImageHashed(in_array($first, $smoke, true) ? $aov : $smoke, $path, 1);
        $rest = array_values(array_filter(
            array_merge($smoke, $aov),
            static fn (string $src): bool => $src !== $first && $src !== $second
        ));
        $third = icomplyImageHashed($rest !== [] ? $rest : $aov, $path, 2);
        $picked = [$first, $second, $third];
        return array_slice($picked, 0, $count);
    }
    if ($own === []) {
        $root = dirname(__DIR__);
        if (str_starts_with($family, 'type-') || str_starts_with($family, 'page-')) {
            $fallback = icomplyImagePools()['type-keywords-building-maintenance']['own'] ?? [];
            $own = is_array($fallback) && $fallback !== []
                ? array_values($fallback)
                : ['/assets/images/real-job/directory-hub.jpg'];
        } else {
            $rel = '/assets/images/services/' . $family . '.jpg';
            $own = is_file($root . $rel) ? [$rel] : ['/assets/images/real-job/directory-hub.jpg'];
        }
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

/** @return array<string, true> */
function icomplyImageAcCluster(): array
{
    static $set = null;
    if ($set !== null) {
        return $set;
    }
    $set = [];
    $file = dirname(__DIR__) . '/data/ac-photo-cluster.json';
    $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
    foreach (is_array($decoded['paths'] ?? null) ? $decoded['paths'] : [] as $item) {
        $set[(string)$item] = true;
    }
    return $set;
}

function icomplyImageCollapsedAov(string $src): bool
{
    $path = (string)strtok($src, '?');
    $path = (string)preg_replace('#^https?://[^/]+#i', '', $path);
    if (preg_match('#/opt/a055da46974c-#i', $path)) {
        return true;
    }
    if (preg_match('#/assets/images/services/aov-air-handling(?:-(?:photo|context))?\.jpe?g$#i', $path)) {
        return true;
    }
    return isset(icomplyImageAcCluster()[$path]);
}

function icomplyImagePoolHas(string $family, string $src): bool
{
    $family = icomplyImageAlias($family);
    $entry = icomplyImagePools()[$family] ?? [];
    $path = (string)strtok($src, '?');
    $path = (string)preg_replace('#^https?://[^/]+#i', '', $path);
    foreach (['smoke', 'aov', 'own'] as $bucket) {
        $list = is_array($entry[$bucket] ?? null) ? $entry[$bucket] : [];
        foreach ($list as $item) {
            $item = (string)$item;
            if ($path === $item || ($item !== '' && str_ends_with($path, $item))) {
                return true;
            }
        }
    }
    return false;
}

/** @return array<string,string> */
function icomplyKeywordServiceMap(): array
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }
    $map = [];
    $primary = dirname(__DIR__) . '/data/keyword-services.json';
    $decoded = is_file($primary) ? json_decode((string)file_get_contents($primary), true) : [];
    if (is_array($decoded)) {
        foreach ($decoded as $slug => $service) {
            if (is_string($slug) && is_string($service) && $service !== '') {
                $map[$slug] = $service;
            }
        }
    }
    $fallbackFile = dirname(__DIR__) . '/data/keyword-service-fallback.json';
    $fallback = is_file($fallbackFile) ? json_decode((string)file_get_contents($fallbackFile), true) : [];
    if (is_array($fallback)) {
        foreach ($fallback as $slug => $service) {
            if (!isset($map[$slug]) && is_string($service) && $service !== '') {
                $map[(string)$slug] = $service;
            }
        }
    }
    return $map;
}

/** @return array<string,array<string,mixed>> */
function icomplyCatalogueRows(): array
{
    static $rows = null;
    if ($rows !== null) {
        return $rows;
    }
    $file = dirname(__DIR__) . '/data/seo-lock/p0-index.json';
    $doc = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
    if (is_array($doc['rows'] ?? null)) {
        $rows = $doc['rows'];
    } elseif (is_array($doc)) {
        $rows = $doc;
    } else {
        $rows = [];
    }
    return $rows;
}

/** @return array<string,string> */
function icomplyResourceServiceMap(): array
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }
    $file = dirname(__DIR__) . '/data/resource-article-images.json';
    $doc = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
    $map = [];
    if (is_array($doc)) {
        foreach ($doc as $slug => $service) {
            if (is_string($slug) && is_string($service)) {
                $map[$slug] = $service;
            }
        }
    }
    return $map;
}

function icomplyPageImageKey(string $path): string
{
    $path = (string)preg_replace('/\.php$/i', '', $path);
    $parts = array_values(array_filter(explode('/', $path), static fn ($part) => $part !== ''));
    $head = ($parts[0] ?? '') === 'pages' ? ($parts[1] ?? '') : ($parts[0] ?? '');
    $slug = ($parts[0] ?? '') === 'pages' ? ($parts[2] ?? '') : ($parts[1] ?? '');
    if ($head === 'aov' || ($head === 'services' && $slug === 'aov')) {
        return 'aov';
    }
    if ($head === 'aov-air-handling' || ($head === 'services' && $slug === 'aov-air-handling')) {
        return 'aov-air-handling';
    }
    if ($head === 'about') {
        return 'page-about';
    }
    if ($head === 'commercial') {
        return 'page-commercial';
    }
    if ($head === 'keywords' && $slug !== '' && $slug !== 'index') {
        $row = icomplyCatalogueRows()[$slug] ?? [];
        $service = icomplyKeywordServiceMap()[$slug] ?? '';
        if ($service === '' && is_array($row)) {
            $service = (string)($row['service'] ?? '');
        }
        if ($service === 'aov') {
            $service = 'aov-air-handling';
        }
        if ($service === '') {
            $service = 'building-maintenance';
        }
        return 'type-keywords-' . $service;
    }
    if ($head === 'jobs' && $slug !== '' && $slug !== 'index') {
        $row = icomplyCatalogueRows()[$slug] ?? [];
        $service = is_array($row) ? (string)($row['service'] ?? '') : '';
        if ($service === '') {
            $fallbackFile = dirname(__DIR__) . '/data/keyword-service-fallback.json';
            $fallback = is_file($fallbackFile) ? json_decode((string)file_get_contents($fallbackFile), true) : [];
            $service = is_array($fallback) ? (string)($fallback[$slug] ?? '') : '';
        }
        if ($service === '' || $service === 'aov') {
            $service = $service === 'aov' ? 'aov-air-handling' : 'building-maintenance';
        }
        return 'type-jobs-' . $service;
    }
    if ($head === 'resources' && $slug !== '' && $slug !== 'index') {
        $service = icomplyResourceServiceMap()[$slug] ?? 'compliance-consultancy';
        return 'type-resources-' . $service;
    }
    return '';
}

function icomplyImageOwner(string $src): string
{
    static $map = null;
    if ($map === null) {
        $file = dirname(__DIR__) . '/data/image-owners.json';
        $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
        $map = is_array($decoded) ? $decoded : [];
    }
    $path = (string)strtok($src, '?');
    $path = (string)preg_replace('#^https?://[^/]+#i', '', $path);
    if (isset($map[$path])) {
        return (string)$map[$path];
    }
    return '';
}
