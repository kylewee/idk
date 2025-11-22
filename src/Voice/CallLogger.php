<?php

namespace MechanicStAugustine\Voice;

use PDO;
use PDOException;

/**
 * Call Logger
 *
 * Handles database operations for voice call recordings and transcripts.
 */
class CallLogger
{
    private $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? $this->createConnection();
    }

    /**
     * Create database connection
     *
     * @return PDO Database connection
     */
    private function createConnection(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            config('database.server'),
            config('database.database')
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return new PDO(
            $dsn,
            config('database.username'),
            config('database.password'),
            $options
        );
    }

    /**
     * Log a voice call recording
     *
     * @param array $callData Call data to log
     * @return int|null Inserted ID or null on failure
     */
    public function logCall(array $callData): ?int
    {
        try {
            // Create table if it doesn't exist
            $this->ensureTableExists();

            $sql = "INSERT INTO voice_recordings (
                call_sid, recording_sid, recording_url,
                transcript, from_number, to_number,
                duration, status, customer_data,
                created_at
            ) VALUES (
                :call_sid, :recording_sid, :recording_url,
                :transcript, :from_number, :to_number,
                :duration, :status, :customer_data,
                NOW()
            )";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'call_sid' => $callData['call_sid'] ?? null,
                'recording_sid' => $callData['recording_sid'] ?? null,
                'recording_url' => $callData['recording_url'] ?? null,
                'transcript' => $callData['transcript'] ?? null,
                'from_number' => $callData['from_number'] ?? null,
                'to_number' => $callData['to_number'] ?? null,
                'duration' => $callData['duration'] ?? null,
                'status' => $callData['status'] ?? 'completed',
                'customer_data' => json_encode($callData['customer_data'] ?? []),
            ]);

            return (int)$this->pdo->lastInsertId();

        } catch (PDOException $e) {
            error_log('CALL_LOGGER: Database error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all call recordings
     *
     * @param int $limit Maximum number of records
     * @param int $offset Offset for pagination
     * @return array List of call recordings
     */
    public function getAllCalls(int $limit = 100, int $offset = 0): array
    {
        try {
            $this->ensureTableExists();

            $sql = "SELECT * FROM voice_recordings
                    ORDER BY created_at DESC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log('CALL_LOGGER: Database error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a specific call by recording SID
     *
     * @param string $recordingSid Recording SID
     * @return array|null Call data or null if not found
     */
    public function getCall(string $recordingSid): ?array
    {
        try {
            $this->ensureTableExists();

            $sql = "SELECT * FROM voice_recordings WHERE recording_sid = :recording_sid LIMIT 1";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['recording_sid' => $recordingSid]);

            $result = $stmt->fetch();
            return $result ?: null;

        } catch (PDOException $e) {
            error_log('CALL_LOGGER: Database error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Ensure the voice_recordings table exists
     */
    private function ensureTableExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS voice_recordings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            call_sid VARCHAR(255),
            recording_sid VARCHAR(255) UNIQUE,
            recording_url TEXT,
            transcript TEXT,
            from_number VARCHAR(50),
            to_number VARCHAR(50),
            duration INT,
            status VARCHAR(50),
            customer_data JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_recording_sid (recording_sid),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->pdo->exec($sql);
    }
}
