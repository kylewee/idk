<?php

declare(strict_types=1);

require_once __DIR__ . '/../Config.php';

/**
 * OpenAI Service
 *
 * Handles all interactions with OpenAI API
 * Provides methods for transcription, chat completion, and data extraction
 *
 * @package MechanicStAugustine\Services
 */
class OpenAiService
{
    private Config $config;
    private string $apiKey;

    public function __construct(?Config $config = null)
    {
        $this->config = $config ?? Config::getInstance();

        $openaiConfig = $this->config::openai();
        $this->apiKey = $openaiConfig['api_key'];
    }

    /**
     * Transcribe audio using Whisper API
     *
     * @param string $audioFilePath Path to audio file
     * @param string $language Language code (optional)
     * @return array|null Transcription result or null on failure
     */
    public function transcribe(string $audioFilePath, ?string $language = null): ?array
    {
        if (!file_exists($audioFilePath)) {
            return null;
        }

        $url = 'https://api.openai.com/v1/audio/transcriptions';

        $postFields = [
            'file' => new CURLFile($audioFilePath),
            'model' => 'whisper-1',
        ];

        if ($language) {
            $postFields['language'] = $language;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($errno || $httpCode !== 200) {
            error_log("OpenAI Whisper API error - HTTP $httpCode: $error");
            return null;
        }

        $data = json_decode($response, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Send chat completion request
     *
     * @param array $messages Array of messages
     * @param string $model Model to use (default: gpt-3.5-turbo)
     * @param int $maxTokens Maximum tokens to generate
     * @param float $temperature Temperature (0.0-2.0)
     * @return array|null Response or null on failure
     */
    public function chatCompletion(
        array $messages,
        string $model = 'gpt-3.5-turbo',
        int $maxTokens = 300,
        float $temperature = 0.1
    ): ?array {
        $url = 'https://api.openai.com/v1/chat/completions';

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($errno || $httpCode !== 200) {
            error_log("OpenAI Chat API error - HTTP $httpCode: $error");
            return null;
        }

        $data = json_decode($response, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Extract customer data from transcript using AI
     *
     * @param string $transcript Call transcript
     * @return array Extracted customer data
     */
    public function extractCustomerData(string $transcript): array
    {
        $prompt = "You are analyzing a phone call transcript for a mobile mechanic service. Extract customer information and return ONLY a JSON object with these exact keys (use null for missing data):

{
  \"first_name\": \"customer's first name\",
  \"last_name\": \"customer's last name\",
  \"phone\": \"phone number in format like 9045551234 (digits only)\",
  \"address\": \"location/address mentioned\",
  \"year\": \"vehicle year (4 digits)\",
  \"make\": \"vehicle make/brand\",
  \"model\": \"vehicle model\",
  \"engine\": \"engine size/type if mentioned\",
  \"notes\": \"problem description or service needed\"
}

Rules:
- Extract actual customer info, not business/agent details
- For phone: digits only, no formatting
- For year: must be 4-digit year between 1990-2030
- For make/model: standardize common brands (Honda, Toyota, etc.)
- For notes: summarize the actual problem/service needed
- Return null for any field that's not clearly stated

Transcript: " . $transcript;

        $messages = [
            ['role' => 'user', 'content' => $prompt]
        ];

        $response = $this->chatCompletion($messages);

        if (!$response || !isset($response['choices'][0]['message']['content'])) {
            error_log("OpenAI: Invalid response format for customer data extraction");
            return [];
        }

        $aiResponse = trim($response['choices'][0]['message']['content']);
        $extracted = json_decode($aiResponse, true);

        if (!is_array($extracted)) {
            error_log("OpenAI: Failed to parse AI response as JSON: " . $aiResponse);
            return [];
        }

        // Clean and validate the AI response
        $result = [];
        $validFields = ['first_name', 'last_name', 'phone', 'address', 'year', 'make', 'model', 'engine', 'notes'];

        foreach ($validFields as $field) {
            $value = $extracted[$field] ?? null;

            if ($value && trim($value) && strtolower(trim($value)) !== 'null') {
                $result[$field] = trim($value);
            }
        }

        // Validate and clean phone number
        if (!empty($result['phone'])) {
            $result['phone'] = preg_replace('/[^\d]/', '', $result['phone']);

            if (strlen($result['phone']) < 10) {
                unset($result['phone']);
            }
        }

        // Validate year
        if (!empty($result['year'])) {
            $year = (int)$result['year'];

            if ($year < 1990 || $year > 2030) {
                unset($result['year']);
            }
        }

        error_log("OpenAI: Extracted customer data: " . json_encode($result));

        return $result;
    }

    /**
     * Check if API key is configured
     *
     * @return bool True if API key is set
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }
}
