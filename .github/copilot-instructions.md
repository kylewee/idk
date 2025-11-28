# Mechanic Saint Augustine - AI Coding Instructions

## Project Overview
This is a mobile mechanic service website built as a static HTML site with PHP backends for quotes, voice handling, and CRM integration. The project integrates Twilio for voice/SMS, Rukovoditel CRM for lead management, and includes automated price estimation.

## Architecture & Data Flow

### Core Components
- **Frontend**: Static HTML (`index.html`) with embedded CSS/JS - no build process
- **Admin Interface**: `/admin` redirects to Rukovoditel CRM at `/crm/` - accessible via symlink and Caddyfile redirect
- **Quote System**: Two endpoints for different use cases:
  - `api/quote_intake.php` - Original API endpoint for external integrations
  - `quote/quote_intake_handler.php` - Enhanced handler with SMS support for web form
- **Voice System**: `voice/` directory handling Twilio webhooks for call routing and recording
- **CRM Integration**: Rukovoditel CRM in `crm/` directory with API endpoints
- **Price Catalog**: `price-catalog.json` drives automated estimates with multipliers

### Critical Data Flow
1. **Lead Generation**: Quote forms → `api/quote_intake.php` → CRM API → Rukovoditel
2. **Voice Handling**: Incoming calls → `voice/incoming.php` → TwiML → call forwarding + recording
3. **Price Estimation**: Quote data + `price-catalog.json` → calculated estimates with V8/age multipliers

## Environment Configuration

### Configuration Pattern
- Use `api/.env.local.php` for all secrets (Twilio, CRM credentials)
- Constants pattern: `const TWILIO_ACCOUNT_SID = 'value';`
- Shared config loaded across voice/quote endpoints: `require __DIR__ . '/../api/.env.local.php';`

### Key Configuration Constants
```php
TWILIO_FORWARD_TO          // Personal phone for call forwarding
TWILIO_ACCOUNT_SID/AUTH_TOKEN  // Twilio API credentials  
CRM_API_URL/API_KEY        // Rukovoditel CRM integration
CRM_LEADS_ENTITY_ID        // Target entity for lead creation
CRM_FIELD_MAP              // Maps form fields to CRM field IDs
```

## Development Workflows

### Local Development with Docker
- **Docker Compose Stack**: `docker-compose.yml` defines 4 services (Caddy, PHP-FPM, MariaDB, phpMyAdmin)
- **Quick Start**: `docker compose up -d` to start, access at http://localhost:8080
- **Admin Access**: http://localhost:8080/admin redirects to CRM (http://localhost:8080/crm)
- **Caddy Config**: `Caddyfile` routes PHP requests to `php:9000`, serves static files from `/srv`, includes `/admin` to `/crm` redirects
- **Database**: MariaDB on port 3306 (user: mechanic, password: mechanic)
- **phpMyAdmin**: Available at http://localhost:8081 for database management

### Deployment
- **CI Workflow**: `.github/workflows/ci.yml` targets `crm/js/PapaParse-master` for Node builds/linting
- **Deploy Workflow**: `.github/workflows/deploy.yml` deploys to Netlify (requires NETLIFY_AUTH_TOKEN/SITE_ID secrets)
- **Static Site**: No compilation step for main HTML/PHP files - deployed as-is
- **Repository Setup**: Use `setup_repo.sh` for initial git configuration and remote setup

### Testing Voice Integration
- Twilio webhooks hit `voice/incoming.php` for call routing
- Check `voice/voice.log` for webhook debugging
- Recording callbacks processed in `voice/recording_callback.php`

### CRM Development
- Rukovoditel CRM runs on PHP with custom modules in `crm/modules/`
- API endpoints at `/crm/api/rest.php` for lead creation
- Authentication via username/password in requests

## Code Patterns & Conventions

### PHP Error Handling
```php
// Standard pattern for API endpoints
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}
```

### Logging Pattern
```php
// Consistent logging across voice/quote systems
$logFile = __DIR__ . '/voice.log';  // or quote_intake.log
$entry = ['ts' => date('c'), 'event' => 'description', 'data' => $data];
@file_put_contents($logFile, json_encode($entry) . PHP_EOL, FILE_APPEND);
```

### Price Calculation
- **Catalog**: All repairs defined in `price-catalog.json` with base time/price
- **Multipliers**: Applied for V8 engines (`"v8": 1.2`) and old cars (`"old_car": 1.1`)
- **Mobile Markup**: Automatically applied in quote calculations
- **Estimate API**: `quote_intake.php` calls `http://127.0.0.1:8091/api/estimate` for price calculations
- **Estimate Data**: Includes repair/service, year, make, model, engine, zip code

### SMS Integration
- **Quote Forms**: Support SMS opt-in via checkbox (see `quote/SMS_SETUP.md`)
- **Handler**: `quote/quote_intake_handler.php` processes SMS requests (distinct from `api/quote_intake.php`)
- **Configuration**: Use `TWILIO_SMS_FROM` or `TWILIO_MESSAGING_SERVICE_SID` in `.env.local.php`
- **Compliance**: Include STOP language in all SMS messages per regulations
- **Appointment Slots**: Form generates time slots (skips Sundays), included in SMS body

## Integration Points

### Twilio Integration
- **Voice**: Webhook URLs point to `voice/incoming.php` for call handling
- **SMS**: Outbound messaging via API for quote follow-ups
- **Recording**: Callback handling in `voice/recording_callback.php`

### CRM Integration  
- **Lead Creation**: POST to `/crm/api/rest.php` with entity/field mapping
- **Field Mapping**: Use `CRM_FIELD_MAP` to translate form fields to CRM field IDs
- **Authentication**: Include username/password in API requests

### External Dependencies
- Rukovoditel CRM (GPL-licensed) - full installation in `crm/` directory
- Twilio SDK for voice/SMS functionality
- No frontend build tools - vanilla HTML/CSS/JS only

## File Patterns
- **Backups**: `.backup` and `.bak` suffixes for file versioning (e.g., `index.html.backup-20240924`)
- **Config**: `.env.local.php` for environment-specific settings (never commit - contains secrets)
- **Logs**: `.log` files for debugging (voice.log, quote_intake.log) - JSON-formatted entries
- **Workspaces**: Multiple `.code-workspace` files in `quote/` for different development phases
- **Apache Config**: `backups/.htaccess` for web server configuration if not using Caddy
- **No Build Artifacts**: Project has no root package.json - static HTML/CSS/JS served directly

## Testing & Debugging
- Use log files for webhook debugging (`voice/voice.log`, `api/quote_intake.log`)
- Test Twilio integration via webhook URLs in development
- CRM API responses include detailed error information for debugging lead creation