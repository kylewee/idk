# Configuration and Setup Guide

## Overview
This document provides step-by-step instructions for setting up and configuring the Mechanic Saint Augustine website's phone system and CRM integration.

## Quick Start

### 1. Configuration File Setup
The main configuration file is `/api/.env.local.php`. This file has been created with default values, but you need to update it with your actual credentials.

**Important**: Never commit `.env.local.php` to version control as it contains sensitive credentials.

### 2. Required Credentials

#### Twilio Configuration (for Phone System)
You need to set up the following Twilio credentials in `/api/.env.local.php`:

```php
const TWILIO_ACCOUNT_SID = 'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';  // From Twilio Console
const TWILIO_AUTH_TOKEN  = 'your_auth_token_here';               // From Twilio Console
const TWILIO_CALLER_ID   = '+19048349227';                        // Your Twilio phone number
const TWILIO_FORWARD_TO  = '+19046634789';                        // Your personal phone
const TWILIO_SMS_FROM    = '+19048349227';                        // Your Twilio SMS number
```

**Where to find these:**
1. Log in to [Twilio Console](https://console.twilio.com/)
2. Account SID and Auth Token are on the main dashboard
3. Phone numbers are under Phone Numbers → Manage → Active numbers

#### CRM Configuration (Rukovoditel)
Update these values in `/api/.env.local.php`:

```php
const CRM_API_URL = 'https://mechanicstaugustine.com/crm/api/rest.php';
const CRM_API_KEY = 'VMm87uzSFFyWAWCDzCXEK2AajBbHIOOIwtfhMWbA';
const CRM_USERNAME = 'your_crm_username';  // Your CRM login username
const CRM_PASSWORD = 'your_crm_password';  // Your CRM login password
```

**Where to find these:**
1. CRM username/password: Your login credentials for the CRM at https://mechanicstaugustine.com/crm/
2. API Key: In CRM, go to Settings → API → Generate API Key

### 3. Twilio Webhook Configuration

Once your site is live, configure Twilio webhooks:

1. Go to Twilio Console → Phone Numbers → Manage → Active numbers
2. Click on your phone number
3. Under "Voice & Fax", set:
   - **A CALL COMES IN**: Webhook → `https://mechanicstaugustine.com/voice/incoming.php` (HTTP POST)
4. Under "Messaging", set:
   - **A MESSAGE COMES IN**: Webhook → (if needed for SMS features)
5. Click **Save**

### 4. Testing the Configuration

#### Test Phone System
```bash
# Check that the voice endpoint is accessible
curl -I https://mechanicstaugustine.com/voice/incoming.php

# Should return HTTP 200 OK
```

Then make a test call to your Twilio number to verify call forwarding works.

#### Test Quote System
```bash
# Submit a test quote request
curl -X POST https://mechanicstaugustine.com/api/quote_intake.php \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "phone": "+19045551234",
    "email": "test@example.com",
    "year": "2020",
    "make": "Toyota",
    "model": "Camry",
    "engine": "2.5",
    "repair": "Oil Change",
    "text_opt_in": false
  }'

# Should return JSON with success status and estimate
```

#### Check Logs
```bash
# View recent quote intake activity
tail -f /path/to/site/api/quote_intake.log

# View voice system activity
tail -f /path/to/site/voice/voice.log
```

## Troubleshooting

### Issue: "username is required" or "key is required" in CRM logs

**Cause**: Missing CRM credentials in `.env.local.php`

**Solution**:
1. Edit `/api/.env.local.php`
2. Set `CRM_USERNAME` and `CRM_PASSWORD` with valid CRM credentials
3. Alternatively, ensure `CRM_API_KEY` is set correctly

### Issue: Phone calls not being forwarded

**Possible causes and solutions**:

1. **Twilio webhook not configured**
   - Verify webhook URL in Twilio Console points to `https://mechanicstaugustine.com/voice/incoming.php`
   - Check that it's set to HTTP POST method

2. **Missing TWILIO_FORWARD_TO**
   - Edit `/api/.env.local.php`
   - Set `TWILIO_FORWARD_TO` to your phone number (format: `+19046634789`)

3. **Cloudflare redirect issues**
   - Per runbook.md, ensure Cloudflare redirect rules only match intended hostnames
   - Check that voice URLs aren't being redirected

### Issue: SMS not being sent

**Possible causes**:
1. Missing Twilio credentials (TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN)
2. Invalid TWILIO_SMS_FROM number
3. Phone number not verified in Twilio (for trial accounts)

**Check logs**:
```bash
grep "sms" /path/to/site/api/quote_intake.log | tail -5
```

Look for HTTP 201 status in SMS responses (indicates success).

### Issue: CRM leads not being created

**Check the quote intake log**:
```bash
tail -20 /path/to/site/api/quote_intake.log | grep -i crm
```

**Common errors and fixes**:
- `"username is required"`: Set CRM_USERNAME and CRM_PASSWORD
- `"No match for Username and/or Password"`: Verify credentials are correct
- `"entity_id is required"`: Verify CRM_LEADS_ENTITY_ID is set to valid entity ID (currently 26)

### Issue: Site not accessible after reboot

**Follow the runbook**:
```bash
# Restart Cloudflare tunnel
sudo systemctl restart cloudflared

# Restart Caddy (if needed)
sudo systemctl restart caddy

# Check tunnel status
sudo systemctl status cloudflared

# Verify site responds
curl -I https://mechanicstaugustine.com
```

## Local Development

For local testing using Docker:

```bash
# Start the development stack
docker compose up -d --build

# Access the site
# App: http://localhost:8080
# phpMyAdmin: http://localhost:8081 (user: crm, pass: crm)

# Test endpoints locally
curl http://localhost:8080/voice/incoming.php
curl -X POST http://localhost:8080/api/quote_intake.php \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","phone":"+19045551234",...}'
```

## Security Notes

1. **Never commit `.env.local.php`** - It's in `.gitignore` for a reason
2. **Use strong passwords** for CRM credentials
3. **Rotate API keys** periodically
4. **Enable HTTPS** - All production traffic should use HTTPS
5. **Restrict API access** - Consider IP whitelisting for sensitive endpoints

## Additional Resources

- **Project Blueprint**: `/docs/project_blueprint.md` - Long-term architecture vision
- **Runbook**: `/docs/runbook.md` - Live site operations guide
- **API Outline**: `/docs/api_outline.md` - API documentation
- **Twilio Docs**: https://www.twilio.com/docs
- **Rukovoditel CRM**: In-site documentation at `/crm/`

## Getting Help

If you continue to experience issues:

1. Check the logs (see "Check Logs" section above)
2. Review this troubleshooting guide
3. Consult the runbook at `/docs/runbook.md`
4. For Twilio issues, check your Twilio Console → Monitor → Logs
5. For CRM issues, log into the CRM directly and verify entities/fields exist

## Next Steps

Once basic configuration is working:

1. **Update field mappings**: Set the actual CRM field IDs in `CRM_FIELD_MAP`
2. **Enable transcription**: Set `TWILIO_TRANSCRIBE_ENABLED = true` if desired
3. **Configure email notifications**: Set up SMTP or SendGrid for email alerts
4. **Test thoroughly**: Make test calls and submit test quotes to verify end-to-end flow
5. **Monitor logs**: Regularly review logs to catch issues early

## Configuration Checklist

- [ ] Set TWILIO_ACCOUNT_SID and TWILIO_AUTH_TOKEN
- [ ] Set TWILIO_CALLER_ID (your Twilio number)
- [ ] Set TWILIO_FORWARD_TO (your phone)
- [ ] Set CRM_USERNAME and CRM_PASSWORD
- [ ] Verify CRM_API_KEY is correct
- [ ] Verify CRM_LEADS_ENTITY_ID (currently 26)
- [ ] Configure Twilio webhooks in Twilio Console
- [ ] Test incoming calls
- [ ] Test quote submission
- [ ] Verify CRM leads are created
- [ ] Check logs for errors
