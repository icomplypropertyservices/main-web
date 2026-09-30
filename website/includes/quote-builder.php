<?php
/**
 * Approved services quote builder (Jack 2026-09-30).
 * List prices are all-in. iComply is not VAT registered.
 * Multi-property % applies to certificates and inspections only.
 * Each discounted unit is rounded to the nearest £1, half up, then multiplied.
 * FRA + EICR + gas uses the £650 bundle when that total is lower — never both.
 */
declare(strict_types=1);

if (!defined('SITE_ROOT')) {
    require_once dirname(__DIR__) . '/config.php';
}

function quoteBuilderCatalog(): array
{
    $data = loadJsonData('quote-builder', []);
    if (!isset($data['services'], $data['tiers']) || !is_array($data['services'])) {
        return ['services' => [], 'tiers' => [], 'groups' => [], 'baselineNote' => '', 'vatNote' => '', 'confirmNote' => ''];
    }
    return $data;
}

function quoteBuilderClampProperties(int $properties): int
{
    if ($properties < 1) {
        return 1;
    }
    if ($properties > 999) {
        return 999;
    }
    return $properties;
}

/** @param array<string,mixed> $catalog */
function quoteBuilderTier(array $catalog, int $properties): array
{
    $properties = quoteBuilderClampProperties($properties);
    $fallback = ['id' => 'T0', 'min' => 1, 'max' => 1, 'percent' => 0];
    foreach ($catalog['tiers'] as $tier) {
        if (!is_array($tier)) {
            continue;
        }
        $min = (int)($tier['min'] ?? 1);
        $max = $tier['max'] ?? null;
        $maxN = $max === null ? 999999 : (int)$max;
        if ($properties >= $min && $properties <= $maxN) {
            return $tier;
        }
        $fallback = $tier;
    }
    return $fallback;
}

function quoteBuilderPercentHundredths(float $percent): int
{
    return (int)round($percent * 100);
}

/**
 * Discount a list price in pence and round to the nearest pound, half up.
 * 0% returns the list pence unchanged (so £199.99 stays £199.99).
 */
function quoteBuilderDiscountPence(int $listPence, int $percentHundredths): int
{
    if ($percentHundredths <= 0 || $listPence <= 0) {
        return $listPence;
    }
    $scaled = $listPence * (10000 - $percentHundredths);
    $pounds = intdiv($scaled + 500000, 1000000);
    return $pounds * 100;
}

function quoteBuilderFormatPence(int $pence): string
{
    $neg = $pence < 0;
    $pence = abs($pence);
    $pounds = intdiv($pence, 100);
    $rem = $pence % 100;
    $body = number_format($pounds);
    if ($rem !== 0) {
        $body .= '.' . str_pad((string)$rem, 2, '0', STR_PAD_LEFT);
    }
    return ($neg ? '-' : '') . '£' . $body;
}

function quoteBuilderFormatPercent(float $percent): string
{
    $s = rtrim(rtrim(number_format($percent, 1, '.', ''), '0'), '.');
    return $s . '%';
}

/** @param array<string,mixed> $service */
function quoteBuilderUnitPence(array $service, int $percentHundredths): ?int
{
    if (!empty($service['poa']) || $service['pence'] === null) {
        return null;
    }
    $list = (int)$service['pence'];
    if (empty($service['discount'])) {
        return $list;
    }
    return quoteBuilderDiscountPence($list, $percentHundredths);
}

/**
 * @param array<string,int> $selected service id => quantity (1 for a ticked line)
 * @return array<string,mixed>
 */
