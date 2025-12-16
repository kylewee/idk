<?php
/**
 * Estimate utilities
 * Shared functions for pricing and estimate calculations
 */

/**
 * Load pricing catalog from JSON file
 *
 * @return array|null Pricing catalog or null on error
 */
function load_price_catalog(): ?array
{
    static $catalog = null;

    if ($catalog !== null) {
        return $catalog;
    }

    $catalogPath = __DIR__ . '/../price-catalog.json';
    if (!file_exists($catalogPath)) {
        return null;
    }

    $json = file_get_contents($catalogPath);
    $catalog = json_decode($json, true);

    return is_array($catalog) ? $catalog : null;
}

/**
 * Quick local pricing matrix for common repairs
 *
 * @param array $lead Lead data with repair/service and vehicle info
 * @return array|null Estimate data or null if no match
 */
function get_local_estimate(array $lead): ?array
{
    $repair = '';
    if (!empty($lead['repair'])) {
        $repair = strtolower(trim((string)$lead['repair']));
    } elseif (!empty($lead['service'])) {
        $repair = strtolower(trim((string)$lead['service']));
    }

    if ($repair === '') {
        return null;
    }

    // Load pricing catalog
    $catalog = load_price_catalog();
    if (!$catalog) {
        return null;
    }

    // Normalize repair description
    $slug = preg_replace('/[^a-z0-9]+/', ' ', $repair);
    $slug = trim($slug);

    if ($slug === '') {
        return null;
    }

    // Find matching repair in catalog
    $matchedItem = null;
    foreach ($catalog as $item) {
        $itemName = strtolower($item['repair']);
        $itemSlug = preg_replace('/[^a-z0-9]+/', ' ', $itemName);
        $itemSlug = trim($itemSlug);

        if ($itemSlug === $slug || strpos($slug, $itemSlug) !== false || strpos($itemSlug, $slug) !== false) {
            $matchedItem = $item;
            break;
        }
    }

    if (!$matchedItem) {
        return null;
    }

    $base = (float)$matchedItem['price'];
    $multiplier = 1.0;
    $multipliers = $matchedItem['multipliers'] ?? [];

    // Adjust for engine type
    $engine = isset($lead['engine']) ? strtolower((string)$lead['engine']) : '';
    if ($engine !== '' && strpos($engine, 'v8') !== false && isset($multipliers['v8'])) {
        $multiplier *= (float)$multipliers['v8'];
    }

    // Adjust for vehicle age
    $year = isset($lead['year']) ? (int)$lead['year'] : 0;
    if ($year > 0 && $year < 2000 && isset($multipliers['old_car'])) {
        $multiplier *= (float)$multipliers['old_car'];
    }

    $amount = round($base * $multiplier);

    return [
        'amount' => (float)$amount,
        'source' => 'local_matrix',
        'base_price' => $base,
        'multiplier' => $multiplier,
        'repair_key' => $slug,
        'repair_name' => $matchedItem['repair'],
        'estimated_time' => $matchedItem['time'] ?? null,
    ];
}

/**
 * Extract a numeric estimate from remote API response
 *
 * @param mixed $estimate Remote estimate response
 * @return array|null Normalized estimate data
 */
function extract_remote_estimate($estimate): ?array
{
    if (!is_array($estimate)) {
        return null;
    }

    $candidates = [];

    // Try to find low/high range
    $low = isset($estimate['total_low']) && is_numeric($estimate['total_low'])
        ? (float)$estimate['total_low']
        : null;
    $high = isset($estimate['total_high']) && is_numeric($estimate['total_high'])
        ? (float)$estimate['total_high']
        : null;

    if ($low !== null && $high !== null && $low > 0 && $high > 0) {
        $candidates[] = [
            'amount' => ($low + $high) / 2,
            'source' => 'remote_range_avg',
            'details' => ['low' => $low, 'high' => $high],
        ];
    }

    // Try common estimate field names
    foreach (['total', 'estimate', 'price', 'amount', 'total_mid'] as $key) {
        if (isset($estimate[$key]) && is_numeric($estimate[$key])) {
            $candidates[] = [
                'amount' => (float)$estimate[$key],
                'source' => 'remote_' . $key,
            ];
        }
    }

    // Fallback: find any numeric value
    if (empty($candidates)) {
        foreach ($estimate as $key => $value) {
            if (is_numeric($value)) {
                $candidates[] = [
                    'amount' => (float)$value,
                    'source' => 'remote_' . $key,
                ];
                break;
            }
        }
    }

    if (empty($candidates)) {
        return null;
    }

    // Return first positive amount
    foreach ($candidates as $candidate) {
        if ($candidate['amount'] > 0) {
            $candidate['amount'] = round($candidate['amount']);
            return $candidate;
        }
    }

    // Fallback to first candidate
    $first = $candidates[0];
    $first['amount'] = round($first['amount']);
    return $first;
}

/**
 * Build comprehensive estimate summary from multiple sources
 *
 * @param mixed $remoteEstimate Remote API estimate response
 * @param array $lead Lead data for local estimate
 * @return array Comprehensive estimate summary
 */
function build_estimate_summary($remoteEstimate, array $lead): array
{
    $remote = extract_remote_estimate($remoteEstimate);
    $local = get_local_estimate($lead);

    $summary = [
        'amount' => null,
        'source' => null,
        'candidates' => [],
    ];

    // Prefer remote estimate
    if ($remote) {
        $summary['candidates']['remote'] = $remote;
        if ($remote['amount'] > 0) {
            $summary['amount'] = $remote['amount'];
            $summary['source'] = $remote['source'];
        }
    }

    // Fallback to local estimate
    if ($local) {
        $summary['candidates']['local'] = $local;
        if ($summary['amount'] === null && $local['amount'] > 0) {
            $summary['amount'] = round((float)$local['amount']);
            $summary['source'] = $local['source'];
        }
    }

    if ($summary['amount'] !== null) {
        $summary['amount'] = (float)round($summary['amount']);
    }

    if (is_array($remoteEstimate)) {
        $summary['raw_remote'] = $remoteEstimate;
    }

    return $summary;
}

/**
 * Format price for display
 *
 * @param float $amount Price amount
 * @param bool $includeDecimals Include decimal places
 * @return string Formatted price string
 */
function format_price(float $amount, bool $includeDecimals = false): string
{
    if ($includeDecimals) {
        return '$' . number_format($amount, 2);
    }
    return '$' . number_format($amount, 0);
}
