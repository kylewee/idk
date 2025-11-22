# API Documentation

This document describes the API endpoints available in the Mechanic Saint Augustine system.

## Table of Contents

- [Quote Intake API](#quote-intake-api)
- [Voice Webhook API](#voice-webhook-api)
- [Recording Callback API](#recording-callback-api)
- [Shared Libraries](#shared-libraries)

---

## Quote Intake API

### POST /quote/quote_intake_handler.php

Submit a quote request from a customer.

#### Request

**Headers:**
```
Content-Type: application/json
```

**Body:**
```json
{
  "name": "John Doe",
  "phone": "904-555-0123",
  "email": "john@example.com",
  "year": 2015,
  "make": "Toyota",
  "model": "Camry",
  "engine": "4-cylinder",
  "repair": "Oil Change",
  "service": "Oil Change",
  "zip": "32080",
  "sms_opt_in": true,
  "preferred_slot": "morning"
}
```

**Required Fields:**
- `name` (string): Customer name
- `phone` (string): Customer phone number

**Optional Fields:**
- `email` (string): Customer email
- `year` (integer): Vehicle year
- `make` (string): Vehicle make
- `model` (string): Vehicle model
- `engine` (string): Engine type
- `repair` (string): Repair description
- `service` (string): Service description (alias for repair)
- `zip` (string): Zip code
- `sms_opt_in` (boolean): Whether to send SMS quote
- `preferred_slot` (string): Preferred appointment time

#### Response

**Success (200 OK):**
```json
{
  "success": true,
  "message": "Quote request received",
  "data": {
    "name": "John Doe",
    "phone": "+19045550123",
    "email": "john@example.com",
    "vehicle": {
      "year": 2015,
      "make": "Toyota",
      "model": "Camry",
      "engine": "4-cylinder"
    },
    "repair": "Oil Change",
    "estimate": {
      "amount": 50,
      "source": "local_matrix",
      "candidates": {
        "local": {
          "amount": 50,
          "source": "local_matrix",
          "base_price": 50,
          "multiplier": 1.0,
          "repair_key": "oil change",
          "repair_name": "Oil Change",
          "estimated_time": 0.5
        }
      }
    },
    "crm": {
      "status": "success",
      "http_code": 200,
      "item_id": 123
    },
    "sms": {
      "status": "success",
      "http_code": 200
    }
  }
}
```

**Error (400 Bad Request):**
```json
{
  "error": "Missing required field: name"
}
```

**Error (405 Method Not Allowed):**
```json
{
  "error": "Method not allowed"
}
```

#### Behavior

1. Validates required fields (name, phone)
2. Attempts to get price estimate from remote API (if available)
3. Falls back to local pricing matrix from `price-catalog.json`
4. Creates lead in CRM (if configured)
5. Sends SMS quote to customer (if opted in)
6. Logs request details

#### Price Estimation

The API uses a two-tier estimation system:

1. **Remote Estimate**: Calls internal estimate API (if available)
2. **Local Fallback**: Uses pricing from `price-catalog.json` with automatic multipliers:
   - **V8 engines**: Apply V8 multiplier from catalog
   - **Old vehicles** (pre-2000): Apply old_car multiplier from catalog

---

## Voice Webhook API

### POST /voice/incoming.php

Twilio webhook for handling incoming voice calls.

#### Request

**Content-Type:** `application/x-www-form-urlencoded`

**Parameters** (sent by Twilio):
- `CallSid`: Unique call identifier
- `From`: Caller's phone number
- `To`: Dialed phone number
- `CallStatus`: Current call status

#### Response

Returns TwiML (Twilio Markup Language) XML:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<Response>
  <Say>Thank you for calling Mechanics Saint Augustine. Please leave a message after the beep.</Say>
  <Record
    maxLength="120"
    transcribe="true"
    recordingStatusCallback="/voice/recording_callback.php"
  />
  <Say>Thank you. We'll get back to you soon.</Say>
</Response>
```

#### Behavior

1. Greets the caller
2. Records message (max 2 minutes)
3. Enables Twilio transcription
4. Triggers recording callback when done

---

## Recording Callback API

### POST /voice/recording_callback.php

Processes recorded calls, extracts information, and creates CRM leads.

#### Request

**Content-Type:** `application/x-www-form-urlencoded`

**Parameters** (sent by Twilio):
- `RecordingSid`: Recording identifier
- `RecordingUrl`: URL to recording file
- `RecordingDuration`: Length in seconds
- `CallSid`: Associated call identifier
- `From`: Caller's phone number
- `TranscriptionText`: Transcribed text (if available)

#### Response

**Success:**
```json
{
  "status": "success",
  "message": "Recording processed",
  "data": {
    "call_sid": "CAxxxx",
    "recording_sid": "RExxxx",
    "transcription": "I need an oil change for my 2015 Toyota Camry",
    "extracted_info": {
      "name": "John Doe",
      "phone": "+19045550123",
      "repair": "oil change",
      "vehicle": "2015 Toyota Camry"
    },
    "crm_lead_id": 123
  }
}
```

#### Behavior

1. Downloads recording from Twilio
2. Transcribes using Twilio native transcription or OpenAI
3. Analyzes transcription to extract:
   - Customer name
   - Contact information
   - Vehicle details
   - Service needed
4. Creates lead in CRM
5. Logs all details

---

## Shared Libraries

The system includes reusable PHP libraries for common operations.

### Common Utils (`lib/common_utils.php`)

#### `to_bool($value): bool`

Converts various opt-in values to boolean.

```php
to_bool('yes');     // true
to_bool('1');       // true
to_bool('on');      // true
to_bool('false');   // false
to_bool(0);         // false
```

#### `split_name(string $fullName): array`

Splits full name into first and last.

```php
$name = split_name('John Doe');
// ['first' => 'John', 'last' => 'Doe', 'full' => 'John Doe']
```

#### `log_structured(string $logFile, string $level, string $type, array $data): bool`

Structured logging to file.

```php
log_structured('api.log', 'info', 'quote_request', [
  'name' => 'John Doe',
  'amount' => 50
]);
```

### Phone Utils (`lib/phone_utils.php`)

#### `normalize_phone($value): ?string`

Normalizes phone number to E.164 format.

```php
normalize_phone('(904) 555-0123');  // '+19045550123'
normalize_phone('904-555-0123');    // '+19045550123'
normalize_phone('+1 904 555 0123'); // '+19045550123'
```

#### `format_phone_display(string $e164): string`

Formats E.164 phone for display.

```php
format_phone_display('+19045550123');  // '(904) 555-0123'
```

### Estimate Utils (`lib/estimate_utils.php`)

#### `load_price_catalog(): ?array`

Loads pricing catalog from JSON file (cached).

```php
$catalog = load_price_catalog();
```

#### `get_local_estimate(array $lead): ?array`

Gets price estimate from local catalog.

```php
$estimate = get_local_estimate([
  'repair' => 'Oil Change',
  'year' => 2015,
  'engine' => '4-cylinder'
]);
// ['amount' => 50, 'source' => 'local_matrix', ...]
```

#### `extract_remote_estimate($estimate): ?array`

Extracts estimate from remote API response.

```php
$estimate = extract_remote_estimate($apiResponse);
```

#### `build_estimate_summary($remoteEstimate, array $lead): array`

Combines remote and local estimates.

```php
$summary = build_estimate_summary($remoteResponse, $leadData);
// Prefers remote, falls back to local
```

#### `format_price(float $amount, bool $includeDecimals = false): string`

Formats price for display.

```php
format_price(50);        // '$50'
format_price(50.5, true); // '$50.50'
```

### Twilio Utils (`lib/twilio_utils.php`)

#### `send_twilio_sms(string $to, string $body, array $options = []): array`

Sends SMS via Twilio.

```php
$result = send_twilio_sms('+19045550123', 'Your quote is ready!', [
  'from' => '+19048340000'
]);
```

#### `send_quote_sms(array $lead, array $estimateSummary): array`

Sends formatted quote SMS to customer.

```php
$result = send_quote_sms(
  ['name' => 'John', 'phone' => '+19045550123', 'repair' => 'Oil Change'],
  ['amount' => 50]
);
```

#### `twilio_api_request(string $endpoint, array $data = [], string $method = 'POST'): array`

Makes authenticated Twilio API request.

```php
$result = twilio_api_request('Calls.json', [
  'To' => '+19045550123',
  'From' => '+19048340000',
  'Url' => 'https://example.com/twiml.xml'
]);
```

---

## Error Handling

All API endpoints return appropriate HTTP status codes:

- **200 OK**: Request successful
- **400 Bad Request**: Invalid input or missing required fields
- **405 Method Not Allowed**: Wrong HTTP method
- **500 Internal Server Error**: Server-side error

Error responses include descriptive messages:

```json
{
  "error": "Description of what went wrong",
  "detail": "Additional context if available"
}
```

---

## Rate Limiting

Currently, there is no rate limiting implemented. For production use, consider:

- Implementing rate limiting per IP address
- Adding CAPTCHA to quote forms
- Using Twilio's built-in fraud detection

---

## Security Considerations

1. **Input Validation**: All user input is sanitized and validated
2. **SQL Injection**: CRM integration uses parameterized queries
3. **XSS Protection**: Output is escaped when rendered
4. **CORS**: Configured to allow necessary origins only
5. **Credentials**: Never exposed in responses or logs
6. **HTTPS**: All production traffic should use HTTPS

---

## Webhooks Configuration

### Twilio Webhooks

Configure in Twilio Console → Phone Numbers → Your Number:

| Webhook | URL | Method |
|---------|-----|--------|
| Voice Incoming | `https://yourdomain.com/voice/incoming.php` | POST |
| Voice Status Callback | `https://yourdomain.com/voice/recording_callback.php` | POST |
| Messaging Incoming | `https://yourdomain.com/quote/quote_intake_handler.php` | POST |

### Testing Webhooks Locally

Use ngrok for local testing:

```bash
ngrok http 8080

# Use the ngrok URL in Twilio console:
# https://abc123.ngrok.io/voice/incoming.php
```

---

## Change Log

### Version 1.0.0

- Initial API documentation
- Quote intake endpoint
- Voice webhook handlers
- Shared utility libraries
- CRM integration
- SMS notifications

---

## Support

For API questions or issues:
- Review this documentation
- Check logs in `api/` and `voice/` directories
- Verify environment configuration in `api/.env.local.php`
- Test with tools like Postman or curl

Example curl test:

```bash
curl -X POST http://localhost:8080/quote/quote_intake_handler.php \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "phone": "904-555-0123",
    "repair": "Oil Change"
  }'
```
