<?php

/**
 * Configuration Loader
 *
 * Loads environment variables from .env file and provides access to configuration values.
 * This replaces the old api/.env.local.php with hardcoded constants.
 */

class Config
{
    private static $instance = null;
    private $config = [];

    private function __construct()
    {
        $this->loadEnv();
        $this->initializeConfig();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadEnv(): void
    {
        $envFile = __DIR__ . '/../.env';

        if (!file_exists($envFile)) {
            throw new RuntimeException('.env file not found. Please copy .env.example to .env and configure it.');
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse key=value
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes if present
                if (preg_match('/^(["\'])(.*)\1$/', $value, $matches)) {
                    $value = $matches[2];
                }

                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }

    private function initializeConfig(): void
    {
        // Twilio Configuration
        $this->config['twilio'] = [
            'account_sid' => $this->env('TWILIO_ACCOUNT_SID'),
            'auth_token' => $this->env('TWILIO_AUTH_TOKEN'),
            'sms_from' => $this->env('TWILIO_SMS_FROM'),
            'forward_to' => $this->env('TWILIO_FORWARD_TO'),
            'transcribe_enabled' => $this->env('TWILIO_TRANSCRIBE_ENABLED', 'true') === 'true',
        ];

        // CRM Configuration
        $this->config['crm'] = [
            'api_url' => $this->env('CRM_API_URL'),
            'api_key' => $this->env('CRM_API_KEY'),
            'username' => $this->env('CRM_USERNAME'),
            'password' => $this->env('CRM_PASSWORD'),
            'leads_entity_id' => (int) $this->env('CRM_LEADS_ENTITY_ID', '26'),
            'created_by_user_id' => (int) $this->env('CRM_CREATED_BY_USER_ID', '1'),
            'field_map' => json_decode($this->env('CRM_FIELD_MAP', '{}'), true),
        ];

        // Database Configuration
        $this->config['database'] = [
            'server' => $this->env('DB_SERVER', 'localhost'),
            'username' => $this->env('DB_SERVER_USERNAME'),
            'password' => $this->env('DB_SERVER_PASSWORD'),
            'port' => $this->env('DB_SERVER_PORT', ''),
            'database' => $this->env('DB_DATABASE', 'rukovoditel'),
        ];

        // OpenAI Configuration
        $this->config['openai'] = [
            'api_key' => $this->env('OPENAI_API_KEY'),
        ];

        // Voice Configuration
        $this->config['voice'] = [
            'recordings_token' => $this->env('VOICE_RECORDINGS_TOKEN'),
        ];
    }

    private function env(string $key, ?string $default = null): ?string
    {
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }

        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        if ($default !== null) {
            return $default;
        }

        throw new RuntimeException("Environment variable '$key' is not set.");
    }

    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public function all(): array
    {
        return $this->config;
    }
}

// Helper function for easy access
function config(string $key = null, $default = null)
{
    $config = Config::getInstance();

    if ($key === null) {
        return $config;
    }

    return $config->get($key, $default);
}

// Legacy constants support for backward compatibility
// These will be removed in a future refactoring
if (!defined('TWILIO_ACCOUNT_SID')) {
    define('TWILIO_ACCOUNT_SID', config('twilio.account_sid'));
    define('TWILIO_AUTH_TOKEN', config('twilio.auth_token'));
    define('TWILIO_SMS_FROM', config('twilio.sms_from'));
    define('TWILIO_FORWARD_TO', config('twilio.forward_to'));
    define('TWILIO_TRANSCRIBE_ENABLED', config('twilio.transcribe_enabled'));

    define('CRM_API_URL', config('crm.api_url'));
    define('CRM_API_KEY', config('crm.api_key'));
    define('CRM_USERNAME', config('crm.username'));
    define('CRM_PASSWORD', config('crm.password'));
    define('CRM_LEADS_ENTITY_ID', config('crm.leads_entity_id'));
    define('CRM_CREATED_BY_USER_ID', config('crm.created_by_user_id'));
    define('CRM_FIELD_MAP', config('crm.field_map'));

    define('OPENAI_API_KEY', config('openai.api_key'));
    define('VOICE_RECORDINGS_TOKEN', config('voice.recordings_token'));

    define('DB_SERVER', config('database.server'));
    define('DB_SERVER_USERNAME', config('database.username'));
    define('DB_SERVER_PASSWORD', config('database.password'));
    define('DB_SERVER_PORT', config('database.port'));
    define('DB_DATABASE', config('database.database'));
}
