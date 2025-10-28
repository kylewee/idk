# Twilio Integration Architecture Diagram

## System Overview

This document provides a visual representation of how Twilio integrates with the Mechanic Saint Augustine website infrastructure.

## Call Flow Diagram

```
┌─────────────────┐
│   Customer      │
│   Phone Call    │
└────────┬────────┘
         │
         │ Dials Twilio Number
         │ (+1-904-834-9227)
         ↓
┌────────────────────────────────────────┐
│      Twilio Voice Platform             │
│  - Receives incoming call              │
│  - Looks up webhook configuration      │
└────────┬───────────────────────────────┘
         │
         │ POST to configured webhook
         │ (voice/incoming.php)
         ↓
┌────────────────────────────────────────┐
│      Internet / Public Network         │
└────────┬───────────────────────────────┘
         │
         │ HTTPS Request
         ↓
┌────────────────────────────────────────┐
│      Cloudflare Network                │
│  - DNS Resolution                      │
│  - SSL/TLS Termination                 │
│  - DDoS Protection                     │
│  - WAF (Web Application Firewall)      │
└────────┬───────────────────────────────┘
         │
         │ Proxied through tunnel
         │ (mechanicsain-tunnel)
         ↓
┌────────────────────────────────────────┐
│    Cloudflared Tunnel Service          │
│  - Runs on server as systemd service   │
│  - Maintains persistent connection     │
│  - Routes to local Caddy               │
└────────┬───────────────────────────────┘
         │
         │ HTTP to localhost:8080
         ↓
┌────────────────────────────────────────┐
│      Caddy Web Server                  │
│  - Serves static files                 │
│  - Routes to PHP handlers              │
│  - Access logging                      │
└────────┬───────────────────────────────┘
         │
         │ Execute PHP script
         ↓
┌────────────────────────────────────────┐
│   voice/incoming.php                   │
│  - Loads .env.local.php config         │
│  - Generates TwiML response            │
│  - Logs webhook data                   │
└────────┬───────────────────────────────┘
         │
         │ Returns TwiML XML
         ↓
┌────────────────────────────────────────┐
│      Twilio Voice Platform             │
│  - Parses TwiML instructions           │
│  - Executes <Say> verb                 │
│  - Executes <Dial> verb                │
└────────┬───────────────────────────────┘
         │
         │ Places call to forward number
         │ Starts recording
         ↓
┌────────────────────────────────────────┐
│   Business Phone                       │
│   (TWILIO_FORWARD_TO)                  │
│   +1-904-663-4789                      │
└────────────────────────────────────────┘
```

## Recording Flow

```
┌────────────────┐
│  Call Ends     │
└────────┬───────┘
         │
         │ Recording finalized
         ↓
┌────────────────────────────────────────┐
│    Twilio Recording Service            │
│  - Processes audio file                │
│  - Stores MP3 on Twilio                │
│  - Triggers callback webhook           │
└────────┬───────────────────────────────┘
         │
         │ POST to recordingStatusCallback
         │ (voice/recording_callback.php)
         ↓
┌────────────────────────────────────────┐
│   Cloudflare → Tunnel → Caddy          │
│   (Same path as incoming)              │
└────────┬───────────────────────────────┘
         │
         ↓
┌────────────────────────────────────────┐
│  voice/recording_callback.php          │
│  - Receives recording metadata         │
│  - Downloads MP3 from Twilio           │
│  - Stores locally (optional)           │
│  - Logs recording details              │
│  - Sends email notification (optional) │
└────────────────────────────────────────┘
```

## SMS Quote Flow

```
┌────────────────┐
│   Customer     │
│  Submits Quote │
│  (SMS opt-in)  │
└────────┬───────┘
         │
         │ POST to quote form
         ↓
┌────────────────────────────────────────┐
│   quote/quote_intake_handler.php       │
│  - Validates input                     │
│  - Calculates estimate                 │
│  - Creates CRM lead                    │
└────────┬───────────────────────────────┘
         │
         │ If text_opt_in = true
         ↓
┌────────────────────────────────────────┐
│   Send SMS via Twilio API              │
│  - POST to Twilio Messages API         │
│  - From: TWILIO_SMS_FROM               │
│  - To: Customer phone number           │
│  - Body: Estimate + contact info       │
└────────┬───────────────────────────────┘
         │
         │ Response
         ↓
┌────────────────────────────────────────┐
│   Customer receives SMS                │
│   "Your estimate: $X.XX                │
│    Reply STOP to opt out"              │
└────────────────────────────────────────┘
```

## Transcription Flow (Optional - with Twilio Intelligence)

```
┌────────────────┐
│  Recording     │
│  Completed     │
└────────┬───────┘
         │
         │ Enqueue for transcription
         ↓
┌────────────────────────────────────────┐
│  Twilio Conversational Intelligence    │
│  - Audio → text transcription          │
│  - AI-powered analytics                │
│  - Generates transcript SID            │
└────────┬───────────────────────────────┘
         │
         │ POST to ci_callback webhook
         │ (voice/ci_callback.php)
         ↓
┌────────────────────────────────────────┐
│  voice/ci_callback.php                 │
│  - Receives transcript notification    │
│  - Fetches full transcript via API     │
│  - Stores transcript data              │
│  - Logs for analysis                   │
└────────────────────────────────────────┘
```

