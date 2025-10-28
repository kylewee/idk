<?php

// Twilio Configuration for Voice System  
const TWILIO_FORWARD_TO = '+19046634789';  // Your personal phone
const TWILIO_ACCOUNT_SID = 'AC65690a662f4e1981b24e9a8bd51908e2';  // Your Twilio Account SID
const TWILIO_AUTH_TOKEN = '68bac506884ee9a39838aef3c5bdff71';
const TWILIO_SMS_FROM = '+19048349227';
// Twilio phone number in E.164 format for webhook updates
const TWILIO_PHONE_NUMBER = '+19048349227';
const CRM_API_URL = 'https://mechanicstaugustine.com/crm/api/rest.php';
const CRM_API_KEY = 'VMm87uzSFFyWAWCDzCXEK2AajBbHIOOIwtfhMWbA';

// Set to your actual Leads Entity ID
const CRM_LEADS_ENTITY_ID = 26;
const CRM_USERNAME = 'kylewee2'; 
const CRM_PASSWORD = 'R0ckS0l!d';
// Minimal mapping to get Leads created now with First/Last split.
// We also map 'name' to First Name as a fallback so it "just works".
const CRM_FIELD_MAP = [
  'name'        => 0,    // replace 0s with your real IDs if you have a combined name
  'first_name'  => 219,  // keep if correct
  'last_name'   => 220,  // keep if correct
  'phone'       => 227,  // mapped to Phone
  'address'     => 234,  // mapped to Address
  'year'        => 231,  // mapped to year
  'make'        => 232,  // mapped to Make
  'model'       => 233,  // mapped to model
  'engine_size' => 0,
  'notes'       => 230,  // mapped to notes (textarea_wysiwyg)
];

define('CRM_CREATED_BY_USER_ID', 1); // change to kylewee2's user ID when known

// OpenAI configuration (for Whisper transcription and AI extraction)
const OPENAI_API_KEY = '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';

// Optional: Secure the recordings page with a tokenized link. Leave empty to disable.
// Example usage: https://mechanicstaugustine.com/voice/recording_callback.php?action=recordings&token=YOUR_TOKEN
const VOICE_RECORDINGS_TOKEN = 'msarec-' . '2b7c9f1a5d4e';

// Native Twilio Transcription (simpler than CI)
// Automatically transcribes recordings up to 2 minutes using <Record transcribe="true">
// Transcripts are delivered to recordingStatusCallback with TranscriptionText field
const TWILIO_TRANSCRIBE_ENABLED = true;  // Enable auto-transcription for recordings
