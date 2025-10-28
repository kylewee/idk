# Twilio and Cloudflare Integration Checklist

## Quick Setup Checklist

Use this checklist when setting up Twilio integration for the first time or troubleshooting issues.

### Prerequisites ✓

- [ ] Twilio account created and verified
- [ ] At least one Twilio phone number purchased
- [ ] Domain (mechanicstaugustine.com) configured in Cloudflare
- [ ] Cloudflare tunnel installed and running
- [ ] Server with Caddy and PHP 7.4+ installed

### Initial Configuration

#### 1. Cloudflare Tunnel Setup

- [ ] Verify tunnel is running: `sudo systemctl status cloudflared`
- [ ] Check DNS CNAME records point to tunnel
- [ ] Confirm SSL/TLS mode is "Full" or "Full (strict)"
- [ ] Enable "Always Use HTTPS"
- [ ] Verify redirect rules don't block /voice/* or /api/* paths

#### 2. Environment Configuration

- [ ] Copy `api/.env.local.php.example` to `api/.env.local.php`
- [ ] Set file permissions: `chmod 600 api/.env.local.php`
- [ ] Fill in TWILIO_ACCOUNT_SID
- [ ] Fill in TWILIO_AUTH_TOKEN
- [ ] Set TWILIO_CALLER_ID (your Twilio number)
- [ ] Set TWILIO_FORWARD_TO (destination number)
- [ ] Set TWILIO_SMS_FROM (for SMS notifications)
- [ ] Generate and set VOICE_RECORDINGS_TOKEN

#### 3. Twilio Phone Number Configuration

- [ ] Log into Twilio Console
- [ ] Navigate to Phone Numbers → Active Numbers
- [ ] Select your number
- [ ] Voice Configuration:
  - [ ] URL: `https://mechanicstaugustine.com/voice/incoming.php`
  - [ ] Method: POST
- [ ] Messaging Configuration (optional):
  - [ ] URL: `https://mechanicstaugustine.com/api/sms_incoming.php`
  - [ ] Method: POST
- [ ] Save configuration

#### 4. Verify Installation

- [ ] Test incoming webhook: `curl -I https://mechanicstaugustine.com/voice/incoming.php`
  - Expected: HTTP/2 200
- [ ] Test recording callback: `curl -I https://mechanicstaugustine.com/voice/recording_callback.php`
  - Expected: HTTP/2 200
- [ ] Check voice log exists: `ls -la /path/to/site/voice/voice.log`
- [ ] Verify write permissions: `touch /path/to/site/voice/test.log && rm /path/to/site/voice/test.log`

### Functional Testing

#### Voice Call Test

- [ ] Call your Twilio number from a phone
- [ ] Hear: "Connecting you now"
- [ ] Call forwards to TWILIO_FORWARD_TO number
- [ ] Answer the forwarded call
- [ ] Hang up
- [ ] Check voice.log for incoming_twiml entry
- [ ] Wait 30 seconds, check for recording_available entry
- [ ] Verify recording can be accessed via recordings page

#### SMS Test

- [ ] Submit quote form with SMS opt-in checked
- [ ] Verify SMS received at test phone number
- [ ] Check quote_intake.log for SMS success
- [ ] Confirm estimate details in SMS message

### Troubleshooting Checklist

#### No Webhook Calls Received

- [ ] Cloudflare tunnel running: `sudo systemctl status cloudflared`
- [ ] Caddy running: `sudo systemctl status caddy`
- [ ] DNS resolves correctly: `dig mechanicstaugustine.com`
- [ ] Webhook URL accessible: `curl -I https://mechanicstaugustine.com/voice/incoming.php`
- [ ] Twilio webhook URL configured correctly (no typos)
- [ ] Cloudflare WAF not blocking Twilio IPs
- [ ] Check Twilio debugger for errors

#### Calls Not Forwarding

- [ ] TWILIO_FORWARD_TO in E.164 format (+1XXXXXXXXXX)
- [ ] TWILIO_CALLER_ID is valid Twilio number
- [ ] Destination number can receive calls
- [ ] Twilio account has sufficient balance
- [ ] Check voice.log for errors

#### Recordings Not Appearing

- [ ] recording_callback.php returns 200 status
- [ ] PHP has write permissions to voice/ directory
- [ ] TWILIO_ACCOUNT_SID and TWILIO_AUTH_TOKEN correct
- [ ] Check Twilio debugger for callback errors
- [ ] Review voice.log for recording download errors

