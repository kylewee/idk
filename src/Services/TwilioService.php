<?php

declare(strict_types=1);

require_once __DIR__ . '/../Config.php';

/**
 * Twilio Service
 *
 * Handles all interactions with Twilio API
 * Provides methods for SMS, voice calls, and recording operations
 *
 * @package MechanicStAugustine\Services
 */
class TwilioService
{
    private Config $config;
    private string $accountSid;
    private string $authToken;
    private string $smsFrom;

    public function __construct(?Config $config = null)
    {
        $this->config = $config ?? Config::getInstance();

        $twilioConfig = $this->config::twilio();
        $this->accountSid = $twilioConfig['account_sid'];
        $this->authToken = $twilioConfig['auth_token'];
        $this->smsFrom = $twilioConfig['sms_from'];
    }

    /**
     * Send an SMS message
     *
     * @param string $to Recipient phone number (E.164 format)
     * @param string $body Message content
     * @return array Result of the operation
     */
    public function sendSms(string $to, string $body): array
    {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json";

        $data = [
            'From' => $this->smsFrom,
            'To' => $to,
            'Body' => $body,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => "{$this->accountSid}:{$this->authToken}",
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        $result = [
            'success' => ($httpCode >= 200 && $httpCode < 300),
            'http_code' => $httpCode,
            'response' => $response,
        ];

        if ($errno) {
            $result['curl_errno'] = $errno;
            $result['curl_error'] = $error;
        }

        return $result;
    }

    /**
     * Get recording details
     *
     * @param string $recordingSid Recording SID
     * @return array|null Recording details or null on failure
     */
    public function getRecording(string $recordingSid): ?array
    {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Recordings/{$recordingSid}.json";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => "{$this->accountSid}:{$this->authToken}",
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return null;
        }

        $data = json_decode($response, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Get recording URL
     *
     * @param string $recordingSid Recording SID
     * @param string $format Format (mp3, wav)
     * @return string Recording URL
     */
    public function getRecordingUrl(string $recordingSid, string $format = 'mp3'): string
    {
        return "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Recordings/{$recordingSid}.{$format}";
    }

    /**
     * Download recording audio
     *
     * @param string $recordingSid Recording SID
     * @param string $format Format (mp3, wav)
     * @return string|null Audio content or null on failure
     */
    public function downloadRecording(string $recordingSid, string $format = 'mp3'): ?string
    {
        $url = $this->getRecordingUrl($recordingSid, $format);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => "{$this->accountSid}:{$this->authToken}",
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return null;
        }

        return $response;
    }

    /**
     * Delete a recording
     *
     * @param string $recordingSid Recording SID
     * @return bool True on success
     */
    public function deleteRecording(string $recordingSid): bool
    {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Recordings/{$recordingSid}.json";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => "{$this->accountSid}:{$this->authToken}",
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($httpCode === 204);
    }

    /**
     * Get call details
     *
     * @param string $callSid Call SID
     * @return array|null Call details or null on failure
     */
    public function getCall(string $callSid): ?array
    {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Calls/{$callSid}.json";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => "{$this->accountSid}:{$this->authToken}",
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return null;
        }

        $data = json_decode($response, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Normalize phone number to E.164 format
     *
     * @param string $phone Phone number
     * @param string $defaultCountryCode Default country code (default: +1 for US)
     * @return string Normalized phone number
     */
    public function normalizePhoneNumber(string $phone, string $defaultCountryCode = '+1'): string
    {
        // Remove all non-digit characters except +
        $cleaned = preg_replace('/[^\d\+]/', '', $phone);

        // If already has +, return as is
        if (substr($cleaned, 0, 1) === '+') {
            return $cleaned;
        }

        // If 10 digits, assume US number
        if (strlen($cleaned) === 10) {
            return $defaultCountryCode . $cleaned;
        }

        // If 11 digits and starts with 1, assume US number
        if (strlen($cleaned) === 11 && substr($cleaned, 0, 1) === '1') {
            return '+' . $cleaned;
        }

        // Otherwise, add default country code
        return $defaultCountryCode . $cleaned;
    }

    /**
     * Format phone number for display
     *
     * @param string $phone Phone number
     * @return string Formatted phone number
     */
    public function formatPhoneNumber(string $phone): string
    {
        $cleaned = preg_replace('/[^\d]/', '', $phone);

        // US phone number format
        if (strlen($cleaned) === 10) {
            return sprintf('(%s) %s-%s',
                substr($cleaned, 0, 3),
                substr($cleaned, 3, 3),
                substr($cleaned, 6)
            );
        }

        if (strlen($cleaned) === 11 && substr($cleaned, 0, 1) === '1') {
            return sprintf('+1 (%s) %s-%s',
                substr($cleaned, 1, 3),
                substr($cleaned, 4, 3),
                substr($cleaned, 7)
            );
        }

        return $phone;
    }

    /**
     * Validate phone number format
     *
     * @param string $phone Phone number
     * @return bool True if valid
     */
    public function isValidPhoneNumber(string $phone): bool
    {
        $cleaned = preg_replace('/[^\d\+]/', '', $phone);

        // Must have at least 10 digits
        $digitCount = strlen(preg_replace('/[^\d]/', '', $cleaned));
        if ($digitCount < 10) {
            return false;
        }

        // If starts with +, must be E.164 format
        if (substr($cleaned, 0, 1) === '+') {
            return (bool)preg_match('/^\+\d{10,15}$/', $cleaned);
        }

        // Otherwise, must be 10 or 11 digits
        return in_array(strlen($cleaned), [10, 11], true);
    }

    /**
     * Generate TwiML response for voice call
     *
     * @param string $forwardTo Number to forward to
     * @param string $recordingCallback URL for recording callback
     * @return string TwiML XML
     */
    public function generateDialTwiml(string $forwardTo, ?string $recordingCallback = null): string
    {
        $twiml = '<?xml version="1.0" encoding="UTF-8"?>';
        $twiml .= '<Response>';
        $twiml .= '<Dial';

        if ($recordingCallback) {
            $twiml .= ' record="record-from-answer"';
            $twiml .= ' recordingStatusCallback="' . htmlspecialchars($recordingCallback) . '"';
            $twiml .= ' recordingStatusCallbackMethod="POST"';

            $twilioConfig = $this->config::twilio();
            if ($twilioConfig['transcribe_enabled']) {
                $twiml .= ' transcribe="true"';
                $twiml .= ' transcribeCallback="' . htmlspecialchars($recordingCallback) . '"';
            }
        }

        $twiml .= '>';
        $twiml .= '<Number>' . htmlspecialchars($forwardTo) . '</Number>';
        $twiml .= '</Dial>';
        $twiml .= '</Response>';

        return $twiml;
    }

    /**
     * Generate TwiML response for voicemail
     *
     * @param string $message Message to play
     * @param string $recordingCallback URL for recording callback
     * @param int $maxLength Maximum recording length in seconds
     * @return string TwiML XML
     */
    public function generateVoicemailTwiml(
        string $message,
        string $recordingCallback,
        int $maxLength = 120
    ): string {
        $twiml = '<?xml version="1.0" encoding="UTF-8"?>';
        $twiml .= '<Response>';
        $twiml .= '<Say>' . htmlspecialchars($message) . '</Say>';
        $twiml .= '<Record';
        $twiml .= ' maxLength="' . $maxLength . '"';
        $twiml .= ' transcribe="true"';
        $twiml .= ' transcribeCallback="' . htmlspecialchars($recordingCallback) . '"';
        $twiml .= '/>';
        $twiml .= '</Response>';

        return $twiml;
    }
}
