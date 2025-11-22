<?php

declare(strict_types=1);

require_once __DIR__ . '/OpenAiService.php';

/**
 * Customer Data Extractor
 *
 * Extracts customer information from transcripts using AI or pattern matching
 * Provides validation and normalization of extracted data
 *
 * @package MechanicStAugustine\Services
 */
class CustomerDataExtractor
{
    private OpenAiService $openAiService;

    public function __construct(?OpenAiService $openAiService = null)
    {
        $this->openAiService = $openAiService ?? new OpenAiService();
    }

    /**
     * Extract customer data from transcript
     * Tries AI extraction first, falls back to pattern matching
     *
     * @param string $transcript Call transcript
     * @return array Extracted customer data
     */
    public function extract(string $transcript): array
    {
        // Try AI extraction if configured
        if ($this->openAiService->isConfigured()) {
            $data = $this->openAiService->extractCustomerData($transcript);

            if (!empty($data)) {
                return $data;
            }

            error_log("CustomerDataExtractor: AI extraction failed, falling back to patterns");
        }

        // Fallback to pattern-based extraction
        return $this->extractWithPatterns($transcript);
    }

    /**
     * Extract customer data using pattern matching
     *
     * @param string $transcript Call transcript
     * @return array Extracted customer data
     */
    public function extractWithPatterns(string $transcript): array
    {
        $data = [];
        $text = trim($transcript);

        // Define field labels and their mappings
        $labelMap = [
            'first name' => 'first_name',
            'last name' => 'last_name',
            'first' => 'first_name',
            'last' => 'last_name',
            'fname' => 'first_name',
            'lname' => 'last_name',
            'name' => 'name',
            'phone' => 'phone',
            'phone number' => 'phone',
            'address' => 'address',
            'year' => 'year',
            'make' => 'make',
            'model' => 'model',
            'engine size' => 'engine_size',
            'notes' => 'notes',
            'special notes' => 'notes',
        ];

        // Extract labeled fields
        $data = $this->extractLabeledFields($text, $labelMap);

        // Extract natural language patterns
        $data = $this->extractNaturalLanguage($text, $data);

        // Split combined name if needed
        $data = $this->splitCombinedName($data);

        // Extract phone number if not found
        if (empty($data['phone']) || !$this->hasValidPhone($data['phone'])) {
            $phone = $this->extractPhoneNumber($text);
            if ($phone) {
                $data['phone'] = $phone;
            }
        }

        // Extract email if present
        $email = $this->extractEmail($text);
        if ($email) {
            $data['email'] = $email;
        }

        // Extract year if not found
        if (empty($data['year'])) {
            $year = $this->extractYear($text);
            if ($year) {
                $data['year'] = $year;
            }
        }

        return $data;
    }

    /**
     * Extract fields with explicit labels
     *
     * @param string $text Transcript text
     * @param array $labelMap Label to field mapping
     * @return array Extracted data
     */
    private function extractLabeledFields(string $text, array $labelMap): array
    {
        $data = [];
        $labelPhrases = array_keys($labelMap);

        foreach ($labelMap as $label => $field) {
            // Build lookahead for next label or punctuation
            $otherLabels = array_filter($labelPhrases, fn($k) => $k !== $label);
            $otherLabelsQuoted = array_map(fn($s) => preg_quote($s, '/'), $otherLabels);
            $lookahead = '(?=(?:\b(?:' . implode('|', $otherLabelsQuoted) . ')\b)|\s*\bok\b|[\.;,\n\r]|$)';

            $pattern = '/\b' . preg_quote($label, '/') . '\b\s*(?:is\s*)?[:\-]?\s*(.*?)\s*' . $lookahead . '/i';

            if (preg_match($pattern, $text, $matches)) {
                $value = trim($matches[1]);

                // Remove common filler words
                $value = preg_replace('/^(is|it|the|a|an)\s+/i', '', $value);

                // Special handling for year
                if ($field === 'year' && preg_match('/\b(19|20)\d{2}\b/', $value, $yearMatch)) {
                    $value = $yearMatch[0];
                }

                // Title case for names
                if (in_array($field, ['first_name', 'last_name', 'name'])) {
                    $value = $this->titleCase($value);
                }

                // Normalize phone
                if ($field === 'phone') {
                    $value = preg_replace('/[^\d\+]/', '', $value);
                }

                $data[$field] = $value;
            }
        }

        return $data;
    }

