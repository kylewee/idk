# Quick Reference Card

## 🚀 Getting Started (5 Minutes)

### Step 1: Add Your Credentials
```bash
# Edit the configuration file
nano api/.env.local.php

# Fill in these values:
# - TWILIO_ACCOUNT_SID (from Twilio Console)
# - TWILIO_AUTH_TOKEN (from Twilio Console)
# - CRM_USERNAME (your CRM login)
# - CRM_PASSWORD (your CRM password)
```

### Step 2: Configure Twilio Webhooks
1. Go to: https://console.twilio.com/
2. Phone Numbers → Manage → Active numbers → [Your Number]
3. Set Voice webhook: `https://mechanicstaugustine.com/voice/incoming.php` (POST)
4. Save

### Step 3: Test
```bash
# Run diagnostics
./scripts/troubleshoot.sh

# Test quote submission
curl -X POST https://mechanicstaugustine.com/api/quote_intake.php \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","phone":"+19045551234","email":"test@example.com","year":"2020","make":"Toyota","model":"Camry","engine":"2.5","repair":"Oil Change"}'

# Make a test call to your Twilio number
```

## 📋 Common Commands

### Check System Health
```bash
./scripts/troubleshoot.sh
```

### View Logs
```bash
# Quote intake logs
tail -f api/quote_intake.log

# Voice system logs
tail -f voice/voice.log

# Filter for CRM errors
grep -i crm api/quote_intake.log | tail -10
```

### Local Development
```bash
# Start Docker stack
docker compose -f docker-compose.yml up -d

# Check container status
docker compose -f docker-compose.yml ps

# View container logs
docker logs idk-php-1
docker logs idk-caddy-1

# Stop containers
docker compose -f docker-compose.yml down
```

### Site Access (Local)
- Main site: http://localhost:8080
- phpMyAdmin: http://localhost:8081 (user: crm, pass: crm)
- Database: localhost:3306 (user: crm, pass: crm)

### Production Access
- Main site: https://mechanicstaugustine.com
- CRM: https://mechanicstaugustine.com/crm/
- Quote API: https://mechanicstaugustine.com/api/quote_intake.php
- Voice webhook: https://mechanicstaugustine.com/voice/incoming.php

## 🔧 Troubleshooting Quick Fixes

### Issue: "username is required" in logs
```bash
# Edit config and add CRM credentials
nano api/.env.local.php
# Set: CRM_USERNAME and CRM_PASSWORD
```

### Issue: Phone calls not forwarding
```bash
# 1. Check Twilio webhook is set correctly
# 2. Edit config
nano api/.env.local.php
# Set: TWILIO_FORWARD_TO = '+19046634789'
```

### Issue: SMS not sending
```bash
# Edit config and add Twilio credentials
nano api/.env.local.php
# Set: TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN, TWILIO_SMS_FROM
```

### Issue: Site down after reboot
```bash
# Restart services
sudo systemctl restart cloudflared
sudo systemctl restart caddy

# Check status
sudo systemctl status cloudflared
curl -I https://mechanicstaugustine.com
```

## 📖 Documentation Links

| Document | Purpose | Path |
|----------|---------|------|
| Setup Guide | Complete configuration instructions | `docs/SETUP_GUIDE.md` |
| Resolution Summary | Details of what was fixed | `docs/RESOLUTION_SUMMARY.md` |
| Runbook | Live site operations | `docs/runbook.md` |
| Blueprint | Long-term architecture | `docs/project_blueprint.md` |
| README | Quick start overview | `README.md` |

## 🔑 Important File Locations

```
/api/.env.local.php           # Main config (YOU MUST EDIT THIS)
/api/quote_intake.php         # Quote endpoint
/api/quote_intake.log         # Quote logs
/voice/incoming.php           # Voice webhook
/voice/voice.log              # Voice logs
/scripts/troubleshoot.sh      # Diagnostic tool
/docs/SETUP_GUIDE.md          # Setup instructions
```

## ✅ Configuration Checklist

After filling in credentials, verify:

- [ ] TWILIO_ACCOUNT_SID is set
- [ ] TWILIO_AUTH_TOKEN is set
- [ ] TWILIO_CALLER_ID matches your Twilio number
- [ ] TWILIO_FORWARD_TO is your phone number
- [ ] CRM_USERNAME is set
- [ ] CRM_PASSWORD is set
- [ ] Twilio webhook configured in Twilio Console
- [ ] Run `./scripts/troubleshoot.sh` - no critical errors
- [ ] Test quote submission works
- [ ] Test call forwarding works
- [ ] Check logs show successful CRM lead creation

## 🆘 Need Help?

1. Run: `./scripts/troubleshoot.sh`
2. Check: `docs/SETUP_GUIDE.md`
3. Review: `docs/runbook.md`
4. Examine logs: `tail -f api/quote_intake.log`

## 💡 Pro Tips

- Never commit `.env.local.php` - it's in `.gitignore`
- Test locally with Docker before deploying to production
- Check logs regularly for issues
- Keep credentials secure and rotate periodically
- Run troubleshoot.sh after any configuration changes

---
**Last Updated**: October 28, 2025
**Version**: 1.0