## Data Flow Summary

| Component | Input | Output | Storage |
|-----------|-------|--------|---------|
| incoming.php | Twilio webhook POST | TwiML XML | voice.log |
| recording_callback.php | Recording metadata | Email/logs | Local MP3 (optional) |
| ci_callback.php | Transcript SID | Stored transcript | Database/logs |
| quote_intake_handler.php | Quote form data | JSON response + SMS | CRM + quote_intake.log |

## Network Ports

| Service | Port | Protocol | Access |
|---------|------|----------|--------|
| Caddy | 8080 | HTTP | localhost only |
| Cloudflared | Dynamic | HTTPS | Cloudflare network |
| Twilio Webhooks | 443 | HTTPS | Internet (via Cloudflare) |
| phpMyAdmin | 8081 | HTTP | localhost only (dev) |

## Security Layers

```
┌─────────────────────────────────────────────┐
│  Layer 1: Cloudflare Edge                  │
│  - DDoS mitigation                         │
│  - Rate limiting                           │
│  - SSL/TLS encryption                      │
└─────────────┬───────────────────────────────┘
              │
┌─────────────▼───────────────────────────────┐
│  Layer 2: Cloudflare Tunnel                │
│  - No exposed public IP                    │
│  - Outbound-only connection                │
│  - Encrypted tunnel                        │
└─────────────┬───────────────────────────────┘
              │
┌─────────────▼───────────────────────────────┐
│  Layer 3: Server (localhost)               │
│  - Caddy web server                        │
│  - PHP scripts                             │
│  - File permissions (600 for .env)         │
└─────────────┬───────────────────────────────┘
              │
┌─────────────▼───────────────────────────────┐
│  Layer 4: Application                      │
│  - Token authentication for recordings     │
│  - Input validation                        │
│  - Twilio signature validation (optional)  │
└─────────────────────────────────────────────┘
```

## Configuration Files

```
/path/to/site/
│
├── api/
│   ├── .env.local.php          ← All secrets here
│   ├── .env.local.php.example  ← Template
│   └── quote_intake.php
│
├── voice/
│   ├── incoming.php            ← Twilio voice webhook
│   ├── recording_callback.php  ← Recording handler
│   ├── ci_callback.php         ← Transcription webhook
│   └── voice.log              ← Webhook activity log
│
├── quote/
│   ├── quote_intake_handler.php ← Quote form + SMS
│   └── index.html
│
├── Caddyfile                   ← Web server config
│
└── docs/
    ├── twilio_cloudflare_integration.md  ← Full guide
    └── twilio_setup_checklist.md        ← Quick ref
```

## Environment Variables Map

| Variable | Used By | Purpose |
|----------|---------|---------|
| TWILIO_ACCOUNT_SID | All | API authentication |
| TWILIO_AUTH_TOKEN | All | API authentication |
| TWILIO_CALLER_ID | incoming.php | Outbound caller ID |
| TWILIO_FORWARD_TO | incoming.php | Call destination |
| TWILIO_SMS_FROM | quote_intake_handler.php | SMS sender number |
| TWILIO_API_KEY_SID | ci_callback.php | CI API access |
| TWILIO_API_KEY_SECRET | ci_callback.php | CI API access |
| CI_SERVICE_SID | ci_callback.php | Transcription service |
| VOICE_RECORDINGS_TOKEN | recording_callback.php | Web UI access control |
| CRM_API_URL | quote_intake_handler.php | Lead creation |
| CRM_USERNAME | quote_intake_handler.php | CRM auth |
| CRM_PASSWORD | quote_intake_handler.php | CRM auth |

## Monitoring Points

1. **Cloudflare Tunnel Health**
   - Command: `systemctl status cloudflared`
   - Log: `journalctl -u cloudflared`

2. **Webhook Activity**
   - File: `voice/voice.log`
   - Check: Incoming calls, recordings, errors

3. **Quote/SMS Activity**
   - File: `api/quote_intake.log`
   - Check: Submissions, SMS status, CRM sync

4. **Caddy Access Logs**
   - File: `access.log`
   - Check: HTTP status codes, response times

5. **Twilio Console**
   - URL: https://console.twilio.com/monitor/debugger
   - Check: Webhook errors, failed API calls

## Failure Modes & Mitigation

| Failure | Impact | Detection | Recovery |
|---------|--------|-----------|----------|
| Tunnel down | No webhooks | Cloudflare dashboard | `systemctl restart cloudflared` |
| Caddy down | 502 errors | Curl test fails | `systemctl restart caddy` |
| PHP errors | 500 response | PHP error logs | Check syntax, permissions |
| Twilio auth fail | 401 errors | Debugger console | Verify SID/token |
| SMS send fail | No text received | quote_intake.log | Check number, balance |
| Recording download fail | Missing audio | voice.log | Verify credentials |

## Integration Test Checklist

- [ ] Call Twilio number → Hear greeting
- [ ] Call forwards → Business phone rings
- [ ] Call completes → Recording appears in logs
- [ ] Submit quote with SMS → Receive text message
- [ ] Check voice.log → See webhook entries
- [ ] Access recordings page → Authenticate with token
- [ ] Review Twilio debugger → No errors

This diagram complements the detailed setup guide in `docs/twilio_cloudflare_integration.md`.