    /**
     * Extract natural language patterns (e.g., "my name is John Smith")
     *
     * @param string $text Transcript text
     * @param array $data Existing data
     * @return array Updated data
     */
    private function extractNaturalLanguage(string $text, array $data): array
    {
        if (empty($data['name'])) {
            $pattern = '/\b(?:my\s+name\s+is|this\s+is|i\s*am|i\'m|it\'s)\s+([a-z][a-z\-\']+)(?:\s+([a-z][a-z\-\']+))?/i';

            if (preg_match($pattern, $text, $matches)) {
                $firstName = $this->titleCase($matches[1] ?? '');
                $lastName = $this->titleCase($matches[2] ?? '');

                // Check if second token is a location preposition
                if ($lastName && preg_match('/^(from|in|at|of)$/i', $lastName)) {
                    $lastName = '';
                }

                if ($firstName && $lastName) {
                    $data['first_name'] = $firstName;
                    $data['last_name'] = $lastName;
                    $data['name'] = "$firstName $lastName";
                } elseif ($firstName) {
                    $data['first_name'] = $firstName;
                    $data['name'] = $firstName;
                }
            }
        }

        return $data;
    }

    /**
     * Split combined name into first and last
     *
     * @param array $data Existing data
     * @return array Updated data
     */
    private function splitCombinedName(array $data): array
    {
        if (!empty($data['name']) && empty($data['first_name']) && empty($data['last_name'])) {
            $parts = preg_split('/\s+/', trim($data['name']));

            if ($parts && count($parts) >= 2) {
                $data['first_name'] = $this->titleCase($parts[0]);
                $data['last_name'] = $this->titleCase(implode(' ', array_slice($parts, 1)));
            } elseif ($parts && count($parts) === 1) {
                $data['first_name'] = $this->titleCase($parts[0]);
            }
        }

        return $data;
    }

    /**
     * Extract phone number from text
     *
     * @param string $text Transcript text
     * @return string|null Phone number or null
     */
    private function extractPhoneNumber(string $text): ?string
    {
        if (preg_match('/\b(\+?\d[\d\s\-().]{6,}\d)\b/', $text, $matches)) {
            return preg_replace('/[^\d\+]/', '', $matches[1]);
        }

        return null;
    }

    /**
     * Extract email address from text
     *
     * @param string $text Transcript text
     * @return string|null Email or null
     */
    private function extractEmail(string $text): ?string
    {
        if (preg_match('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', $text, $matches)) {
            return $matches[0];
        }

        return null;
    }

    /**
     * Extract year from text
     *
     * @param string $text Transcript text
     * @return string|null Year or null
     */
    private function extractYear(string $text): ?string
    {
        if (preg_match('/\b(19|20)\d{2}\b/', $text, $matches)) {
            $year = (int)$matches[0];

            if ($year >= 1990 && $year <= 2030) {
                return $matches[0];
            }
        }

        return null;
    }

    /**
     * Convert string to title case
     *
     * @param string $str Input string
     * @return string Title cased string
     */
    private function titleCase(string $str): string
    {
        $str = strtolower($str);
        return preg_replace_callback(
            '/\b([a-z])([a-z]*)/u',
            fn($m) => strtoupper($m[1]) . $m[2],
            $str
        ) ?? $str;
    }

    /**
     * Check if phone number has valid length
     *
     * @param string $phone Phone number
     * @return bool True if valid
     */
    private function hasValidPhone(string $phone): bool
    {
        $digits = preg_replace('/[^\d]/', '', $phone);
        return strlen($digits) >= 10;
    }

    /**
     * Validate extracted data
     *
     * @param array $data Extracted data
     * @return array Validated data
     */
    public function validate(array $data): array
    {
        $validated = [];

        // Validate phone
        if (!empty($data['phone'])) {
            $phone = preg_replace('/[^\d\+]/', '', $data['phone']);

            if ($this->hasValidPhone($phone)) {
                $validated['phone'] = $phone;
            }
        }

        // Validate year
        if (!empty($data['year'])) {
            $year = (int)$data['year'];

            if ($year >= 1990 && $year <= 2030) {
                $validated['year'] = (string)$year;
            }
        }

        // Validate email
        if (!empty($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $validated['email'] = $data['email'];
        }

        // Pass through other fields
        $passThrough = ['first_name', 'last_name', 'name', 'address', 'make', 'model', 'engine', 'engine_size', 'notes'];

        foreach ($passThrough as $field) {
            if (!empty($data[$field])) {
                $validated[$field] = trim($data[$field]);
            }
        }

        return $validated;
    }
}
