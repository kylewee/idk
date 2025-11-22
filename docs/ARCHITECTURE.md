# Architecture Documentation

## System Overview

Mechanic Saint Augustine is a multi-tier web application that integrates customer-facing forms, voice call processing, and CRM management into a unified lead generation and management system.

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENT LAYER                              │
├─────────────────────────────────────────────────────────────────┤
│  Landing Page    │    Quote Form    │    Phone Call             │
│  (index.html)    │  (quote/index)   │  (Twilio Number)          │
└────────┬─────────┴────────┬──────────┴─────────┬────────────────┘
         │                  │                     │
         │                  ▼                     ▼
         │         ┌────────────────┐    ┌───────────────┐
         │         │ Quote Handler  │    │Voice Incoming │
         │         │   (PHP)        │    │    (TwiML)    │
         │         └───────┬────────┘    └───────┬───────┘
         │                 │                     │
         │                 │                     ▼
         │                 │            ┌────────────────┐
         │                 │            │   Recording    │
         │                 │            │   Callback     │
         │                 │            └───────┬────────┘
         │                 │                    │
         ▼                 ▼                    ▼
┌─────────────────────────────────────────────────────────────────┐
│                      SERVICE LAYER                               │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────┐  ┌──────────────┐  ┌─────────────────────┐    │
│  │   Config    │  │  CrmService  │  │  TwilioService      │    │
│  │   Manager   │  │              │  │                     │    │
│  └─────────────┘  └──────────────┘  └─────────────────────┘    │
│  ┌─────────────────────────┐  ┌────────────────────────────┐   │
│  │   OpenAiService         │  │ CustomerDataExtractor      │   │
│  │   - GPT Integration     │  │ - AI Extraction            │   │
│  │   - Whisper API         │  │ - Pattern Matching         │   │
│  └─────────────────────────┘  └────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
         │                 │                    │
         ▼                 ▼                    ▼
┌─────────────────────────────────────────────────────────────────┐
│                    INTEGRATION LAYER                             │
├─────────────────────────────────────────────────────────────────┤
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────────┐    │
│  │  Twilio  │  │  OpenAI  │  │   CRM    │  │   Database   │    │
│  │   API    │  │   API    │  │ REST API │  │   (MariaDB)  │    │
│  └──────────┘  └──────────┘  └──────────┘  └──────────────┘    │
└─────────────────────────────────────────────────────────────────┘
```

## Component Details

### 1. Configuration Management (`src/Config.php`)

**Purpose**: Centralized configuration loading and validation

**Features**:
- Loads environment variables from `.env` file
- Validates required configuration on startup
- Provides type-safe getters (getBool, getInt, getArray)
- Singleton pattern for global access
- Convenience methods for grouped config (twilio(), crm(), database())

**Key Methods**:
```php
Config::get('KEY', 'default')
Config::getBool('TWILIO_TRANSCRIBE_ENABLED')
Config::getInt('CRM_LEADS_ENTITY_ID')
Config::getArray('CRM_FIELD_MAP')
```

### 2. CRM Service (`src/Services/CrmService.php`)

**Purpose**: Manage all CRM operations

**Responsibilities**:
- Lead creation via REST API
- Authentication and token management
- Direct database fallback when API fails
- Duplicate detection (within 1 hour by phone number)
- Empty field updates on existing leads
- Field mapping resolution

**Key Methods**:
```php
createLead(array $leadData): array
createLeadDbInsert(array $leadData): array
```

**Duplicate Detection**:
- Checks for existing leads with same phone number within last hour
- Updates empty fields on duplicates instead of creating new lead
- Returns existing lead ID with `duplicate: true` flag

**Fallback Strategy**:
1. Try REST API with token auth
2. If token fails, try API key auth
3. If API fails (timeout, 5xx error), fall back to direct DB insert
4. Direct DB insert handles all field mapping and constraints

### 3. Twilio Service (`src/Services/TwilioService.php`)

**Purpose**: Handle Twilio communications

**Responsibilities**:
- Send SMS messages
- Retrieve recording details and audio
- Generate TwiML responses
- Phone number validation and normalization
- E.164 format conversion

**Key Methods**:
```php
sendSms(string $to, string $body): array
getRecording(string $recordingSid): ?array
downloadRecording(string $recordingSid): ?string
normalizePhoneNumber(string $phone): string
generateDialTwiml(string $forwardTo, ?string $callback): string
```

**Phone Number Handling**:
- Normalizes to E.164 format (+1234567890)
- Validates 10-11 digit US numbers
- Auto-adds +1 for 10-digit numbers

### 4. OpenAI Service (`src/Services/OpenAiService.php`)

**Purpose**: AI-powered transcription and data extraction

**Responsibilities**:
- Audio transcription via Whisper API
- Customer data extraction via GPT-3.5-turbo
- Chat completion requests
- Response validation and parsing

**Key Methods**:
```php
transcribe(string $audioFilePath): ?array
chatCompletion(array $messages, string $model): ?array
extractCustomerData(string $transcript): array
```

**Data Extraction Prompt**:
- Structured JSON output
- Fields: first_name, last_name, phone, address, year, make, model, engine, notes
- Built-in validation rules
- Fallback to pattern matching on failure

### 5. Customer Data Extractor (`src/Services/CustomerDataExtractor.php`)

**Purpose**: Extract customer information from unstructured text

**Strategies**:
1. **AI Extraction** (primary): Uses OpenAI GPT to extract structured data
2. **Pattern Matching** (fallback): Regex-based extraction

**Pattern Matching Features**:
- Labeled field extraction ("name: John Smith")
- Natural language parsing ("my name is John")
- Combined name splitting
- Phone number extraction (any format)
- Email extraction
- Year validation (1990-2030)

**Key Methods**:
```php
extract(string $transcript): array
extractWithPatterns(string $transcript): array
validate(array $data): array
```

## Data Flow

### Quote Intake Flow

```
1. Customer fills form (quote/index.html)
   ↓
