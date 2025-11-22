/**
 * Pricing Calculator
 *
 * Client-side pricing calculator that uses price-catalog.json as the single source of truth.
 * Matches the server-side PricingService logic.
 */

class PricingCalculator {
  constructor() {
    this.catalog = [];
    this.loaded = false;
  }

  /**
   * Load the pricing catalog from JSON file
   * @returns {Promise<void>}
   */
  async loadCatalog() {
    if (this.loaded) {
      return;
    }

    try {
      const response = await fetch('/price-catalog.json');
      if (!response.ok) {
        throw new Error('Failed to load price catalog');
      }
      this.catalog = await response.json();
      this.loaded = true;
    } catch (error) {
      console.error('Error loading price catalog:', error);
      throw error;
    }
  }

  /**
   * Get all available repair types
   * @returns {string[]} List of repair names
   */
  getRepairTypes() {
    return this.catalog.map(item => item.repair);
  }

  /**
   * Find a repair entry by name
   * @param {string} repairName - Name of the repair
   * @returns {object|null} Repair entry or null if not found
   */
  findRepair(repairName) {
    return this.catalog.find(item =>
      item.repair.toLowerCase() === repairName.toLowerCase()
    ) || null;
  }

  /**
   * Calculate price estimate for a repair
   * @param {string} repairName - Name of the repair
   * @param {object} options - Options: isV8, isOldCar (booleans)
   * @returns {object|null} Estimate data or null if repair not found
   */
  calculateEstimate(repairName, options = {}) {
    const repair = this.findRepair(repairName);

    if (!repair) {
      return null;
    }

    const basePrice = repair.price;
    const baseTime = repair.time;
    const multipliers = repair.multipliers || {};

    // Apply multipliers
    let priceMultiplier = 1.0;
    let timeMultiplier = 1.0;

    if (options.isV8 && multipliers.v8) {
      priceMultiplier *= multipliers.v8;
      timeMultiplier *= multipliers.v8;
    }

    if (options.isOldCar && multipliers.old_car) {
      priceMultiplier *= multipliers.old_car;
      timeMultiplier *= multipliers.old_car;
    }

    const finalPrice = Math.round(basePrice * priceMultiplier);
    const finalTime = Math.round(baseTime * timeMultiplier * 10) / 10;

    return {
      repair: repair.repair,
      basePrice,
      baseTime,
      finalPrice,
      finalTime,
      priceMultiplier,
      timeMultiplier,
      isV8: !!options.isV8,
      isOldCar: !!options.isOldCar,
    };
  }

  /**
   * Calculate estimate from vehicle details
   * @param {string} repairName - Name of the repair
   * @param {object} vehicle - Vehicle data: year, engineSize
   * @returns {object|null} Estimate data or null if repair not found
   */
  calculateEstimateFromVehicle(repairName, vehicle = {}) {
    const year = parseInt(vehicle.year) || 0;
    const engineSize = (vehicle.engineSize || '').toLowerCase();

    // Determine if old car (>15 years)
    let isOldCar = false;
    if (year > 0) {
      const currentYear = new Date().getFullYear();
      const age = currentYear - year;
      isOldCar = age > 15;
    }

    // Determine if V8 engine
    let isV8 = false;
    if (engineSize) {
      // Check for V8 indicators
      const v8Patterns = ['v8', '8 cyl', '8-cyl', '8cyl', 'v-8'];
      isV8 = v8Patterns.some(pattern => engineSize.includes(pattern));

      // Also check if engine displacement suggests V8 (typically 4.6L+)
      const match = engineSize.match(/(\d+\.?\d*)\s*l/i);
      if (match) {
        const displacement = parseFloat(match[1]);
        if (displacement >= 4.6) {
          isV8 = true;
        }
      }
    }

    return this.calculateEstimate(repairName, { isV8, isOldCar });
  }

  /**
   * Format price for display
   * @param {number} price - Price in dollars
   * @returns {string} Formatted price string
   */
  formatPrice(price) {
    return `$${price.toLocaleString()}`;
  }

  /**
   * Format time for display
   * @param {number} time - Time in hours
   * @returns {string} Formatted time string
   */
  formatTime(time) {
    if (time === 1) {
      return '1 hour';
    }
    return `${time} hours`;
  }
}

// Create singleton instance
const pricingCalculator = new PricingCalculator();

// Auto-load catalog when script loads
if (typeof window !== 'undefined') {
  pricingCalculator.loadCatalog().catch(err => {
    console.warn('Failed to auto-load pricing catalog:', err);
  });
}
