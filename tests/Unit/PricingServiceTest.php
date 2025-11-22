<?php

namespace MechanicStAugustine\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PricingService;

/**
 * Unit tests for PricingService
 */
class PricingServiceTest extends TestCase
{
    private $pricing;

    protected function setUp(): void
    {
        $this->pricing = PricingService::getInstance();
    }

    public function testGetRepairTypes(): void
    {
        $types = $this->pricing->getRepairTypes();

        $this->assertIsArray($types);
        $this->assertNotEmpty($types);
        $this->assertContains('Oil Change', $types);
    }

    public function testFindRepair(): void
    {
        $repair = $this->pricing->findRepair('Oil Change');

        $this->assertIsArray($repair);
        $this->assertEquals('Oil Change', $repair['repair']);
        $this->assertEquals(50, $repair['price']);
    }

    public function testFindRepairCaseInsensitive(): void
    {
        $repair = $this->pricing->findRepair('oil change');

        $this->assertIsArray($repair);
        $this->assertEquals('Oil Change', $repair['repair']);
    }

    public function testFindRepairNotFound(): void
    {
        $repair = $this->pricing->findRepair('Nonexistent Repair');

        $this->assertNull($repair);
    }

    public function testCalculateEstimateBasic(): void
    {
        $estimate = $this->pricing->calculateEstimate('Oil Change');

        $this->assertIsArray($estimate);
        $this->assertEquals(50, $estimate['final_price']);
        $this->assertEquals(0.5, $estimate['final_time']);
        $this->assertEquals(1.0, $estimate['price_multiplier']);
    }

    public function testCalculateEstimateWithV8(): void
    {
        $estimate = $this->pricing->calculateEstimate('Oil Change', ['is_v8' => true]);

        $this->assertIsArray($estimate);
        $this->assertEquals(60, $estimate['final_price']); // 50 * 1.2
        $this->assertTrue($estimate['is_v8']);
    }

    public function testCalculateEstimateWithOldCar(): void
    {
        $estimate = $this->pricing->calculateEstimate('Oil Change', ['is_old_car' => true]);

        $this->assertIsArray($estimate);
        $this->assertEquals(55, $estimate['final_price']); // 50 * 1.1
        $this->assertTrue($estimate['is_old_car']);
    }

    public function testCalculateEstimateFromVehicle(): void
    {
        $estimate = $this->pricing->calculateEstimateFromVehicle('Oil Change', [
            'year' => 2000,
            'engine_size' => '5.0L V8',
        ]);

        $this->assertIsArray($estimate);
        $this->assertTrue($estimate['is_old_car']);
        $this->assertTrue($estimate['is_v8']);
    }
}