2. JavaScript validation
   ↓
3. POST to quote_intake_handler.php
   ↓
4. Load Config and validate input
   ↓
5. Estimate price from price-catalog.json
   ↓
6. Create CRM lead (CrmService)
   ├─→ Try REST API
   └─→ Fallback to DB insert
   ↓
7. Send SMS quote (TwilioService)
   ↓
8. Return success response
```

### Voice Call Flow

```
1. Inbound call to Twilio number
   ↓
2. Twilio webhook → voice/incoming.php
   ↓
3. Generate TwiML (TwilioService)
   ├─→ Forward to business phone
   ├─→ Enable recording
   └─→ Set callback URL
   ↓
4. Call completes, recording ready
   ↓
5. Twilio webhook → voice/recording_callback.php
   ↓
6. Process recording
   ├─→ Get transcript (Twilio native or Whisper)
   ├─→ Extract customer data (CustomerDataExtractor)
   │   ├─→ Try AI extraction (OpenAiService)
   │   └─→ Fallback to patterns
   ├─→ Create CRM lead (CrmService)
   └─→ Send email notification
```

## Database Schema

### CRM Leads Table

Table: `app_entity_{CRM_LEADS_ENTITY_ID}` (e.g., `app_entity_26`)

**Standard Columns**:
- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `created_by` (INT) - User ID who created the lead
- `date_added` (INT) - Unix timestamp of creation
- `date_updated` (INT, NULLABLE) - Last update timestamp
- `parent_item_id` (INT) - For hierarchical entities
- `sort_order` (INT) - Display ordering

**Custom Field Columns**:
- `field_{id}` (VARCHAR/TEXT/INT) - Mapped from CRM_FIELD_MAP

**Example Field Mapping**:
```
field_219 → First Name
field_220 → Last Name
field_227 → Phone
field_230 → Notes
field_231 → Vehicle Year
field_232 → Vehicle Make
field_233 → Vehicle Model
field_234 → Address
```

## Configuration

### Environment Variables

**Required**:
```
TWILIO_ACCOUNT_SID
TWILIO_AUTH_TOKEN
TWILIO_SMS_FROM
CRM_API_URL
CRM_API_KEY
CRM_USERNAME
CRM_PASSWORD
CRM_LEADS_ENTITY_ID
DB_SERVER
DB_SERVER_USERNAME
DB_SERVER_PASSWORD
DB_DATABASE
```

**Optional**:
```
TWILIO_FORWARD_TO
TWILIO_TRANSCRIBE_ENABLED
OPENAI_API_KEY
VOICE_RECORDINGS_TOKEN
VOICE_RECORDINGS_PASSWORD
CRM_CREATED_BY_USER_ID
CRM_FIELD_MAP
```

### Field Mapping Configuration

Field mapping is stored as JSON in `CRM_FIELD_MAP`:

```json
{
  "first_name": 219,
  "last_name": 220,
  "phone": 227,
  "address": 234,
  "year": 231,
  "make": 232,
  "model": 233,
  "notes": 230
}
```

To find field IDs:
1. Log into Rukovoditel CRM as admin
2. Go to Settings → Entities → Leads
3. Edit each field to see its ID in the URL

## Security Architecture

### Credential Management

**Before Refactoring**:
- ❌ Hardcoded secrets in PHP files
- ❌ Committed to version control
- ❌ No secret rotation

**After Refactoring**:
- ✅ All secrets in `.env` (gitignored)
- ✅ Config class validates on startup
- ✅ Backward-compatible wrappers for legacy code
- ✅ `.env.example` for documentation

### Input Validation

**Phone Numbers**:
- Strip all non-digit characters except +
- Validate length (10-15 digits)
- Normalize to E.164 format

**Vehicle Year**:
- Must be 4-digit integer
- Range: 1990-2030

**SQL Injection Prevention**:
- All queries use prepared statements
- Bound parameters for all user input
- Table names validated against known patterns

### Duplicate Prevention

**Strategy**: Check for existing leads within 1 hour by phone number

**Benefits**:
- Prevents spam/duplicate submissions
- Updates incomplete leads with new data
- Maintains data quality

## Error Handling

### Logging Strategy

**Locations**:
- `voice/voice.log` - Voice system events (JSON format)
- `quote/quote_intake.log` - Quote intake events (JSON format)
- PHP error_log - PHP errors and warnings

**Log Rotation**:
- Manually managed (no auto-rotation configured)
- Recommended: Configure logrotate

**Log Format** (voice.log):
```json
{
  "timestamp": "2025-01-15T10:30:00Z",
  "event": "recording_processed",
  "call_sid": "CA...",
  "recording_sid": "RE...",
  "duration": 45,
  "transcript_length": 250,
  "data_extracted": true,
  "lead_created": true,
  "lead_id": 123
}
```

### Fallback Mechanisms

**CRM API → Database**:
- If REST API fails (network, timeout, 5xx), use direct DB insert
- Logged as `fallback: true` in response

**AI → Pattern Matching**:
- If OpenAI fails (no key, API error, invalid response), use regex patterns
- Logged as fallback attempt

## Performance Considerations

### Caching

**Not Currently Implemented**:
- CRM API tokens (re-authenticated per request)
- Field mapping (resolved per request)

**Recommendations**:
- Cache API tokens (expiry: 1 hour)
- Cache field mapping in session
- Use Redis for distributed caching

### Database Optimization

**Current State**:
- Indexed on `id` (primary key)
- No index on phone field for deduplication
- No index on `date_added`

**Recommendations**:
- Add index on `field_{phone_id}` for faster duplicate checks
- Add index on `date_added` for time-based queries

## Scalability

### Current Limitations

- Single-threaded PHP processing
- No queue system for async operations
- No load balancing
- Single database server

### Scaling Recommendations

**Horizontal Scaling**:
1. Deploy multiple PHP-FPM instances behind Caddy
2. Use Redis for session storage
3. Implement job queue (Beanstalkd, RabbitMQ) for async tasks

**Vertical Scaling**:
1. Increase PHP-FPM worker processes
2. Optimize MySQL/MariaDB configuration
3. Add database read replicas

## Monitoring and Observability

### Current Monitoring

- Manual log file inspection
- Twilio debugger console
- OpenAI usage dashboard

### Recommended Additions

- Application Performance Monitoring (APM): New Relic, DataDog
- Log aggregation: ELK Stack, Splunk
- Uptime monitoring: Pingdom, UptimeRobot
- Error tracking: Sentry, Rollbar

## Future Enhancements

### Short-term

- [ ] Implement proper log rotation
- [ ] Add API rate limiting
- [ ] Create admin dashboard for lead review
- [ ] Add webhook signature verification

### Long-term

- [ ] Migrate to Laravel/Symfony framework
- [ ] Implement job queue system
- [ ] Add comprehensive test suite (PHPUnit)
- [ ] Create mobile app
- [ ] Add payment processing
- [ ] Implement customer portal
