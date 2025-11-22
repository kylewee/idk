# Mechanic Saint Augustine

A comprehensive mobile mechanic service platform with integrated CRM, voice call handling, and automated quote intake system.

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Architecture](#architecture)
- [Technology Stack](#technology-stack)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [API Documentation](#api-documentation)
- [Development](#development)
- [Deployment](#deployment)
- [Contributing](#contributing)
- [License](#license)

## Overview

This platform provides a complete solution for mobile mechanic businesses, featuring:

- **Marketing Website**: Static HTML landing page for customer engagement
- **Quote Intake System**: Web form for customers to request repair quotes
- **Voice Call Integration**: Twilio-powered call recording and transcription
- **CRM Integration**: Rukovoditel CRM for lead management
- **AI-Powered Data Extraction**: OpenAI GPT for extracting customer information from calls and forms
- **Multi-Channel Communication**: SMS notifications and email alerts

## Features

### Customer-Facing

- 📱 Mobile-responsive landing page
- 📝 Online quote request form with vehicle details
- ☎️ Phone call handling with automatic recording
- 💬 SMS quote delivery
- 🚗 Comprehensive vehicle information capture

### Business Operations

- 📊 Automated lead creation in CRM
- 🎙️ Call recording and transcription
- 🤖 AI-powered customer data extraction
- 📧 Email notifications for new leads
- 🔄 Duplicate lead detection and merging
- 💾 Automatic data backup and logging

### Technical Features

- 🔐 Secure credential management with environment variables
- 🏗️ Service-oriented architecture
- 📦 Docker containerization
- 🔄 API fallback mechanisms
- 📝 Comprehensive logging
- ✅ Input validation and sanitization

## Architecture

The project follows a service-oriented architecture with clear separation of concerns:

```
├── src/
│   ├── Config.php                      # Centralized configuration management
│   └── Services/
│       ├── CrmService.php              # CRM integration
│       ├── TwilioService.php           # Twilio voice & SMS
│       ├── OpenAiService.php           # AI transcription & extraction
│       └── CustomerDataExtractor.php   # Data extraction logic
├── api/                                # API endpoints
├── quote/                              # Quote intake system
├── voice/                              # Voice call handling
└── crm/                                # Rukovoditel CRM installation
```

For detailed architecture documentation, see [ARCHITECTURE.md](./docs/ARCHITECTURE.md).

## Technology Stack

### Backend
- **PHP 8.2** (FPM) - Primary backend language
- **MariaDB 10.11** - Database server
- **Rukovoditel CRM 3.6.2** - Lead management system

### Frontend
- **HTML5** - Markup
- **CSS3** - Styling
- **Vanilla JavaScript** - Client-side interactions
- **jQuery 3.7.0** - Legacy support

### External Services
- **Twilio** - Voice, SMS, and call recording
- **OpenAI** - GPT-3.5-turbo & Whisper for AI processing
- **Caddy 2** - Web server with automatic HTTPS

### DevOps
- **Docker** - Containerization
- **docker-compose** - Orchestration
- **GitHub Actions** - CI/CD

## Installation

### Prerequisites

- Docker and Docker Compose
- Git
- Domain name (for production)

### Quick Start with Docker

1. **Clone the repository**
   ```bash
   git clone https://github.com/kylewee/idk.git
   cd idk
   ```

2. **Configure environment variables**
   ```bash
   cp .env.example .env
   nano .env  # Edit with your actual credentials
   ```

3. **Start services**
   ```bash
   docker compose up -d
   ```

4. **Access the application**
   - Website: http://localhost:8080
   - phpMyAdmin: http://localhost:8081
   - CRM: http://localhost:8080/crm

### Manual Installation

See [DEPLOYMENT.md](./docs/DEPLOYMENT.md) for detailed manual installation instructions.

## Configuration

### Environment Variables

All configuration is managed through environment variables in the `.env` file:

```bash
# Twilio Configuration
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_SMS_FROM=+1234567890
TWILIO_FORWARD_TO=+1234567890

# OpenAI Configuration
OPENAI_API_KEY=your_api_key

# CRM Configuration
CRM_API_URL=https://yourdomain.com/crm/api/rest.php
CRM_API_KEY=your_crm_api_key
CRM_USERNAME=your_username
CRM_PASSWORD=your_password
CRM_LEADS_ENTITY_ID=26

# Database Configuration
DB_SERVER=localhost
DB_SERVER_USERNAME=your_db_user
DB_SERVER_PASSWORD=your_db_password
DB_DATABASE=rukovoditel
```

### CRM Field Mapping

Field mapping is configured via the `CRM_FIELD_MAP` environment variable in JSON format:

```json
{
  "first_name": 219,
  "last_name": 220,
  "phone": 227,
  "address": 234,
  "year": 231,
  "make": 232,
  "model": 233,
  "notes": 230
}
```

Each number corresponds to a field ID in your Rukovoditel CRM installation.

## Usage

### Quote Request Flow

1. Customer visits website and fills out quote form
2. Form validates vehicle information and contact details
3. System estimates price from local pricing matrix
4. Lead is created in CRM via REST API
5. Customer receives SMS with quote estimate
6. Business owner receives email notification

### Voice Call Flow

1. Customer calls Twilio number
2. Call is forwarded to business phone
3. Call is recorded automatically
4. After call ends, recording is transcribed
5. AI extracts customer information from transcript
6. Lead is created in CRM with call details
7. Business owner receives email notification

## API Documentation

### Quote Intake API

**Endpoint:** `POST /quote/quote_intake_handler.php`

**Request Body:**
```json
{
  "name": "John Smith",
  "phone": "9045551234",
  "year": "2020",
  "make": "Honda",
  "model": "Civic",
  "notes": "Check engine light is on"
}
```

### Voice Webhooks

**Recording Callback:** `POST /voice/recording_callback.php`

Handles Twilio recording callbacks and transcription.

For complete API documentation, see [API.md](./docs/API.md).

## Development

### Project Structure

```
.
├── index.html                  # Main landing page
├── price-catalog.json          # Repair pricing matrix
├── .env                        # Environment configuration (gitignored)
├── .env.example                # Environment template
├── docker-compose.yml          # Docker orchestration
├── Dockerfile                  # PHP container definition
├── Caddyfile                   # Web server configuration
│
├── src/                        # Refactored service layer
│   ├── Config.php
│   └── Services/
│       ├── CrmService.php
│       ├── TwilioService.php
│       ├── OpenAiService.php
│       └── CustomerDataExtractor.php
│
├── api/                        # API endpoints
│   ├── rest.php
│   └── ipn.php
│
├── quote/                      # Quote intake system
│   ├── index.html
│   └── quote_intake_handler.php
│
├── voice/                      # Voice call handling
│   ├── incoming.php
│   ├── recording_callback.php
│   └── ci_callback.php
│
├── crm/                        # Rukovoditel CRM
│   ├── api/
│   ├── config/
│   └── ...
│
└── docs/                       # Documentation
    ├── ARCHITECTURE.md
    ├── API.md
    └── DEPLOYMENT.md
```

### Running Tests

```bash
# Coming soon
```

### Code Style

- PHP: PSR-12 coding standard
- JavaScript: ESLint (ES2021)
- Use Prettier for formatting

## Deployment

### Production Deployment

1. **Set up server** (Ubuntu 20.04+ recommended)
2. **Install dependencies** (PHP 8.2, MariaDB, Caddy)
3. **Configure environment variables**
4. **Set up SSL certificates** (automatic with Caddy)
5. **Configure Twilio webhooks**

See [DEPLOYMENT.md](./docs/DEPLOYMENT.md) for complete deployment guide.

### Docker Deployment

```bash
# Production with automatic SSL
docker compose -f docker-compose.prod.yml up -d
```

## Security Considerations

- ✅ Environment variables for all secrets
- ✅ Prepared statements for SQL queries
- ✅ Input validation and sanitization
- ✅ HTTPS enforced in production
- ✅ Duplicate lead detection
- ⚠️ Rotate API keys regularly
- ⚠️ Keep CRM and dependencies updated

## Troubleshooting

### Common Issues

**Leads not appearing in CRM**
- Check CRM API credentials in `.env`
- Verify CRM_LEADS_ENTITY_ID is correct
- Check logs in `voice/voice.log` and `quote/quote_intake.log`

**Twilio calls not recording**
- Verify webhook URLs are publicly accessible
- Check TWILIO_TRANSCRIBE_ENABLED is set to `true`
- Review Twilio debugger console

**AI extraction not working**
- Verify OPENAI_API_KEY is valid
- Check OpenAI account has credits
- Review error logs for API errors

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is proprietary software. All rights reserved.

## Support

For issues or questions:
- GitHub Issues: https://github.com/kylewee/idk/issues
- Email: support@mechanicstaugustine.com

## Acknowledgments

- Rukovoditel CRM - GPL-licensed open-source CRM
- Twilio - Voice and SMS infrastructure
- OpenAI - AI-powered transcription and extraction
- Caddy - Modern web server with automatic HTTPS
