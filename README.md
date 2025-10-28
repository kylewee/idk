# Mechanic Saint Augustine Website

This is the starting point for the mobile mechanic service website.

- Static HTML site
- Caddy server recommended
- Rukovoditel CRM integration
- Twilio voice/SMS integration
- Call recording and automated transcription
- Automated price quotes with SMS notifications

## Documentation

- **[Twilio & Cloudflare Integration Guide](docs/twilio_cloudflare_integration.md)** - Complete setup guide for phone number integration
- **[Setup Checklist](docs/twilio_setup_checklist.md)** - Quick reference for configuration and troubleshooting
- **[Architecture Diagram](docs/architecture_diagram.md)** - Visual representation of system components and data flow
- **[Runbook](docs/runbook.md)** - Production deployment and maintenance procedures
- **[Project Blueprint](docs/project_blueprint.md)** - Overall architecture and roadmap
- **[Requirements](docs/requirements.md)** - Detailed functional requirements
- **[API Outline](docs/api_outline.md)** - Planned API endpoints

## Local dev quickstart

- Start stack: docker compose up -d --build
- App: http://localhost:8080 • phpMyAdmin: http://localhost:8081 (host db / user crm / pass crm)
- Probe: open / (index), /crm/health.php, /voice/incoming.php – expect 200s.

## Smoke tests (optional)

- POST quote: /quote/quote_intake_handler.php with JSON { name, phone, service, year, make, model, engine, text_opt_in:false } – expect success and estimate.amount.
- Example services: oil change → ~50, alternator with V8 → ~420 (includes V8/age multipliers).

## Twilio Integration

This project integrates with Twilio for:
- **Inbound call routing** - Forwards calls to your business number with recording
- **SMS notifications** - Sends quote estimates via text when customers opt in
- **Call transcription** - Optional AI-powered transcription via Twilio Conversational Intelligence

### Quick Setup

1. Copy environment template: `cp api/.env.local.php.example api/.env.local.php`
2. Fill in your Twilio credentials (Account SID, Auth Token, phone numbers)
3. Configure Twilio phone number webhooks to point to:
   - Voice: `https://mechanicstaugustine.com/voice/incoming.php`
   - SMS: `https://mechanicstaugustine.com/api/sms_incoming.php` (if needed)
4. Test by calling your Twilio number

**See [Twilio & Cloudflare Integration Guide](docs/twilio_cloudflare_integration.md) for detailed setup instructions.**

## Production Deployment

The site runs behind a Cloudflare tunnel for secure HTTPS access:
- Domain: mechanicstaugustine.com
- Tunnel: cloudflared systemd service
- Web server: Caddy (localhost:8080)

See [Runbook](docs/runbook.md) for deployment procedures and [Setup Checklist](docs/twilio_setup_checklist.md) for configuration verification.