function quoteBuilderCalculate(int $properties, array $selected): array
{
    $catalog = quoteBuilderCatalog();
    $properties = quoteBuilderClampProperties($properties);
    $tier = quoteBuilderTier($catalog, $properties);
    $percent = (float)($tier['percent'] ?? 0);
    $hundredths = quoteBuilderPercentHundredths($percent);
    $byId = [];
    foreach ($catalog['services'] as $service) {
        if (is_array($service) && isset($service['id'])) {
            $byId[(string)$service['id']] = $service;
        }
    }

    $active = [];
    foreach ($selected as $id => $qty) {
        $id = (string)$id;
        $qty = (int)$qty;
        if ($qty > 0 && isset($byId[$id])) {
            $active[$id] = min($qty, 99);
        }
    }

    $bundle = $byId['bundle'] ?? null;
    $supersedes = is_array($bundle) && isset($bundle['supersedes']) && is_array($bundle['supersedes'])
        ? array_map('strval', $bundle['supersedes'])
        : [];
    $bundleExplicit = $bundle && !empty($active['bundle']);
    $bundleAuto = false;
    if ($bundleExplicit) {
        foreach ($supersedes as $id) {
            unset($active[$id]);
        }
    } elseif ($bundle && $supersedes) {
        $allOn = true;
        foreach ($supersedes as $id) {
            if (empty($active[$id])) {
                $allOn = false;
                break;
            }
        }
        if ($allOn) {
            $separate = 0;
            foreach ($supersedes as $id) {
                $unit = quoteBuilderUnitPence($byId[$id], $hundredths);
                $separate += (int)$unit * $properties;
            }
            $bundleUnit = (int)quoteBuilderUnitPence($bundle, $hundredths);
            if (($bundleUnit * $properties) <= $separate) {
                foreach ($supersedes as $id) {
                    unset($active[$id]);
                }
                $active['bundle'] = 1;
                $bundleAuto = true;
            }
        }
    }

    $lines = [];
    $total = 0;
    $saving = 0;
    $hasPoa = false;
    foreach ($catalog['services'] as $service) {
        if (!is_array($service)) {
            continue;
        }
        $id = (string)($service['id'] ?? '');
        if ($id === '' || empty($active[$id])) {
            continue;
        }
        $mode = (string)($service['qty'] ?? 'once');
        $qty = ($mode === 'properties') ? $properties : (int)$active[$id];
        if ($qty < 1) {
            continue;
        }
        $poa = !empty($service['poa']) || $service['pence'] === null;
        if ($poa) {
            $hasPoa = true;
            $lines[] = [
                'id' => $id,
                'code' => (string)($service['code'] ?? ''),
                'name' => (string)$service['name'],
                'qty' => $qty,
                'poa' => true,
                'unitPence' => null,
                'linePence' => 0,
                'unitLabel' => 'POA',
                'lineLabel' => 'POA',
            ];
            continue;
        }
        $list = (int)$service['pence'];
        $discounted = !empty($service['discount']) && $hundredths > 0;
        $unit = quoteBuilderUnitPence($service, $discounted ? $hundredths : 0);
        $line = (int)$unit * $qty;
        $total += $line;
        if ($discounted) {
            $saving += ($list * $qty) - $line;
        }
        $lines[] = [
            'id' => $id,
            'code' => (string)($service['code'] ?? ''),
            'name' => (string)$service['name'],
            'qty' => $qty,
            'poa' => false,
            'unitPence' => (int)$unit,
            'linePence' => $line,
            'unitLabel' => quoteBuilderFormatPence((int)$unit),
            'lineLabel' => quoteBuilderFormatPence($line),
        ];
    }

    $priced = array_values(array_filter($lines, static fn(array $line): bool => empty($line['poa'])));
    if ($priced && $hasPoa) {
        $totalLabel = quoteBuilderFormatPence($total) . ' + POA';
    } elseif ($priced) {
        $totalLabel = quoteBuilderFormatPence($total);
    } elseif ($hasPoa) {
        $totalLabel = 'POA';
    } else {
        $totalLabel = quoteBuilderFormatPence(0);
    }

    $tierLabel = (string)$tier['id'] . ' · ';
    if ($percent > 0) {
        $tierLabel .= quoteBuilderFormatPercent($percent) . ' off certificates and inspections';
    } else {
        $tierLabel .= 'list price';
    }

    $announce = 'Total ' . $totalLabel . '. Tier ' . (string)$tier['id'] . ', ';
    $announce .= $percent > 0
        ? quoteBuilderFormatPercent($percent) . ' off certificates and inspections. '
        : 'list price. ';
    $announce .= $properties . ' ' . ($properties === 1 ? 'property.' : 'properties.');
    if ($hasPoa && $priced) {
        $announce .= ' Some items are priced after scope.';
    } elseif ($hasPoa) {
        $announce .= ' Price confirmed after scope.';
    }

    $bundleNote = '';
    if ($bundleAuto) {
        $bundleNote = 'FRA, EICR and gas are priced as the £650 bundle because that is lower than the three certificates separately. The discount applies to the bundle once.';
    } elseif ($bundleExplicit) {
        $bundleNote = 'The bundle includes FRA, EICR and gas on the same property. Those three are not added again.';
    }

    $summaryLines = [];
    $summaryLines[] = 'Properties: ' . $properties;
    $summaryLines[] = 'Tier: ' . $tierLabel;
    $summaryLines[] = 'Indicative total: ' . $totalLabel;
    $summaryLines[] = (string)($catalog['vatNote'] ?? '');
    if ($bundleNote !== '') {
        $summaryLines[] = $bundleNote;
    }
    $summaryLines[] = '';
    if (!$lines) {
        $summaryLines[] = 'No services selected.';
    }
    foreach ($lines as $line) {
        if (!empty($line['poa'])) {
            $summaryLines[] = $line['code'] . ' ' . $line['name'] . ' × ' . $line['qty'] . ' — POA (confirm after scope)';
        } else {
            $summaryLines[] = $line['code'] . ' ' . $line['name'] . ' × ' . $line['qty'] . ' @ ' . $line['unitLabel'] . ' = ' . $line['lineLabel'];
        }
    }
    $summaryLines[] = '';
    $summaryLines[] = (string)($catalog['baselineNote'] ?? '');

    return [
        'properties' => $properties,
        'tierId' => (string)$tier['id'],
        'tierPercent' => $percent,
        'tierLabel' => $tierLabel,
        'lines' => $lines,
        'totalPence' => $total,
        'totalLabel' => $totalLabel,
        'hasPoa' => $hasPoa,
        'savingPence' => $saving,
        'savingLabel' => $saving > 0 ? ('Multi-property discount saves ' . quoteBuilderFormatPence($saving) . '.') : '',
        'bundleAuto' => $bundleAuto,
        'bundleExplicit' => (bool)$bundleExplicit,
        'bundleNote' => $bundleNote,
        'announce' => $announce,
        'summary' => trim(implode("\n", $summaryLines)),
    ];
}