#### SMS Not Sending

- [ ] TWILIO_SMS_FROM is SMS-capable number
- [ ] Phone number in E.164 format (+1XXXXXXXXXX)
- [ ] Recipient hasn't sent STOP
- [ ] Twilio messaging service active
- [ ] Check quote_intake.log for error details

### Security Checklist

- [ ] api/.env.local.php has 600 permissions
- [ ] api/.env.local.php in .gitignore
- [ ] VOICE_RECORDINGS_TOKEN is random and secure (32+ characters)
- [ ] All webhook URLs use HTTPS
- [ ] Cloudflare proxy enabled (orange cloud)
- [ ] Consider implementing Twilio signature validation
- [ ] Recording access requires token authentication

### Maintenance Checklist (Monthly)

- [ ] Review voice.log for errors or unusual patterns
- [ ] Check Twilio account balance and usage
- [ ] Verify all webhook endpoints return 200 status
- [ ] Test end-to-end call flow
- [ ] Test SMS quote notifications
- [ ] Review and archive old log files if needed
- [ ] Check Cloudflare tunnel uptime
- [ ] Verify SSL certificate valid

### Production Deployment Checklist

- [ ] All development/test credentials replaced with production
- [ ] Production Twilio numbers configured
- [ ] TWILIO_FORWARD_TO points to correct business number
- [ ] Email notifications configured (VOICE_EMAIL_NOTIFY_TO)
- [ ] Monitoring set up for webhook endpoints
- [ ] Log rotation configured
- [ ] Backup strategy in place for recordings
- [ ] Emergency contact procedures documented
- [ ] Rate limiting configured if needed
- [ ] Tested from multiple phone carriers

### Optional Features

- [ ] Twilio Conversational Intelligence enabled
  - [ ] CI_SERVICE_SID configured
  - [ ] TWILIO_API_KEY_SID configured
  - [ ] TWILIO_API_KEY_SECRET configured
- [ ] Multiple phone numbers configured
- [ ] Messaging Service SID configured
- [ ] SMS compliance (STOP/HELP responses) implemented
- [ ] Voicemail detection configured

## Quick Reference Commands

### System Status
```bash
# Check services
sudo systemctl status cloudflared
sudo systemctl status caddy

# View logs
journalctl -u cloudflared -n 50 --no-pager
tail -f /path/to/site/voice/voice.log
tail -f /path/to/site/api/quote_intake.log
```

### Test Endpoints
```bash
# Test webhooks
curl -I https://mechanicstaugustine.com/voice/incoming.php
curl -I https://mechanicstaugustine.com/voice/recording_callback.php
curl -I https://mechanicstaugustine.com/quote/quote_intake_handler.php

# Test quote submission
curl -X POST https://mechanicstaugustine.com/quote/quote_intake_handler.php \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","phone":"+15551234567","service":"oil change","year":"2020","make":"Honda","model":"Civic","engine":"4-cylinder","text_opt_in":true}'
```

### Update Webhooks (Development)
```bash
# With ngrok
ngrok http 8080
php scripts/twilio/update-webhooks.php https://your-url.ngrok.io

# With production URL
export VOICE_BASE_URL=https://mechanicstaugustine.com
php scripts/twilio/update-webhooks.php
```

### Restart Services
```bash
sudo systemctl restart cloudflared
sudo systemctl restart caddy
```

## Common Issues Quick Fixes

| Issue | Quick Fix |
|-------|-----------|
| Webhooks not working | `sudo systemctl restart cloudflared` |
| 520/522 errors | Check Cloudflare tunnel logs |
| Calls not forwarding | Verify TWILIO_FORWARD_TO format |
| SMS not sending | Check TWILIO_SMS_FROM is configured |
| Recordings not saving | Check voice/ directory permissions |
| Config not loading | Verify api/.env.local.php exists and syntax is valid |

## Support Resources

- Full Integration Guide: `docs/twilio_cloudflare_integration.md`
- Runbook: `docs/runbook.md`
- SMS Setup: `quote/SMS_SETUP.md`
- Twilio Console: https://console.twilio.com
- Twilio Debugger: https://console.twilio.com/monitor/debugger
- Cloudflare Dashboard: https://dash.cloudflare.com

## Notes

- Always test changes in development before production
- Keep TWILIO_AUTH_TOKEN and other secrets secure
- Monitor logs regularly for unusual activity
- Document any custom modifications
- Back up configuration before making changes
