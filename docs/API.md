# API Documentation

## Overview

This document describes all public-facing API endpoints and webhooks for the Mechanic Saint Augustine platform.

## Table of Contents

- [Quote Intake API](#quote-intake-api)
- [Voice Webhooks](#voice-webhooks)
- [CRM REST API](#crm-rest-api)
- [Recording Access](#recording-access)
- [Error Responses](#error-responses)

---

## Quote Intake API

### Submit Quote Request

Submit a new quote request from a customer.

**Endpoint**: `POST /quote/quote_intake_handler.php`

**Content-Type**: `application/x-www-form-urlencoded` or `application/json`

**Request Parameters**:

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | No* | Full name (if first_name and last_name not provided) |
| `first_name` | string | No* | First name |
| `last_name` | string | No* | Last name |
| `phone` | string | Yes | Phone number (any format, will be normalized) |
| `address` | string | No | Service location address |
| `year` | string/int | No | Vehicle year (1990-2030) |
| `make` | string | No | Vehicle make (e.g., Honda, Toyota) |
| `model` | string | No | Vehicle model |
| `engine_size` | string | No | Engine size (e.g., 2.0L) |
| `notes` | string | No | Problem description or service needed |
| `send_sms` | boolean | No | Send SMS quote (default: false) |

*Either `name` OR (`first_name` + `last_name`) is required.

**Example Request (JSON)**:

```json
POST /quote/quote_intake_handler.php
Content-Type: application/json

{
  "first_name": "John",
  "last_name": "Smith",
  "phone": "(904) 555-1234",
  "address": "123 Main St, St Augustine, FL",
  "year": "2020",
  "make": "Honda",
  "model": "Civic",
  "notes": "Check engine light is on, car hesitates on acceleration",
  "send_sms": true
}
```

**Example Request (Form)**:

```http
POST /quote/quote_intake_handler.php
Content-Type: application/x-www-form-urlencoded

name=John+Smith&phone=9045551234&year=2020&make=Honda&model=Civic&notes=Check+engine+light
```

**Success Response**:

```json
HTTP/1.1 200 OK
Content-Type: application/json

{
  "success": true,
  "message": "Quote request submitted successfully",
  "lead_id": 123,
  "estimate": {
    "local": "$85-$150",
    "notes": "Diagnostic inspection included"
  },
  "sms_sent": true
}
```

**Error Response**:

```json
HTTP/1.1 400 Bad Request
Content-Type: application/json

{
  "success": false,
  "error": "validation_failed",
  "details": {
    "phone": "Phone number is required",
    "year": "Year must be between 1990 and 2030"
  }
}
```

---

## Voice Webhooks

### Incoming Call Handler

Handles incoming calls and generates TwiML response.

**Endpoint**: `POST /voice/incoming.php`

**Triggered By**: Twilio when call is received

**Twilio Parameters**:

| Parameter | Description |
|-----------|-------------|
| `CallSid` | Unique call identifier |
| `From` | Caller's phone number |
| `To` | Called number (your Twilio number) |
| `CallStatus` | Call status (ringing, in-progress, etc.) |

**Response**: TwiML XML

```xml
<?xml version="1.0" encoding="UTF-8"?>
<Response>
  <Dial record="record-from-answer"
        recordingStatusCallback="https://yourdomain.com/voice/recording_callback.php"
        recordingStatusCallbackMethod="POST"
        transcribe="true"
        transcribeCallback="https://yourdomain.com/voice/recording_callback.php">
    <Number>+19046634789</Number>
  </Dial>
</Response>
```

### Recording Callback

Processes completed call recordings and creates CRM leads.

**Endpoint**: `POST /voice/recording_callback.php`

**Triggered By**: Twilio when recording is ready

**Twilio Parameters**:

| Parameter | Description |
|-----------|-------------|
| `CallSid` | Unique call identifier |
| `RecordingSid` | Unique recording identifier |
| `RecordingUrl` | URL to download recording |
| `RecordingDuration` | Duration in seconds |
| `TranscriptionText` | Transcribed text (if transcription enabled) |
| `TranscriptionStatus` | Status of transcription |
| `From` | Caller's phone number |
| `To` | Called number |

**Processing Flow**:

1. Validate webhook authenticity (optional)
2. Retrieve transcript (from Twilio or use Whisper)
3. Extract customer data using AI or patterns
4. Create lead in CRM
5. Send email notification
6. Log event to voice.log

**Response**:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<Response>
  <Say>Thank you for your call. We will contact you soon.</Say>
</Response>
```

**No Response Required**: Twilio doesn't wait for response, but returning TwiML is good practice.

### Conversational Intelligence Callback (Optional)

Alternative transcription via Twilio CI.

**Endpoint**: `POST /voice/ci_callback.php`

**Triggered By**: Twilio Conversational Intelligence when transcript is ready

**Response**: HTTP 200 OK (no content required)

---

## CRM REST API

### Create Lead

Create a new lead in the CRM system.

**Endpoint**: `POST /crm/api/rest.php`

**Authentication**: API Key or Token

**Request Parameters**:

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | Must be "insert" |
| `entity_id` | int | Yes | CRM entity ID (e.g., 26 for Leads) |
| `key` | string | No* | API key for authentication |
| `token` | string | No* | Session token (from login) |
| `fields[field_{id}]` | string | Varies | Field values (see CRM field mapping) |

*Either `key` OR `token` is required.

**Example Request**:

```http
POST /crm/api/rest.php
Content-Type: application/x-www-form-urlencoded

action=insert&entity_id=26&key=YOUR_API_KEY&fields[field_219]=John&fields[field_220]=Smith&fields[field_227]=9045551234
```

**Success Response**:

```json
{
  "success": true,
  "id": 123,
  "message": "Record created successfully"
}
```

**Error Response**:

```json
{
  "success": false,
  "error": "authentication_failed",
  "message": "Invalid API key"
}
```

### Login (Get Token)

Authenticate and receive session token.

**Endpoint**: `POST /crm/api/rest.php`

**Parameters**:

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | Must be "login" |
| `username` | string | Yes | CRM username |
| `password` | string | Yes | CRM password |

**Example Request**:

```http
POST /crm/api/rest.php
Content-Type: application/x-www-form-urlencoded

action=login&username=kylewee2&password=YourPassword
```

**Success Response**:

```json
{
  "success": true,
  "token": "abc123def456ghi789",
  "expires_in": 3600
}
```

---

## Recording Access

### List Recordings

View all call recordings (requires authentication).

**Endpoint**: `GET /voice/recording_callback.php?action=recordings`

**Authentication Methods**:

1. **Token-based**: `?action=recordings&token=YOUR_TOKEN`
2. **Password-based**: `?action=recordings&pass=YOUR_PASSWORD`
3. **IP whitelist**: Configure in code

**Response**: HTML page with list of recordings

Each recording shows:
- Call SID and Recording SID
- Caller number (From)
- Called number (To)
- Duration
- Timestamp
- Audio player
- Download link
- Transcript (if available)

### Download Recording

Download raw audio file.

**Endpoint**: `GET /voice/recording_callback.php?action=download&sid={RecordingSid}`

**Authentication**: Same as List Recordings

**Response**: Audio file (MP3 or WAV)

**Headers**:
```
Content-Type: audio/mpeg
Content-Disposition: attachment; filename="recording_{sid}.mp3"
```

---

## Error Responses

All API endpoints follow consistent error response format.

### Standard Error Format

```json
{
  "success": false,
  "error": "error_code",
  "message": "Human-readable error message",
  "details": {
    "field_name": "Specific validation error"
  }
}
```

### Common Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `validation_failed` | 400 | Input validation failed |
| `missing_required_field` | 400 | Required field not provided |
| `invalid_phone_number` | 400 | Phone number format invalid |
| `invalid_year` | 400 | Year out of valid range |
| `authentication_failed` | 401 | Invalid credentials or API key |
| `unauthorized` | 403 | Access denied |
| `not_found` | 404 | Resource not found |
| `rate_limit_exceeded` | 429 | Too many requests |
| `crm_api_failed` | 500 | CRM API returned error |
| `database_error` | 500 | Database operation failed |
| `external_api_error` | 502 | External service (Twilio, OpenAI) failed |

### Validation Errors

**Phone Number**:
```json
{
  "success": false,
  "error": "invalid_phone_number",
  "message": "Phone number must be at least 10 digits"
}
```

**Year**:
```json
{
  "success": false,
  "error": "invalid_year",
  "message": "Year must be between 1990 and 2030"
}
```

**Missing Name**:
```json
{
  "success": false,
  "error": "missing_required_field",
  "message": "Either 'name' or 'first_name' and 'last_name' must be provided"
}
```

---

## Rate Limiting

**Current Status**: Not implemented

**Recommended**:
- 60 requests per minute per IP
- 1000 requests per hour per IP
- Headers: `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `X-RateLimit-Reset`

---

## Webhook Security

### Twilio Webhook Validation

**Recommended**: Validate Twilio webhook signatures

**Implementation**:

```php
use Twilio\Security\RequestValidator;

$validator = new RequestValidator(Config::get('TWILIO_AUTH_TOKEN'));
$signature = $_SERVER['HTTP_X_TWILIO_SIGNATURE'] ?? '';
$url = 'https://yourdomain.com/voice/recording_callback.php';
$params = $_POST;

if (!$validator->validate($signature, $url, $params)) {
    http_response_code(403);
    exit('Forbidden');
}
```

---

## Testing

### Test Quote Submission

```bash
curl -X POST https://mechanicstaugustine.com/quote/quote_intake_handler.php \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Test",
    "last_name": "User",
    "phone": "9045551234",
    "year": "2020",
    "make": "Honda",
    "model": "Civic",
    "notes": "Test quote request"
  }'
```

### Test Twilio Webhook (Local)

```bash
# Install ngrok for local testing
ngrok http 8080

# Update Twilio webhook URL to ngrok URL
# Make a test call to your Twilio number
```

---

## Changelog

### Version 2.0 (2025-01-15)

- Refactored to service-oriented architecture
- Added comprehensive error handling
- Improved validation
- Added environment variable configuration

### Version 1.0 (2024-12-01)

- Initial API implementation
- Basic quote intake
- Voice recording callbacks
