# Quick Setup Guide - Site is Working!

## ✅ What's Working

Your Mechanic Saint Augustine website is now fully operational with Docker! Here's what's been set up:

### Services Running
- **Main Website**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081 (Database management)
- **PHP-FPM**: Processing all PHP requests
- **MariaDB**: Database backend
- **Caddy Web Server**: Serving files and routing PHP requests

### Features Working
- ✅ Homepage loads correctly
- ✅ Quote form with instant price estimates
- ✅ Client-side price calculation (works even without backend setup)
- ✅ PHP backend for quote processing
- ✅ Voice webhook endpoints (for Twilio integration)

## 🚀 Getting Started

### Start the Site
```bash
# Start all Docker services
docker compose -f docker-compose.yml up -d

# Check that everything is running
docker compose -f docker-compose.yml ps

# View logs if needed
docker compose -f docker-compose.yml logs -f
```

### Stop the Site
```bash
docker compose -f docker-compose.yml down
```

### Access the Site
- Open your browser to: **http://localhost:8080**
- Try the quote form - it works immediately with client-side estimates!

## 📝 What Was Fixed

1. **Created `docker/caddy/Caddyfile`** - This was missing and preventing the Docker setup from working
   - Configured PHP-FPM routing for `.php` files
   - Set up static file serving
   - Added security headers

2. **Created `api/.env.local.php`** - Environment configuration for optional features
   - Contains placeholder values for Twilio (voice/SMS)
   - Contains placeholder values for CRM integration
   - This file is gitignored for security

3. **Fixed `.gitignore`** - Updated to allow `docker/caddy/` directory while still blocking the Caddy binary

## 🔧 Optional Configuration

### To Enable SMS Quotes (Optional)
If you want customers to receive quote estimates via text message:

1. Edit `api/.env.local.php`
2. Fill in your Twilio credentials:
   ```php
   const TWILIO_ACCOUNT_SID = 'your_account_sid';
   const TWILIO_AUTH_TOKEN  = 'your_auth_token';
   const TWILIO_SMS_FROM    = '+19045551234'; // Your Twilio number
   ```
3. Restart Docker: `docker compose -f docker-compose.yml restart`

### To Enable Voice Call Forwarding (Optional)
1. Edit `api/.env.local.php`
2. Set your forwarding number:
   ```php
   const TWILIO_FORWARD_TO = '+19045551234'; // Your business phone
   ```
3. Configure Twilio webhook: `https://yourdomain.com/voice/incoming.php`

### To Enable CRM Integration (Optional)
The Rukovoditel CRM is included but needs initial setup:
1. Access phpMyAdmin at http://localhost:8081
2. Import the CRM database schema (if available in `crm/` directory)
3. Configure CRM credentials in `api/.env.local.php`

## 📚 Next Steps

1. **Test the Quote Form**: Visit the site and fill out the quote form - you'll get instant estimates!

2. **Customize Your Info**: Edit `index.html` to update:
   - Phone number (currently: 904-217-5152)
   - Email address
   - Business hours
   - Service area

3. **Review Documentation**:
   - [Twilio Integration Guide](docs/twilio_cloudflare_integration.md)
   - [Setup Checklist](docs/twilio_setup_checklist.md)
   - [Architecture Diagram](docs/architecture_diagram.md)

## 🎯 Quote Form Features

The quote form already works great with these features:

- **Smart Pricing**: Automatically calculates estimates based on:
  - Repair type (Oil Change, Brake Pads, Alternator, etc.)
  - Engine type (V8 engines get appropriate multipliers)
  - Vehicle age (older cars get complexity multipliers)
  - Mobile service premium (~20%)

- **Time Slot Selection**: Customers can pick their preferred appointment time

- **Client-side Fallback**: If the PHP backend is unavailable, the form uses JavaScript to calculate estimates

- **SMS Opt-in**: Customers can choose to receive quote via text (requires Twilio setup)

## 🛠️ Troubleshooting

### Container won't start
```bash
# Check logs
docker compose -f docker-compose.yml logs

# Rebuild from scratch
docker compose -f docker-compose.yml down -v
docker compose -f docker-compose.yml up -d --build
```

### Can't access site at localhost:8080
- Make sure Docker containers are running: `docker compose -f docker-compose.yml ps`
- Check if port 8080 is already in use: `lsof -i :8080`

### Quote form not working
- Check browser console for JavaScript errors
- Verify PHP endpoint: `curl http://localhost:8080/quote/quote_intake_handler.php`
- Even without backend, client-side estimation should work

## 💡 Tips

- The site works great with just the basics - no Twilio or CRM required!
- Quote estimates are calculated locally using the `price-catalog.json` file
- All your old project data is preserved in the repository
- The Docker setup is production-ready for local development

---

**Your site is ready to use! Visit http://localhost:8080 to see it in action.** 🎉
