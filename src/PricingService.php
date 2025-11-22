<?php

/**
 * Pricing Service
 *
 * Centralized pricing calculator that uses price-catalog.json as the single source of truth.
 * Eliminates code duplication across PHP and JavaScript implementations.
 */

class PricingService
{
    private $catalog = [];
    private static $instance = null;

    private function __construct()
    {
        $this->loadCatalog();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadCatalog(): void
    {
        $catalogPath = __DIR__ . '/../price-catalog.json';

        if (!file_exists($catalogPath)) {
            throw new RuntimeException('Price catalog not found at: ' . $catalogPath);
        }

        $json = file_get_contents($catalogPath);
        $this->catalog = json_decode($json, true);

        if (!is_array($this->catalog)) {
            throw new RuntimeException('Invalid price catalog format');
        }
    }

    /**
     * Get all available repair types
     *
     * @return array List of repair types
     */
    public function getRepairTypes(): array
    {
        return array_column($this->catalog, 'repair');
    }

    /**
     * Get full catalog data
     *
     * @return array Complete catalog
     */
    public function getCatalog(): array
    {
        return $this->catalog;
    }

    /**
     * Find a repair entry by name
     *
     * @param string $repairName Name of the repair
     * @return array|null Repair entry or null if not found
     */
    public function findRepair(string $repairName): ?array
    {
        foreach ($this->catalog as $item) {
            if (strcasecmp($item['repair'], $repairName) === 0) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Calculate price estimate for a repair
     *
     * @param string $repairName Name of the repair
     * @param array $options Options: 'is_v8', 'is_old_car' (booleans)
     * @return array|null Estimate data or null if repair not found
     */
    public function calculateEstimate(string $repairName, array $options = []): ?array
    {
        $repair = $this->findRepair($repairName);

        if (!$repair) {
            return null;
        }

        $basePrice = $repair['price'];
        $baseTime = $repair['time'];
        $multipliers = $repair['multipliers'] ?? [];

        // Apply multipliers
        $priceMultiplier = 1.0;
        $timeMultiplier = 1.0;

        if (!empty($options['is_v8']) && isset($multipliers['v8'])) {
            $priceMultiplier *= $multipliers['v8'];
            $timeMultiplier *= $multipliers['v8'];
        }

        if (!empty($options['is_old_car']) && isset($multipliers['old_car'])) {
            $priceMultiplier *= $multipliers['old_car'];
            $timeMultiplier *= $multipliers['old_car'];
        }

        $finalPrice = round($basePrice * $priceMultiplier);
        $finalTime = round($baseTime * $timeMultiplier, 1);

        return [
            'repair' => $repair['repair'],
            'base_price' => $basePrice,
            'base_time' => $baseTime,
            'final_price' => $finalPrice,
            'final_time' => $finalTime,
            'price_multiplier' => $priceMultiplier,
            'time_multiplier' => $timeMultiplier,
            'is_v8' => !empty($options['is_v8']),
            'is_old_car' => !empty($options['is_old_car']),
        ];
    }

    /**
     * Calculate estimate from vehicle details
     *
     * @param string $repairName Name of the repair
     * @param array $vehicle Vehicle data: 'year', 'engine_size'
     * @return array|null Estimate data or null if repair not found
     */
    public function calculateEstimateFromVehicle(string $repairName, array $vehicle = []): ?array
    {
        $year = (int)($vehicle['year'] ?? 0);
        $engineSize = strtolower($vehicle['engine_size'] ?? '');

        $isOldCar = false;
        if ($year > 0) {
            $currentYear = (int)date('Y');
            $age = $currentYear - $year;
            $isOldCar = ($age > 15);
        }

        $isV8 = false;
        if (!empty($engineSize)) {
            // Check for V8 indicators
            $v8Patterns = ['v8', '8 cyl', '8-cyl', '8cyl', 'v-8'];
            foreach ($v8Patterns as $pattern) {
                if (stripos($engineSize, $pattern) !== false) {
                    $isV8 = true;
                    break;
                }
            }

            // Also check if engine displacement suggests V8 (typically 4.6L+)
            if (preg_match('/(\d+\.?\d*)\s*l/i', $engineSize, $matches)) {
                $displacement = (float)$matches[1];
                if ($displacement >= 4.6) {
                    $isV8 = true;
                }
            }
        }

        return $this->calculateEstimate($repairName, [
            'is_v8' => $isV8,
            'is_old_car' => $isOldCar,
        ]);
    }

    /**
     * Get pricing data for API responses
     *
     * @param string $repairName Name of the repair
     * @param array $vehicle Vehicle data
     * @return array API response data
     */
    public function getEstimateResponse(string $repairName, array $vehicle = []): array
    {
        $estimate = $this->calculateEstimateFromVehicle($repairName, $vehicle);

        if (!$estimate) {
            return [
                'success' => false,
                'error' => 'Repair type not found in catalog',
                'repair' => $repairName,
            ];
        }

        return [
            'success' => true,
            'repair' => $estimate['repair'],
            'price' => $estimate['final_price'],
            'time' => $estimate['final_time'],
            'base_price' => $estimate['base_price'],
            'multipliers_applied' => [
                'v8' => $estimate['is_v8'],
                'old_car' => $estimate['is_old_car'],
            ],
        ];
    }
}

// Helper function for easy access
function pricing(): PricingService
{
    return PricingService::getInstance();
}
