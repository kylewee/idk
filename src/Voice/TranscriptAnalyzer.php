<?php

namespace MechanicStAugustine\Voice;

/**
 * Transcript Analyzer
 *
 * Analyzes voice call transcripts using AI (OpenAI) or pattern matching
 * to extract customer information.
 */
class TranscriptAnalyzer
{
    private $openaiApiKey;

    public function __construct(?string $openaiApiKey = null)
    {
        $this->openaiApiKey = $openaiApiKey ?? config('openai.api_key');
    }

    /**
     * Extract customer data from transcript using AI or patterns
     *
     * @param string $transcript The transcript text
     * @return array Extracted customer data
     */
    public function extractCustomerData(string $transcript): array
    {
        if (!empty($this->openaiApiKey)) {
            return $this->extractWithAI($transcript);
        }

        return $this->extractWithPatterns($transcript);
    }

    /**
     * Extract customer data using OpenAI GPT
     *
     * @param string $transcript The transcript text
     * @return array Extracted customer data
     */
    private function extractWithAI(string $transcript): array
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

        $payload = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 300,
            'temperature' => 0.1
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->openaiApiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($payload)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode !== 200) {
            error_log("VOICE_AI: OpenAI API error - HTTP $httpCode: $error");
            return $this->extractWithPatterns($transcript);
        }

        $data = json_decode($response, true);
        if (!$data || !isset($data['choices'][0]['message']['content'])) {
            error_log("VOICE_AI: Invalid OpenAI response format");
            return $this->extractWithPatterns($transcript);
        }

        $aiResponse = trim($data['choices'][0]['message']['content']);
        $extracted = json_decode($aiResponse, true);

        if (!is_array($extracted)) {
            error_log("VOICE_AI: Failed to parse AI response as JSON: " . $aiResponse);
            return $this->extractWithPatterns($transcript);
        }

        return $this->cleanAndValidate($extracted);
    }

    /**
     * Extract customer data using regex patterns (fallback)
     *
     * @param string $transcript The transcript text
     * @return array Extracted customer data
     */
    private function extractWithPatterns(string $transcript): array
    {
        $result = [];

        // Extract phone number
        if (preg_match('/\b(\d{3}[-.\s]?\d{3}[-.\s]?\d{4})\b/', $transcript, $matches)) {
            $phone = preg_replace('/[^\d]/', '', $matches[1]);
            if (strlen($phone) === 10) {
                $result['phone'] = $phone;
            }
        }

        // Extract vehicle year
        if (preg_match('/\b(19\d{2}|20[0-3]\d)\b/', $transcript, $matches)) {
            $result['year'] = $matches[1];
        }

        // Extract common car makes
        $makes = ['honda', 'toyota', 'ford', 'chevrolet', 'chevy', 'nissan', 'dodge', 'jeep', 'ram', 'bmw', 'mercedes', 'audi', 'lexus', 'hyundai', 'kia', 'mazda', 'subaru', 'volkswagen', 'vw'];
        $transcript_lower = strtolower($transcript);
        foreach ($makes as $make) {
            if (stripos($transcript_lower, $make) !== false) {
                $result['make'] = ucfirst($make);
                break;
            }
        }

        // Extract common issues/services
        $services = [
            'oil change' => 'Oil Change',
            'brake' => 'Brake Service',
            'battery' => 'Battery Replacement',
            'alternator' => 'Alternator Replacement',
            'starter' => 'Starter Replacement',
            'check engine' => 'Engine Diagnostic',
            'diagnostic' => 'Engine Diagnostic',
            'timing belt' => 'Timing Belt',
            'ac recharge' => 'AC Recharge',
            'air conditioning' => 'AC Recharge',
            'transmission' => 'Transmission Service',
        ];

        foreach ($services as $keyword => $service) {
            if (stripos($transcript_lower, $keyword) !== false) {
                $result['notes'] = $service;
                break;
            }
        }

        return $result;
    }

    /**
     * Clean and validate extracted data
     *
     * @param array $extracted Raw extracted data
     * @return array Cleaned and validated data
     */
    private function cleanAndValidate(array $extracted): array
    {
        $result = [];

        foreach (['first_name', 'last_name', 'phone', 'address', 'year', 'make', 'model', 'engine', 'notes'] as $field) {
            $value = $extracted[$field] ?? null;
            if ($value && trim($value) && strtolower(trim($value)) !== 'null') {
                $result[$field] = trim($value);
            }
        }

        // Validate phone number format
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

        return $result;
    }
}