function quoteBuilderNormalisePostcode(string $raw): string
{
    $pc = strtoupper((string)preg_replace('/[^A-Za-z0-9]/', '', $raw));
    if (strlen($pc) >= 5 && strlen($pc) <= 7) {
        return substr($pc, 0, -3) . ' ' . substr($pc, -3);
    }
    return strtoupper(trim($raw));
}

function quoteBuilderValidPostcode(string $postcode): bool
{
    return (bool)preg_match('/^[A-Z]{1,2}\d[A-Z\d]? \d[A-Z]{2}$/', quoteBuilderNormalisePostcode($postcode));
}

/** @param array<string,mixed> $post @return array<string,int> */
function quoteBuilderSelectionFromPost(array $post): array
{
    $catalog = quoteBuilderCatalog();
    $selected = [];
    foreach ($catalog['services'] as $service) {
        if (!is_array($service) || empty($service['id'])) {
            continue;
        }
        $id = (string)$service['id'];
        $mode = (string)($service['qty'] ?? 'once');
        if ($mode === 'hours') {
            $qty = (int)($post['qty_' . $id] ?? 0);
            if ($qty > 0) {
                $selected[$id] = $qty;
            }
            continue;
        }
        if (!isset($post['svc_' . $id]) || (string)$post['svc_' . $id] === '') {
            continue;
        }
        if ($mode === 'each') {
            $qty = (int)($post['qty_' . $id] ?? 1);
            if ($qty > 0) {
                $selected[$id] = $qty;
            }
            continue;
        }
        $selected[$id] = 1;
    }
    return $selected;
}

/**
 * @param array<string,mixed> $post
 * @return array{errors:list<string>,lead:array<string,mixed>,subject:string,body:string}
 */
function quoteBuilderLeadFromPost(array $post): array
{
    $errors = [];
    $name = trim((string)($post['name'] ?? ''));
    $email = trim((string)($post['email'] ?? ''));
    $phone = trim((string)($post['phone'] ?? ''));
    $postcode = quoteBuilderNormalisePostcode((string)($post['postcode'] ?? ''));
    $notes = trim((string)($post['notes'] ?? ''));
    $properties = (int)($post['property_count'] ?? 0);
    if ($name === '' || strlen($name) > 120) {
        $errors[] = 'Please enter your name.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email.';
    }
    if ($phone === '' || strlen($phone) > 40) {
        $errors[] = 'Please enter a phone number.';
    }
    if (!quoteBuilderValidPostcode($postcode)) {
        $errors[] = 'Please enter a UK postcode.';
    }
    if ($properties < 1 || $properties > 999) {
        $errors[] = 'Please enter a property count from 1 to 999.';
    }
    if (strlen($notes) > 5000) {
        $errors[] = 'Please shorten your notes.';
    }
    $selected = quoteBuilderSelectionFromPost($post);
    if (!$selected) {
        $errors[] = 'Please select at least one service.';
    }
    $quote = $selected ? quoteBuilderCalculate($properties, $selected) : quoteBuilderCalculate(max(1, $properties), []);
    if ($errors) {
        return ['errors' => $errors, 'lead' => [], 'subject' => '', 'body' => ''];
    }
    $summary = (string)$quote['summary'];
    if ($notes !== '') {
        $summary .= "\n\nNotes:\n" . $notes;
    }
    $lead = [
        'source' => 'quote-builder',
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'postcode' => $postcode,
        'service' => 'Quote builder',
        'property_count' => $quote['properties'],
        'tier' => $quote['tierLabel'],
        'quote_total' => $quote['totalLabel'],
        'services_summary' => $summary,
        'message' => $summary,
        'notes' => $notes,
        'gclid' => trim((string)($post['gclid'] ?? '')),
        'fbclid' => trim((string)($post['fbclid'] ?? '')),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'timestamp' => date('c'),
    ];
    $body = "New quote builder lead\n"
        . "=======================\n"
        . "Name: {$name}\n"
        . "Email: {$email}\n"
        . "Phone: {$phone}\n"
        . "Postcode: {$postcode}\n\n"
        . $summary . "\n\n"
        . "gclid: " . $lead['gclid'] . "\n"
        . "fbclid: " . $lead['fbclid'] . "\n"
        . "IP: " . $lead['ip'] . "\n"
        . "Time: " . $lead['timestamp'] . "\n";
    return [
        'errors' => [],
        'lead' => $lead,
        'subject' => 'New quote builder lead: ' . $postcode,
        'body' => $body,
    ];
}
