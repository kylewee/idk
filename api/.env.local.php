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
define('TWILIO_FORWARD_TO', Config::get('TWILIO_FORWARD_TO'));
define('TWILIO_ACCOUNT_SID', Config::get('TWILIO_ACCOUNT_SID'));
define('TWILIO_AUTH_TOKEN', Config::get('TWILIO_AUTH_TOKEN'));
define('TWILIO_SMS_FROM', Config::get('TWILIO_SMS_FROM'));
define('CRM_API_URL', Config::get('CRM_API_URL'));
define('CRM_API_KEY', Config::get('CRM_API_KEY'));
define('CRM_LEADS_ENTITY_ID', Config::getInt('CRM_LEADS_ENTITY_ID'));
define('CRM_USERNAME', Config::get('CRM_USERNAME'));
define('CRM_PASSWORD', Config::get('CRM_PASSWORD'));
define('CRM_FIELD_MAP', Config::getArray('CRM_FIELD_MAP'));
define('OPENAI_API_KEY', Config::get('OPENAI_API_KEY'));
define('VOICE_RECORDINGS_TOKEN', Config::get('VOICE_RECORDINGS_TOKEN'));
define('TWILIO_TRANSCRIBE_ENABLED', Config::getBool('TWILIO_TRANSCRIBE_ENABLED', true));
define('CRM_CREATED_BY_USER_ID', Config::getInt('CRM_CREATED_BY_USER_ID', 1));
