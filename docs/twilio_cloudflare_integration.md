# Twilio and Cloudflare Phone Number Integration Guide

## Overview

This guide covers the integration of Twilio phone numbers with the Mechanic Saint Augustine website hosted behind a Cloudflare tunnel. The integration enables:

- Inbound call routing and recording
- SMS notifications for quote requests
- Automated transcription via Twilio Intelligence
- Secure webhook delivery through Cloudflare

## Architecture

```
Customer Call/SMS
    ↓
Twilio Phone Number
    ↓
Twilio Webhooks (HTTPS)
    ↓
Cloudflare DNS (mechanicstaugustine.com)
    ↓
Cloudflare Tunnel (mechanicsain-tunnel)
    ↓
Caddy Server (localhost:8080)
    ↓
PHP Webhook Handlers (voice/incoming.php, voice/recording_callback.php)
```

## Prerequisites

1. **Twilio Account** with:
   - Active phone number(s) capable of voice and SMS
   - Account SID and Auth Token
   - (Optional) API Key/Secret for advanced features
   - (Optional) Conversational Intelligence Service SID

2. **Cloudflare Account** with:
   - Domain configured (mechanicstaugustine.com)
   - Cloudflare Tunnel set up and running
   - DNS proxying enabled

3. **Server Requirements**:
   - Caddy web server running and accessible via Cloudflare tunnel
   - PHP 7.4+ with curl extension
   - Proper file permissions for log directories

## Step 1: Cloudflare Tunnel Setup

### Verify Tunnel Status

```bash
# Check tunnel is running
sudo systemctl status cloudflared

# View tunnel logs
journalctl -u cloudflared -n 50 --no-pager
```

Expected output should show: `Registered tunnel connection`

### DNS Configuration

1. Log into Cloudflare dashboard
2. Navigate to DNS settings for mechanicstaugustine.com
3. Verify CNAME records:
   ```
   mechanicstaugustine.com → ac25c77a-477c-47ea-ab37-40992c075ab7.cfargotunnel.com (Proxied)
   www.mechanicstaugustine.com → ac25c77a-477c-47ea-ab37-40992c075ab7.cfargotunnel.com (Proxied)
   ```

### Important Cloudflare Settings

1. **SSL/TLS Mode**: Set to "Full" or "Full (strict)"
2. **Always Use HTTPS**: Enabled
3. **Minimum TLS Version**: 1.2 or higher
4. **Automatic HTTPS Rewrites**: Enabled

### Redirect Rules Configuration

**Critical**: Ensure redirect rules don't interfere with webhook paths:

1. **WWW to Root Redirect**:
   - Condition: `http.host eq "www.mechanicstaugustine.com"`
   - Target: `https://mechanicstaugustine.com${1}`
   - Status: 301

2. **Do NOT apply global redirects** that would affect:
   - `/voice/*` paths
   - `/api/*` paths
   - `/quote/*` paths

## Step 2: Environment Configuration

### Create Configuration File

```bash
cd /path/to/site
cp api/.env.local.php.example api/.env.local.php
chmod 600 api/.env.local.php  # Protect sensitive data
```

### Configure Twilio Credentials

Edit `api/.env.local.php`:

```php
<?php
// Twilio Account Credentials
const TWILIO_ACCOUNT_SID = 'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
const TWILIO_AUTH_TOKEN  = 'your_auth_token_here';

// Twilio Phone Numbers
const TWILIO_CALLER_ID   = '+19048349227';  // Your Twilio number for outbound
const TWILIO_FORWARD_TO  = '+19046634789';  // Your personal/business number
const TWILIO_SMS_FROM    = '+19048349227';  // Number for SMS sending

// Optional: Twilio Conversational Intelligence
const TWILIO_API_KEY_SID     = 'SKxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
const TWILIO_API_KEY_SECRET  = 'your_api_key_secret';
const CI_SERVICE_SID         = 'GAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

// Optional: Enable native transcription
const TWILIO_TRANSCRIBE_ENABLED = false;  // Set to true to enable

// Recording Protection
const VOICE_RECORDINGS_TOKEN = 'your_secure_random_token';

// Email Notifications (optional)
const VOICE_EMAIL_NOTIFY_TO = 'alerts@mechanicstaugustine.com';
const VOICE_EMAIL_FROM      = 'noreply@mechanicstaugustine.com';
```

