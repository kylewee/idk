<?php
/**
 * DEPRECATED: This file is maintained for backward compatibility only.
 * New code should use the Config class directly.
 *
 * @deprecated Use Config class instead (see /src/Config.php)
 */

// Load the Config class
require_once __DIR__ . '/../src/Config.php';

// Define constants from Config for backward compatibility
const TWILIO_FORWARD_TO = Config::get('TWILIO_FORWARD_TO');
const TWILIO_ACCOUNT_SID = Config::get('TWILIO_ACCOUNT_SID');
const TWILIO_AUTH_TOKEN = Config::get('TWILIO_AUTH_TOKEN');
const TWILIO_SMS_FROM = Config::get('TWILIO_SMS_FROM');
const CRM_API_URL = Config::get('CRM_API_URL');
const CRM_API_KEY = Config::get('CRM_API_KEY');
const CRM_LEADS_ENTITY_ID = Config::getInt('CRM_LEADS_ENTITY_ID');
const CRM_USERNAME = Config::get('CRM_USERNAME');
const CRM_PASSWORD = Config::get('CRM_PASSWORD');
const CRM_FIELD_MAP = Config::getArray('CRM_FIELD_MAP');
const OPENAI_API_KEY = Config::get('OPENAI_API_KEY');
const VOICE_RECORDINGS_TOKEN = Config::get('VOICE_RECORDINGS_TOKEN');
const TWILIO_TRANSCRIBE_ENABLED = Config::getBool('TWILIO_TRANSCRIBE_ENABLED', true);

define('CRM_CREATED_BY_USER_ID', Config::getInt('CRM_CREATED_BY_USER_ID', 1));
