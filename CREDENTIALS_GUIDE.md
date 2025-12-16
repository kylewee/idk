# Credentials Configuration Guide

This document lists all the locations where credentials need to be configured for the Mechanic Saint Augustine website.

## 1. Twilio Configuration (Primary Location)

**File:** `api/.env.local.php`

### Required Twilio Credentials
These must be obtained from your Twilio Console: https://console.twilio.com

```php
const TWILIO_ACCOUNT_SID = '';  // TODO: Add your Twilio Account SID
const TWILIO_AUTH_TOKEN  = '';  // TODO: Add your Twilio Auth Token
```

### Twilio Phone Numbers (Already Configured)
```php
const TWILIO_CALLER_ID   = '+19048349227';  // Your Twilio number
const TWILIO_SMS_FROM    = '+19048349227';  // SMS sender number
const TWILIO_FORWARD_TO  = '+19046634789';  // Business phone for call forwarding
```

### How to Find Your Twilio Credentials:
1. Log in to https://console.twilio.com
2. Navigate to Account Info section on the dashboard
3. Copy your **Account SID** and **Auth Token**
4. Paste them into `api/.env.local.php`

## 2. CRM Configuration (Already Configured)

**File:** `api/.env.local.php`

### CRM API Credentials (Configured from backups)
```php
const CRM_API_URL = 'https://mechanicstaugustine.com/crm/api/rest.php';
const CRM_API_KEY = 'VMm87uzSFFyWAWCDzCXEK2AajBbHIOOIwtfhMWbA';
const CRM_LEADS_ENTITY_ID = 26;
```

### CRM Field Mapping (Configured from backups)
```php
const CRM_FIELD_MAP = [
  'first_name'  => 219,  // First Name field ID
  'last_name'   => 220,  // Last Name field ID
  'name'        => 219,  // Fallback mapping
  // Other fields auto-discover
];
```

### CRM Username/Password (Optional)
If your CRM requires authentication:
```php
const CRM_USERNAME = '';  // TODO: Add if needed
const CRM_PASSWORD = '';  // TODO: Add if needed
```

## 3. Database Configuration

**File:** `crm/config/database.php`

### Docker Environment (Automatically Configured)
Database credentials are set via environment variables in `docker-compose.yml`:
```yaml
environment:
  - DB_HOST=db
  - DB_NAME=crm
  - DB_USER=crm
  - DB_PASS=crm
  - DB_PORT=3306
```

### Local Development Fallbacks (Already Set)
```php
define('DB_SERVER', getenv('DB_HOST') ?: 'localhost');
define('DB_SERVER_USERNAME', getenv('DB_USER') ?: 'kylewee');
define('DB_SERVER_PASSWORD', getenv('DB_PASS') ?: 'rainonin');
define('DB_DATABASE', getenv('DB_NAME') ?: 'rukovoditel');
```

**Note:** When running in Docker, environment variables take precedence.

## 4. Optional Features

### OpenAI (for AI Transcription)
**File:** `api/.env.local.php`
```php
const OPENAI_API_KEY = '';  // Add OpenAI API key for Whisper transcription
```

### Email Notifications
**File:** `api/.env.local.php`
```php
const VOICE_EMAIL_NOTIFY_TO = '';  // Notification recipient email
const VOICE_EMAIL_FROM      = '';  // From email address

// SMTP Settings
const VOICE_SMTP_HOST = '';
const VOICE_SMTP_PORT = 587;
const VOICE_SMTP_USERNAME = '';
const VOICE_SMTP_PASSWORD = '';
```

### SendGrid (Alternative to SMTP)
```php
const VOICE_SENDGRID_API_KEY = '';
```

## Summary of What Needs to Be Done

### ✅ Already Configured
- Twilio phone numbers (`+19048349227`, `+19046634789`)
- CRM API URL and API Key
- CRM field mappings (219, 220 for first/last name)
- CRM Leads Entity ID (26)
- Database configuration (Docker environment)

### ⚠️ Action Required
You need to add these Twilio credentials to `api/.env.local.php`:
1. **TWILIO_ACCOUNT_SID** - Get from Twilio Console
2. **TWILIO_AUTH_TOKEN** - Get from Twilio Console

### 🔧 Optional Configuration
- OpenAI API key (for transcription)
- Email/SMTP settings (for notifications)
- CRM username/password (if required by your setup)

## After Updating Credentials

1. Save the `api/.env.local.php` file
2. Restart Docker containers:
   ```bash
   docker compose -f docker-compose.yml restart
   ```
3. Test the quote form at http://localhost:8080
4. Check logs for any errors:
   ```bash
   docker compose -f docker-compose.yml logs -f
   ```

## Security Notes

- The file `api/.env.local.php` is gitignored and will not be committed
- Never commit credentials to version control
- Keep backup copies of your credentials in a secure location
- The CRM API key and database passwords should be rotated periodically

## Verification

After adding Twilio credentials, test these features:
- Submit a quote with SMS opt-in enabled
- Make a test call to your Twilio number
- Check that call recording works
- Verify SMS notifications are sent

## Support

If you encounter issues:
1. Check the logs: `tail -f api/quote_intake.log` and `tail -f voice/voice.log`
2. Verify Twilio webhook URLs in Twilio Console
3. Ensure all required constants are defined in `api/.env.local.php`