## Step 3: Twilio Phone Number Configuration

### Option A: Using Twilio Console (Recommended for Initial Setup)

1. Log into [Twilio Console](https://console.twilio.com)
2. Navigate to **Phone Numbers → Manage → Active Numbers**
3. Select your phone number
4. Configure **Voice & Fax** section:
   - **Configure with**: Webhooks, TwiML Bins, Functions, Studio, or Proxy
   - **A call comes in**: Webhook
   - **URL**: `https://mechanicstaugustine.com/voice/incoming.php`
   - **HTTP Method**: POST
   - **Primary Handler Fails**: (optional fallback URL)

5. Configure **Messaging** section:
   - **Configure with**: Webhooks
   - **A message comes in**: Webhook
   - **URL**: `https://mechanicstaugustine.com/api/sms_incoming.php` (if SMS inbound needed)
   - **HTTP Method**: POST

6. Click **Save**

### Option B: Using Update Script (for Development)

For local development with ngrok:

```bash
# Start ngrok tunnel
ngrok http 8080

# Update Twilio webhooks automatically
php scripts/twilio/update-webhooks.php https://your-ngrok-url.ngrok.io
```

For production:

```bash
# Set environment variable
export VOICE_BASE_URL=https://mechanicstaugustine.com

# Update webhooks
php scripts/twilio/update-webhooks.php
```

### Verify Webhook Configuration

```bash
# Test incoming webhook endpoint
curl -I https://mechanicstaugustine.com/voice/incoming.php

# Expected: HTTP/2 200
# Content-Type: text/xml
```

## Step 4: Testing the Integration

### Test 1: Basic Connectivity

```bash
# Test from origin (bypassing Cloudflare)
curl -I https://mechanicstaugustine.com/voice/incoming.php \
  --resolve mechanicstaugustine.com:443:127.0.0.1

# Test through Cloudflare
curl -I https://mechanicstaugustine.com/voice/incoming.php
```

Both should return `HTTP/2 200`.

### Test 2: Voice Call Flow

1. Call your Twilio number from a phone
2. Expected behavior:
   - Hear: "Connecting you now"
   - Brief pause
   - Call forwards to TWILIO_FORWARD_TO number
   - Call is recorded from answer
3. Monitor logs:
   ```bash
   tail -f /path/to/site/voice/voice.log
   ```

Expected log entry:
```json
{
  "ts": "2025-10-28T13:42:00+00:00",
  "ip": "54.172.60.0",
  "event": "incoming_twiml",
  "to": "+19046634789",
  "from": "+15551234567",
  "method": "POST"
}
```

### Test 3: Recording Callback

After call completes:

```bash
# Check recording callback logs
tail -f /path/to/site/voice/voice.log | grep recording_available
```

Expected log entry:
```json
{
  "ts": "2025-10-28T13:45:00+00:00",
  "event": "recording_available",
  "recordingSid": "RExxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "callSid": "CAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}
```

### Test 4: SMS Quote Follow-up

Submit a quote request with SMS opt-in:

```bash
curl -X POST https://mechanicstaugustine.com/quote/quote_intake_handler.php \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "phone": "+15551234567",
    "service": "oil change",
    "year": "2018",
    "make": "Toyota",
    "model": "Camry",
    "engine": "4-cylinder",
    "text_opt_in": true
  }'
```

Expected response:
```json
{
  "success": true,
  "estimate": {
    "amount": "50.00",
    "service": "oil change"
  },
  "sms": {
    "sent": true,
    "sid": "SMxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

## Troubleshooting

### Issue: Webhooks Not Receiving Data

**Symptoms**: No entries in voice.log when calling the number

**Diagnosis**:
```bash
# Check Cloudflare tunnel status
sudo systemctl status cloudflared

# Check Caddy status
sudo systemctl status caddy

# Test webhook URL from external source
curl -X POST https://mechanicstaugustine.com/voice/incoming.php \
  -d "From=%2B15551234567"
```

**Solutions**:
1. Restart cloudflared: `sudo systemctl restart cloudflared`
2. Check Cloudflare redirect rules aren't intercepting /voice/* paths
3. Verify DNS CNAME records are proxied (orange cloud)
4. Check Twilio webhook URL doesn't have typos

### Issue: "Invalid Webhook URL" in Twilio

**Symptoms**: Twilio returns error when saving webhook URL

**Solutions**:
1. Ensure URL uses HTTPS (not HTTP)
2. Verify domain resolves publicly: `dig mechanicstaugustine.com`
3. Test URL returns 200 status code
4. Check Cloudflare SSL mode is "Full" not "Flexible"

### Issue: Calls Not Forwarding

**Symptoms**: Hear TwiML message but call doesn't connect

**Diagnosis**:
```bash
# Check TWILIO_FORWARD_TO is set correctly
grep TWILIO_FORWARD_TO /path/to/site/api/.env.local.php

# Check logs for dial action
tail -f /path/to/site/voice/voice.log
```

**Solutions**:
1. Verify TWILIO_FORWARD_TO is E.164 format: `+1XXXXXXXXXX`
2. Ensure TWILIO_CALLER_ID is a verified Twilio number
3. Check Twilio account has sufficient balance
4. Verify destination number can receive calls

### Issue: Recordings Not Saved

**Symptoms**: Calls complete but no recordings appear

**Diagnosis**:
```bash
# Check recording callback endpoint
curl -I https://mechanicstaugustine.com/voice/recording_callback.php

# Check file permissions
ls -la /path/to/site/voice/
```

**Solutions**:
1. Ensure recording_callback.php is accessible (200 status)
2. Check PHP has write permissions to voice/ directory
3. Verify TWILIO_ACCOUNT_SID and TWILIO_AUTH_TOKEN are correct
4. Review Twilio debugger for webhook errors

### Issue: SMS Not Sending

**Symptoms**: Quote submissions succeed but no SMS received

**Diagnosis**:
```bash
# Check SMS configuration
grep TWILIO_SMS_FROM /path/to/site/api/.env.local.php

# Check quote intake logs
tail -f /path/to/site/api/quote_intake.log
```

**Solutions**:
1. Verify TWILIO_SMS_FROM is an SMS-capable number
2. Check phone number format is E.164: `+1XXXXXXXXXX`
3. Verify Twilio messaging service is active
4. Check recipient hasn't opted out (STOP)
5. Review response JSON for error details under `data.sms`

### Issue: Cloudflare Blocking Twilio Webhooks

**Symptoms**: Webhooks time out, Twilio shows 520/522 errors

**Solutions**:
1. Add Twilio IP ranges to Cloudflare allowlist:
   - Navigate to Security → WAF → Tools
   - Add IP Access Rule
   - Twilio webhook IPs: [See Twilio docs](https://www.twilio.com/docs/messaging/guides/how-to-use-twilio-ip-addresses)
2. Disable "Bot Fight Mode" for /voice/* and /api/* paths
3. Check rate limiting rules aren't blocking POST requests

## Security Best Practices

### 1. Webhook Validation

Implement Twilio signature validation in webhook handlers:

```php
// Example validation (add to incoming.php if needed)
function validateTwilioRequest(array $post, string $url, string $signature): bool {
    $authToken = TWILIO_AUTH_TOKEN;
    ksort($post);
    $data = $url;
    foreach ($post as $k => $v) {
        $data .= $k . $v;
    }
    $hash = base64_encode(hash_hmac('sha1', $data, $authToken, true));
    return hash_equals($hash, $signature);
}
```

### 2. Recordings Access Control

Protected recordings endpoint requires authentication:

```bash
# Access recordings page with token
https://mechanicstaugustine.com/voice/recording_callback.php?action=recordings&token=YOUR_TOKEN
```

Set token in `api/.env.local.php`:
```php
const VOICE_RECORDINGS_TOKEN = 'generate_random_secure_token_here';
```

### 3. Environment File Protection

```bash
# Ensure .env.local.php is not web-accessible
chmod 600 api/.env.local.php

# Verify .gitignore excludes it
grep ".env.local.php" .gitignore
```

### 4. HTTPS Enforcement

- All webhook URLs must use HTTPS
- Cloudflare "Always Use HTTPS" should be enabled
- Minimum TLS 1.2 enforced

## Monitoring and Maintenance

### Log Files

Monitor these files for issues:

```bash
# Voice webhook logs
tail -f /path/to/site/voice/voice.log

# Quote intake logs (including SMS)
tail -f /path/to/site/api/quote_intake.log

# Caddy access logs
tail -f /path/to/site/access.log

# Cloudflare tunnel logs
journalctl -u cloudflared -f
```

### Health Checks

Set up monitoring for:

```bash
# Webhook endpoints
https://mechanicstaugustine.com/voice/incoming.php
https://mechanicstaugustine.com/voice/recording_callback.php
https://mechanicstaugustine.com/quote/quote_intake_handler.php

# Ping endpoint
https://mechanicstaugustine.com/voice/ping.php
```

### Regular Maintenance

1. **Weekly**: Review voice.log for errors
2. **Monthly**: Check Twilio usage and balance
3. **Quarterly**: Rotate VOICE_RECORDINGS_TOKEN
4. **As needed**: Update webhook URLs when infrastructure changes

## Advanced Configuration

### Enabling Twilio Conversational Intelligence

For AI-powered transcription and analytics:

1. Set up Twilio CI Service in console
2. Add to `api/.env.local.php`:
   ```php
   const TWILIO_API_KEY_SID     = 'SKxxx...';
   const TWILIO_API_KEY_SECRET  = 'your_secret';
   const CI_SERVICE_SID         = 'GAxxx...';
   ```
3. Callback handler `voice/ci_callback.php` processes transcripts

### Multiple Phone Numbers

To support multiple Twilio numbers:

1. Configure each number in Twilio console with same webhook URLs
2. Webhook handlers automatically detect called/calling numbers
3. Use number-specific routing in incoming.php if needed:
   ```php
   $to = match($_POST['To'] ?? '') {
       '+19048349227' => '+15551111111',  // Main line
       '+19048349228' => '+15552222222',  // Sales line
       default => TWILIO_FORWARD_TO
   };
   ```

### Messaging Service Setup

For advanced SMS features:

1. Create Messaging Service in Twilio console
2. Add phone number(s) to service
3. Configure in `api/.env.local.php`:
   ```php
   const TWILIO_MESSAGING_SERVICE_SID = 'MGxxx...';
   ```
4. Quote handler automatically uses service if configured

## Reference Links

- [Twilio Console](https://console.twilio.com)
- [Twilio Webhook Security](https://www.twilio.com/docs/usage/security)
- [Cloudflare Tunnel Docs](https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/)
- [Twilio TwiML Reference](https://www.twilio.com/docs/voice/twiml)
- [Twilio SMS API](https://www.twilio.com/docs/sms)

## Support

For issues:
1. Check troubleshooting section above
2. Review log files for error messages
3. Check Twilio debugger: https://console.twilio.com/monitor/debugger
4. Verify Cloudflare tunnel status
5. Test webhook endpoints directly

## Appendix: Webhook Payload Examples

### Incoming Call (voice/incoming.php)

Twilio sends:
```
POST /voice/incoming.php HTTP/1.1
Host: mechanicstaugustine.com
Content-Type: application/x-www-form-urlencoded

CallSid=CAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx&
From=%2B15551234567&
To=%2B19048349227&
CallStatus=ringing&
Direction=inbound&
...
```

Response (TwiML):
```xml
<?xml version="1.0" encoding="UTF-8"?>
<Response>
  <Say voice="alice">Connecting you now.</Say>
  <Pause length="1" />
  <Dial record="record-from-answer" answerOnBridge="true" 
        callerId="+19048349227"
        recordingStatusCallback="https://mechanicstaugustine.com/voice/recording_callback.php">
    <Number>+19046634789</Number>
  </Dial>
  <Hangup />
</Response>
```

### Recording Callback (voice/recording_callback.php)

Twilio sends:
```
POST /voice/recording_callback.php HTTP/1.1
Host: mechanicstaugustine.com
Content-Type: application/x-www-form-urlencoded

RecordingSid=RExxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx&
RecordingUrl=https://api.twilio.com/2010-04-01/Accounts/.../Recordings/RExxxxx&
RecordingStatus=completed&
RecordingDuration=125&
CallSid=CAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx&
...
```

Handler downloads MP3 and stores metadata.
