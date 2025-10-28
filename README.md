# Mechanic Saint Augustine Website

This is the mobile mechanic service website with integrated phone system and CRM.

## Features

- Static HTML site with PHP backends
- Twilio phone system integration (call forwarding, recording, SMS)
- Rukovoditel CRM integration for lead management
- Automated price estimation for repair services
- Quote intake with SMS notifications

## Quick Start

### First-Time Setup

1. **Configure credentials**: Copy and edit the configuration file
   ```bash
   cp api/.env.local.php.example api/.env.local.php
   # Edit api/.env.local.php with your Twilio and CRM credentials
   ```

2. **Run diagnostics**: Check your configuration
   ```bash
   ./scripts/troubleshoot.sh
   ```

3. **See setup guide**: For detailed instructions
   ```bash
   cat docs/SETUP_GUIDE.md
   # Or visit: docs/SETUP_GUIDE.md
   ```

### Local Development

- Start stack: `docker compose up -d --build`
- App: http://localhost:8080
- phpMyAdmin: http://localhost:8081 (host: db, user: crm, pass: crm)
- Probe: open / (index), /crm/health.php, /voice/incoming.php – expect 200s

### Testing

Run the troubleshooting script to check configuration:
```bash
./scripts/troubleshoot.sh
```

Test quote submission (optional):
```bash
curl -X POST http://localhost:8080/api/quote_intake.php \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","phone":"+19045551234","repair":"Oil Change","year":"2020","make":"Toyota","model":"Camry","engine":"2.5"}'
```

Expected: JSON response with success status and estimate amount.
Example services: oil change → ~$50-150, alternator with V8 → ~$420 (includes V8/age multipliers).

## Documentation

- **[Setup Guide](docs/SETUP_GUIDE.md)** - Complete configuration and troubleshooting guide
- **[Runbook](docs/runbook.md)** - Live site operations and deployment
- **[Project Blueprint](docs/project_blueprint.md)** - Long-term architecture vision
- **[API Outline](docs/api_outline.md)** - API documentation

## Troubleshooting

If you experience issues:

1. Run the diagnostics script: `./scripts/troubleshoot.sh`
2. Check the logs:
   - Quote intake: `tail -f api/quote_intake.log`
   - Voice system: `tail -f voice/voice.log`
3. Review the [Setup Guide](docs/SETUP_GUIDE.md)
4. Check the [Runbook](docs/runbook.md) for live site operations

## Common Issues

- **CRM authentication errors**: Set `CRM_USERNAME` and `CRM_PASSWORD` in `api/.env.local.php`
- **Phone calls not forwarding**: Configure Twilio webhooks and set `TWILIO_FORWARD_TO`
- **SMS not sending**: Set `TWILIO_ACCOUNT_SID`, `TWILIO_AUTH_TOKEN`, and `TWILIO_SMS_FROM`

See [docs/SETUP_GUIDE.md](docs/SETUP_GUIDE.md) for detailed solutions.
