# Mechanic Saint Augustine

A comprehensive mobile mechanic service website with integrated CRM, automated quote management, SMS notifications, and voice call handling powered by AI.

## Features

- **Static HTML Website**: Fast, responsive front-end for customer engagement
- **Quote Management**: Automated quote intake with price estimation
- **SMS Integration**: Twilio-powered SMS notifications for quotes and updates
- **Voice Call Handling**: Automated call recording, transcription, and lead creation
- **CRM Integration**: Rukovoditel CRM for comprehensive lead and customer management
- **AI-Powered**: OpenAI integration for intelligent call transcription and analysis
- **Docker-Ready**: Complete containerized environment for easy deployment

## Project Structure

```
/
├── api/                      # API endpoints
│   └── .env.local.php       # Environment configuration (DO NOT COMMIT)
├── crm/                      # Rukovoditel CRM system
├── lib/                      # Shared PHP utilities
│   ├── common_utils.php     # General helper functions
│   ├── estimate_utils.php   # Pricing and estimate calculations
│   ├── phone_utils.php      # Phone number handling
│   └── twilio_utils.php     # Twilio SMS/voice integration
├── quote/                    # Quote intake system
│   ├── index.html           # Quote request form
│   └── quote_intake_handler.php  # Quote processing endpoint
├── voice/                    # Voice call handling
│   ├── incoming.php         # Twilio webhook for incoming calls
│   ├── recording_callback.php    # Recording processing
│   └── ci_callback.php      # Conversation Intelligence callback
├── backups/                  # Backup directory
├── index.html               # Main landing page
├── price-catalog.json       # Single source of truth for pricing
├── docker-compose.yml       # Docker services configuration
├── Dockerfile               # PHP container definition
├── Caddyfile               # Web server configuration
└── .env.example            # Environment variables template

```

## Technology Stack

### Backend
- **PHP 8.2**: Server-side processing
- **MariaDB 10.11**: Database
- **Rukovoditel CRM 3.6.2**: Customer relationship management

### Frontend
- **HTML/CSS/JavaScript**: Vanilla web technologies
- **No frameworks**: Fast, lightweight delivery

### Infrastructure
- **Caddy 2**: Modern web server with automatic HTTPS
- **Docker & Docker Compose**: Containerization
- **phpMyAdmin**: Database administration

### Third-Party Services
- **Twilio**: SMS messaging and voice calls
- **OpenAI GPT-3.5**: Call transcription and analysis

## Setup Instructions

### Prerequisites

- Docker and Docker Compose installed
- Twilio account (for SMS/voice features)
- OpenAI API key (for AI features)
- Domain name configured (for production)

