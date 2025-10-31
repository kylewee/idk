# Mechanic Saint Augustine Website

This is the starting point for your mobile mechanic service website.

- Static HTML site
- Caddy server recommended
- Rukoviditel CRM integration planned
- Call recording and price quote features coming soon

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

## Cloud Mode

Cloud mode enables production-ready logging and monitoring capabilities.

### Logging

The application includes a centralized logging system in the `log/` directory:

- **chat.json**: Tracks all user interactions and conversations
- **logger.php**: PHP utility class for structured logging

### Usage Example

```php
require_once __DIR__ . '/log/logger.php';

$logger = new Logger(__DIR__ . '/log');
$logger->logChat('quote_request', [
    'name' => 'Customer Name',
    'phone' => '555-1234',
    'service' => 'Oil Change'
]);
```

### Log Management

- Logs are automatically rotated to keep file sizes manageable (last 1000 entries)
- Individual log files (*.log, *.txt) are excluded from git
- The log directory structure is preserved via .gitkeep

For more details, see [log/README.md](log/README.md)
