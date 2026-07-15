<?php
/**
 * Breadcrumb navigation helper.
 *
 * Usage:
 *   require_once SITE_ROOT . '/includes/breadcrumbs.php';
 *   echo breadcrumbsHtml([
 *       ['label' => 'Home', 'url' => rtrim(SITE_URL, '/') . '/'],
 *       ['label' => 'Services', 'url' => url('/pages/services/index.php')],
 *       ['label' => 'Fire Alarms'], // current page — no url
 *   ]);
 *
 * Optional $options:
 *   - variant: 'dark' (hero on navy) | 'light' (default page)
 *   - class:   extra CSS classes on the <nav>
 */
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config.php';
}

/**
 * Render breadcrumb HTML.
 *
 * @param array<int, array{label:string, url?:string}> $items
 * @param array{variant?:string, class?:string} $options
 */
function breadcrumbsHtml(array $items, array $options = []): string
{
    if ($items === []) {
        return '';
    }

    $variant = $options['variant'] ?? 'dark';
    $extraClass = trim((string)($options['class'] ?? ''));

    if ($variant === 'light') {
        $navClass = 'text-xs text-zinc-500 mb-6 flex flex-wrap gap-2 items-center';
        $linkClass = 'hover:text-[#ff6b00] transition';
        $currentClass = 'text-zinc-800 font-medium';
        $sepClass = 'text-zinc-300';
    } else {
        // Match existing hero breadcrumbs (dark navy backgrounds)
        $navClass = 'text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center';
        $linkClass = 'hover:text-white transition';
        $currentClass = 'text-white/80';
        $sepClass = 'text-white/40';
    }

    if ($extraClass !== '') {
        $navClass .= ' ' . $extraClass;
    }

    $lastIndex = count($items) - 1;
    $html = '<nav class="' . htmlspecialchars($navClass, ENT_QUOTES, 'UTF-8') . '" aria-label="Breadcrumb">';
    $html .= '<ol class="flex flex-wrap gap-2 items-center list-none p-0 m-0" itemscope itemtype="https://schema.org/BreadcrumbList">';

    foreach ($items as $i => $item) {
        $label = (string)($item['label'] ?? '');
        if ($label === '') {
            continue;
        }
        $url = isset($item['url']) ? trim((string)$item['url']) : '';
        $isLast = ($i === $lastIndex);
        $pos = $i + 1;

        if ($i > 0) {
            $html .= '<li class="' . $sepClass . '" aria-hidden="true">/</li>';
        }

        $html .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="inline">';

        if ($url !== '' && !$isLast) {
            $html .= '<a itemprop="item" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" class="' . $linkClass . '">';
            $html .= '<span itemprop="name">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>';
            $html .= '</a>';
        } else {
            $html .= '<span itemprop="name" class="' . $currentClass . '"' . ($isLast ? ' aria-current="page"' : '') . '>';
            $html .= htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $html .= '</span>';
            if ($url !== '') {
                // Still expose URL in schema for current page when provided
                $html .= '<meta itemprop="item" content="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">';
            }
        }

        $html .= '<meta itemprop="position" content="' . $pos . '">';
        $html .= '</li>';
    }

    $html .= '</ol></nav>';
    return $html;
}
