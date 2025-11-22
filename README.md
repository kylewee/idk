# Mechanic Saint Augustine Website

A modern mobile mechanic service website with automated quote intake, voice call handling with AI transcription, and Rukovoditel CRM integration.

## Features

- 📞 **Voice Call Handling**: Twilio integration with AI-powered transcript analysis
- 💰 **Automated Pricing**: Dynamic quote generation based on vehicle details
- 📊 **CRM Integration**: Automatic lead creation in Rukovoditel CRM
- 🔒 **Secure Configuration**: Environment-based secrets management
- 🧪 **Test Coverage**: PHPUnit testing infrastructure
- 🏗️ **Modern Architecture**: PSR-4 autoloading, service-based design

## Docker Setup

This project includes Docker configuration for easy development and deployment.

### Services

- **Caddy**: Web server (ports 8080:80, 8443:443)
- **PHP**: PHP-FPM 8.2 with required extensions
- **MariaDB**: Database server (port 3306)
- **phpMyAdmin**: Database management UI (port 8081)

### Quick Start

```bash
# Start all services
docker compose up -d

# View logs
docker compose logs -f

# Stop all services
docker compose down

# Stop and remove volumes
docker compose down -v
```

### Accessing Services

- Website: http://localhost:8080
- phpMyAdmin: http://localhost:8081
- Database: localhost:3306 (user: mechanic, password: mechanic)

## Configuration

### First Time Setup

1. **Copy environment file:**
   ```bash
   cp .env.example .env
   ```

2. **Edit `.env` with your credentials:**
   ```bash
   nano .env
   ```

   Required settings:
   - Twilio credentials (Account SID, Auth Token, phone numbers)
   - CRM credentials (API URL, API key, username, password)
   - OpenAI API key (for AI transcript analysis)
   - Database credentials

3. **Install PHP dependencies:**
   ```bash
   composer install
   ```

### Environment Variables

See `.env.example` for all available configuration options.

**Security Note:** Never commit `.env` to version control. It contains sensitive credentials.

## Project Structure

```
├── api/                      # API endpoints
│   ├── estimate.php          # Pricing estimate API
│   └── quote_intake.php      # Quote submission handler
├── config/
│   └── config.php            # Centralized configuration loader
├── crm/                      # Rukovoditel CRM system
├── js/
│   └── pricing-calculator.js # Frontend pricing logic
├── src/                      # PHP source code
│   ├── autoload.php          # PSR-4 autoloader
│   ├── PricingService.php    # Pricing calculator
│   └── Voice/                # Voice call handling
│       ├── TranscriptAnalyzer.php
│       ├── CrmLeadService.php
│       └── CallLogger.php
├── tests/                    # PHPUnit tests
│   ├── Unit/
│   └── bootstrap.php
├── voice/                    # Twilio voice integration
│   ├── incoming.php          # Incoming call handler
│   └── recording_callback_refactored.php  # Recording processor
├── index.html                # Main landing page
├── price-catalog.json        # Pricing data (single source of truth)
├── composer.json             # PHP dependencies
└── phpunit.xml               # Test configuration
```

## API Endpoints

### POST /api/quote_intake.php
Submit a service quote request.

**Request:**
```json
{
  "name": "John Doe",
  "phone": "904-555-1234",
  "repair": "Oil Change",
  "year": 2015,
  "make": "Honda",
  "model": "Civic",
  "engine": "4-cylinder"
}
```

### POST /api/estimate.php
Get a price estimate for a repair.

**Request:**
```json
{
  "repair": "Oil Change",
  "year": 2015,
  "engine": "V8"
}
```

**Response:**
```json
{
  "success": true,
  "repair": "Oil Change",
  "price": 60,
  "time": 0.6,
  "multipliers_applied": {
    "v8": true,
    "old_car": false
  }
}
```

## Development

### Running Tests

```bash
# Run all tests
composer test

# Run specific test suite
./vendor/bin/phpunit tests/Unit

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage/
```

### Code Quality

```bash
# Check code style
composer cs-check

# Fix code style
composer cs-fix
```

## Refactoring

This project underwent a comprehensive refactoring in November 2025. See [REFACTORING.md](REFACTORING.md) for details.

**Key improvements:**
- Moved secrets to environment variables
- Removed 146MB of duplicate files
- Consolidated pricing logic
- Refactored 1,671-line god object into focused services
- Added PSR-4 autoloading and testing infrastructure

## Twilio Integration

### Voice Call Flow

1. Customer calls Twilio number
2. Twilio forwards to `voice/incoming.php`
3. Call is recorded and transcribed
4. Callback sent to `voice/recording_callback_refactored.php`
5. AI extracts customer information from transcript
6. Lead created in CRM automatically
7. Recording stored in database

### Configuration

Set your Twilio webhooks to:
- **Voice URL:** `https://yourdomain.com/voice/incoming.php`
- **Recording Callback:** `https://yourdomain.com/voice/recording_callback_refactored.php`

## Security

- All sensitive credentials in `.env` file (not committed)
- Database uses prepared statements (SQL injection protection)
- Access token protection for recordings page
- Input validation on all API endpoints

## License

Proprietary - Mechanic St. Augustine