### Local Development Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd idk
   ```

2. **Configure environment variables**
   ```bash
   # Copy the example environment file
   cp .env.example api/.env.local.php

   # Edit with your credentials
   nano api/.env.local.php
   ```

3. **Start Docker services**
   ```bash
   # Using npm scripts
   npm run dev

   # Or directly with docker-compose
   docker-compose up -d
   ```

4. **Access the services**
   - Website: http://localhost:8080
   - phpMyAdmin: http://localhost:8081
   - CRM: http://localhost:8080/crm

### Environment Configuration

Required environment variables (see `.env.example` for details):

- **Twilio Configuration**
  - `TWILIO_ACCOUNT_SID`: Your Twilio Account SID
  - `TWILIO_AUTH_TOKEN`: Your Twilio Auth Token
  - `TWILIO_SMS_FROM`: Twilio phone number for SMS
  - `TWILIO_FORWARD_TO`: Phone number for call forwarding

- **CRM Configuration**
  - `CRM_API_URL`: CRM API endpoint
  - `CRM_API_KEY`: CRM API key
  - `CRM_USERNAME`: CRM username
  - `CRM_PASSWORD`: CRM password
  - `CRM_LEADS_ENTITY_ID`: Entity ID for leads

- **OpenAI Configuration**
  - `OPENAI_API_KEY`: OpenAI API key for transcription

### Database Setup

The database is automatically initialized when you start the Docker services. Default credentials:

- **User**: mechanic
- **Password**: mechanic (change in production!)
- **Database**: mechanic
- **Port**: 3306

## Usage

### Quote System

Customers can request quotes through:
1. Web form at `/quote/index.html`
2. SMS to your Twilio number
3. Voice calls (automatically processed)

Quotes are automatically:
- Estimated using the pricing catalog
- Sent via SMS (if customer opts in)
- Created as leads in the CRM

### Voice Call Handling

Incoming calls are:
1. Answered and recorded by Twilio
2. Transcribed using native Twilio or OpenAI
3. Analyzed to extract customer information
4. Automatically created as CRM leads

### Pricing Management

Edit `price-catalog.json` to update pricing:

```json
{
  "repair": "Oil Change",
  "time": 0.5,
  "price": 50,
  "multipliers": {
    "v8": 1.2,
    "old_car": 1.1
  }
}
```

Multipliers are automatically applied based on:
- Engine type (V8 engines)
- Vehicle age (pre-2000 vehicles)

## Development

### Available npm Scripts

```bash
npm run dev              # Start Docker services
npm run docker:logs      # View Docker logs
npm run docker:down      # Stop Docker services
npm run docker:restart   # Restart services
npm run lint:js          # Lint JavaScript files
npm run lint:php         # Check PHP syntax
npm run test             # Run all linters and validators
```

### Shared Libraries

The project includes reusable PHP libraries in `/lib/`:

- **common_utils.php**: String sanitization, name splitting, logging
- **estimate_utils.php**: Price calculations and estimation logic
- **phone_utils.php**: Phone number normalization to E.164 format
- **twilio_utils.php**: SMS sending and Twilio API integration

Include them in your code:
```php
require_once __DIR__ . '/lib/common_utils.php';
require_once __DIR__ . '/lib/estimate_utils.php';
```

## API Documentation

See [API.md](API.md) for detailed endpoint documentation.

### Quick Reference

- `POST /quote/quote_intake_handler.php` - Submit quote request
- `POST /voice/incoming.php` - Twilio voice webhook
- `POST /voice/recording_callback.php` - Recording processing webhook

## Security

- **Never commit** `api/.env.local.php` - contains sensitive credentials
- **Log files** are excluded from web access via `.htaccess`
- **Backup files** are automatically ignored by git
- **HTTPS** is automatically handled by Caddy in production

## Deployment

### Production Checklist

- [ ] Update all credentials in `api/.env.local.php`
- [ ] Change database password in `docker-compose.yml`
- [ ] Configure domain in `Caddyfile`
- [ ] Set up Twilio webhooks to your domain
- [ ] Enable automatic backups for database
- [ ] Monitor logs for errors
- [ ] Test SMS and voice call flows

### Twilio Webhook Configuration

Configure these webhooks in your Twilio console:

1. **Voice incoming calls**: `https://yourdomain.com/voice/incoming.php`
2. **Recording status callback**: `https://yourdomain.com/voice/recording_callback.php`
3. **SMS incoming**: `https://yourdomain.com/quote/quote_intake_handler.php` (if handling SMS quotes)

## Troubleshooting

### Common Issues

**Docker containers won't start**
```bash
# Check if ports are already in use
docker-compose down
docker-compose up -d
```

**CRM not loading**
```bash
# Check database connection in CRM config
docker-compose logs mariadb
```

**SMS not sending**
- Verify Twilio credentials in `api/.env.local.php`
- Check Twilio console for errors
- Ensure phone numbers are E.164 formatted

**Logs not writing**
```bash
# Check permissions
chmod 755 api voice
chmod 644 api/*.log voice/*.log
```

## License

Proprietary - All rights reserved

## Support

For issues or questions, please contact the development team or create an issue in the repository.

## Credits

- CRM: [Rukovoditel](https://www.rukovoditel.net/) (GPLv3)
- Web Server: [Caddy](https://caddyserver.com/)
- SMS/Voice: [Twilio](https://www.twilio.com/)
- AI: [OpenAI](https://openai.com/)
