<?php

/**
 * Configuration Management Class
 *
 * Loads and manages application configuration from .env file
 * Provides centralized access to all configuration values
 *
 * @package MechanicStAugustine
 */
class Config
{
    private static ?Config $instance = null;
    private array $config = [];
    private bool $loaded = false;

    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct()
    {
        $this->loadEnv();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): Config
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load environment variables from .env file
     */
    private function loadEnv(): void
    {
        if ($this->loaded) {
            return;
        }

        $envPath = $this->findEnvFile();

        if (!$envPath || !file_exists($envPath)) {
            throw new RuntimeException('.env file not found. Please copy .env.example to .env and configure it.');
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments and empty lines
            $line = trim($line);
            if (empty($line) || $line[0] === '#') {
                continue;
            }

            // Parse KEY=VALUE format
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes if present
                if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                    $value = $matches[2];
                }

                $this->config[$key] = $value;

                // Also set in $_ENV and putenv for compatibility
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }

        $this->loaded = true;
        $this->validateRequiredConfig();
    }

    /**
     * Find .env file in project root
     */
    private function findEnvFile(): ?string
    {
        // Try multiple possible locations
        $possiblePaths = [
            __DIR__ . '/../.env',
            dirname(__DIR__) . '/.env',
            $_SERVER['DOCUMENT_ROOT'] . '/../.env',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Validate that all required configuration is present
     */
    private function validateRequiredConfig(): void
    {
        $required = [
            'TWILIO_ACCOUNT_SID',
            'TWILIO_AUTH_TOKEN',
            'TWILIO_SMS_FROM',
            'CRM_API_URL',
            'CRM_API_KEY',
            'CRM_USERNAME',
            'CRM_PASSWORD',
            'CRM_LEADS_ENTITY_ID',
            'DB_SERVER',
            'DB_SERVER_USERNAME',
            'DB_SERVER_PASSWORD',
            'DB_DATABASE',
        ];

        $missing = [];
        foreach ($required as $key) {
            if (empty($this->config[$key])) {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            throw new RuntimeException(
                'Missing required configuration: ' . implode(', ', $missing)
            );
        }
    }

    /**
     * Get configuration value
     *
     * @param string $key Configuration key
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value
     */
    public static function get(string $key, $default = null)
    {
        $instance = self::getInstance();
        return $instance->config[$key] ?? $default;
    }

    /**
     * Get configuration value as boolean
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        $value = self::get($key);

        if ($value === null) {
            return $default;
        }

        $value = strtolower(trim($value));
        return in_array($value, ['true', '1', 'yes', 'on'], true);
    }

    /**
     * Get configuration value as integer
     */
    public static function getInt(string $key, int $default = 0): int
    {
        $value = self::get($key);
        return $value !== null ? (int)$value : $default;
    }

    /**
     * Get configuration value as array (from JSON)
     */
    public static function getArray(string $key, array $default = []): array
    {
        $value = self::get($key);

        if ($value === null) {
            return $default;
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : $default;
    }

    /**
     * Check if configuration key exists
     */
    public static function has(string $key): bool
    {
        $instance = self::getInstance();
        return isset($instance->config[$key]);
    }

    /**
     * Get all configuration as array
     */
    public static function all(): array
    {
        $instance = self::getInstance();
        return $instance->config;
    }

    // Convenience methods for specific configuration groups

    /**
     * Get Twilio configuration
     */
    public static function twilio(): array
    {
        return [
            'account_sid' => self::get('TWILIO_ACCOUNT_SID'),
            'auth_token' => self::get('TWILIO_AUTH_TOKEN'),
            'sms_from' => self::get('TWILIO_SMS_FROM'),
            'forward_to' => self::get('TWILIO_FORWARD_TO'),
            'transcribe_enabled' => self::getBool('TWILIO_TRANSCRIBE_ENABLED', true),
        ];
    }

    /**
     * Get OpenAI configuration
     */
    public static function openai(): array
    {
        return [
            'api_key' => self::get('OPENAI_API_KEY'),
        ];
    }

    /**
     * Get CRM configuration
     */
    public static function crm(): array
    {
        return [
            'api_url' => self::get('CRM_API_URL'),
            'api_key' => self::get('CRM_API_KEY'),
            'username' => self::get('CRM_USERNAME'),
            'password' => self::get('CRM_PASSWORD'),
            'leads_entity_id' => self::getInt('CRM_LEADS_ENTITY_ID'),
            'created_by_user_id' => self::getInt('CRM_CREATED_BY_USER_ID', 1),
            'field_map' => self::getArray('CRM_FIELD_MAP'),
        ];
    }

    /**
     * Get Database configuration
     */
    public static function database(): array
    {
        return [
            'server' => self::get('DB_SERVER'),
            'username' => self::get('DB_SERVER_USERNAME'),
            'password' => self::get('DB_SERVER_PASSWORD'),
            'port' => self::get('DB_SERVER_PORT', ''),
            'database' => self::get('DB_DATABASE'),
        ];
    }

    /**
     * Get Voice/Recording configuration
     */
    public static function voice(): array
    {
        return [
            'recordings_token' => self::get('VOICE_RECORDINGS_TOKEN'),
            'recordings_password' => self::get('VOICE_RECORDINGS_PASSWORD'),
        ];
    }
}
