# MechanicSaintAugustine.com

A static HTML/CSS website served via Caddy with a PHP backend for auto repair quote requests. Features an instant-quote form with SMS opt-in and rolling time-slot picker.

## Features

- **Marketing Homepage**: Professional landing page with service information
- **Instant Quote Form**: User-friendly form with vehicle information and service selection
- **Rolling Time-Slot Picker**: Dynamic appointment scheduling interface
- **SMS Integration**: Twilio-powered SMS quotes for opted-in customers
- **CRM Integration**: Pushes leads to Rukovoditel CRM system
- **Lead Validation**: Server-side validation and sanitization
- **Internal Estimator**: Automatic price estimation based on service and vehicle
- **Comprehensive Logging**: Database logging of all leads and system events

## Setup Instructions

### Prerequisites

- Web server with PHP 8.0+ support
- MySQL/MariaDB database
- Caddy web server
- Twilio account (for SMS functionality)
- Rukovoditel CRM instance (optional)

### Installation

1. **Database Setup**
   ```bash
   mysql -u root -p < api/database_schema.sql
   ```

2. **Configuration**
   - Edit `api/.env.local.php` with your database, Twilio, and CRM credentials
   - Update business information in the configuration file

3. **Web Server Setup**
   - Copy files to your web root directory
   - Ensure PHP-FPM is running
   - Start Caddy with the provided Caddyfile

4. **Development Server**
   ```bash
   # Start PHP built-in server for development
   php -S localhost:8000
   
   # Or use Caddy for local development
   caddy run --config Caddyfile
   ```

### File Structure

```
├── index.html              # Main marketing homepage
├── css/
│   └── styles.css         # Stylesheet
├── js/
│   └── app.js            # Frontend JavaScript
├── quote/
│   └── quote_intake_handler.php  # Backend form processor
├── api/
│   ├── .env.local.php    # Configuration file
│   └── database_schema.sql # Database schema
├── logs/                 # Application logs
├── Caddyfile            # Caddy web server configuration
└── error.html          # Error page
```

## Configuration

Edit `api/.env.local.php` to configure:

- **Database**: MySQL connection settings
- **Twilio SMS**: Account SID, Auth Token, Phone Number/Messaging Service
- **Rukovoditel CRM**: API URL and credentials
- **Business Info**: Contact details and operating hours

## API Endpoints

- `POST /quote/quote_intake_handler.php` - Submit quote request

## Database Tables

- `quote_leads` - Customer quote requests
- `system_logs` - Application and error logs
- `sms_messages` - SMS delivery tracking
- `crm_integrations` - CRM sync status

## Security Features

- Input validation and sanitization
- SQL injection prevention with prepared statements
- XSS protection
- CSRF protection
- Rate limiting on API endpoints
- Security headers via Caddy

## Development

The application follows a simple architecture:
- Static HTML/CSS/JS frontend
- PHP backend for form processing
- MySQL for data persistence
- External API integrations for SMS and CRM

## Support

For support or customization requests, contact the development team.