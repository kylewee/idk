<?php
/**
 * Logger Utility for Cloud Mode
 * 
 * Provides centralized logging functionality for the application.
 * Logs are written to JSON files for easy parsing and monitoring.
 */

class Logger {
    private $logDir;
    
    public function __construct($logDir = __DIR__) {
        $this->logDir = rtrim($logDir, '/');
    }
    
    /**
     * Log a chat/interaction event
     * 
     * @param string $event Event type (e.g., 'quote_request', 'call_received')
     * @param array $data Event data
     * @param string $level Log level (info, warning, error)
     * @return bool Success status
     */
    public function logChat($event, $data = [], $level = 'info') {
        return $this->writeLog('chat.json', [
            'timestamp' => date('c'),
            'level' => $level,
            'event' => $event,
            'data' => $data
        ]);
    }
    
    /**
     * Log a general application event
     * 
     * @param string $filename Log file name
     * @param string $message Log message
     * @param array $context Additional context
     * @param string $level Log level
     * @return bool Success status
     */
    public function log($filename, $message, $context = [], $level = 'info') {
        return $this->writeLog($filename, [
            'timestamp' => date('c'),
            'level' => $level,
            'message' => $message,
            'context' => $context
        ]);
    }
    
    /**
     * Write log entry to file
     * 
     * @param string $filename Log file name
     * @param array $entry Log entry data
     * @return bool Success status
     */
    private function writeLog($filename, $entry) {
        $filepath = $this->logDir . '/' . $filename;
        
        // For JSON files, maintain array structure
        if (pathinfo($filename, PATHINFO_EXTENSION) === 'json') {
            $entries = [];
            if (file_exists($filepath)) {
                $content = @file_get_contents($filepath);
                if ($content !== false) {
                    $entries = json_decode($content, true) ?: [];
                }
            }
            $entries[] = $entry;
            
            // Keep only last 1000 entries to prevent file from growing too large
            if (count($entries) > 1000) {
                $entries = array_slice($entries, -1000);
            }
            
            $result = @file_put_contents($filepath, json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            // For other files, append as line-delimited JSON
            $result = @file_put_contents($filepath, json_encode($entry) . PHP_EOL, FILE_APPEND);
        }
        
        return $result !== false;
    }
    
    /**
     * Read log entries from a file
     * 
     * @param string $filename Log file name
     * @param int $limit Maximum number of entries to return (0 = all)
     * @return array Log entries
     */
    public function readLog($filename, $limit = 0) {
        $filepath = $this->logDir . '/' . $filename;
        
        if (!file_exists($filepath)) {
            return [];
        }
        
        if (pathinfo($filename, PATHINFO_EXTENSION) === 'json') {
            $content = @file_get_contents($filepath);
            if ($content === false) {
                return [];
            }
            $entries = json_decode($content, true) ?: [];
            
            if ($limit > 0) {
                return array_slice($entries, -$limit);
            }
            return $entries;
        } else {
            // Read line-delimited JSON
            $lines = @file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines === false) {
                return [];
            }
            
            $entries = array_map(function($line) {
                return json_decode($line, true);
            }, $lines);
            
            if ($limit > 0) {
                return array_slice($entries, -$limit);
            }
            return $entries;
        }
    }
}

// Example usage:
// $logger = new Logger(__DIR__);
// $logger->logChat('quote_request', ['name' => 'John Doe', 'phone' => '555-1234']);
// $logger->log('application.log', 'User logged in', ['user_id' => 123]);
