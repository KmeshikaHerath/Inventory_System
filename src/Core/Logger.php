<?php
namespace App\Core;

class Logger {
    // Base directory for logs
    private static $logDir = __DIR__ . '/../../storage/logs/events/';

    private static function getFilePath(): string 
    {
        // Generates filename
        $fileName = date("Y-m-d") . "_log.log";
        
        // Ensure the directory exists
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0775, true);
        }

        return self::$logDir . $fileName;
    }

    // Main logging method
    public static function log($message, $level = 'INFO', array $context = []) : void 
    {
        $timestamp = date("Y-m-d H:i:s");
        
        // Convert context array to string if provided
        $contextString = !empty($context) ? " | Context: " . json_encode($context) : "";
        
        $entry = "[$timestamp] [$level] $message$contextString" . PHP_EOL;
        
        file_put_contents(self::getFilePath(), $entry, FILE_APPEND);
    }

    // Convenience methods for different log levels
    public static function info($message, array $context = []): void 
    {
        self::log($message, 'INFO', $context);
    }

    // Convenience method for warning level
    public static function warning($message, array $context = []): void
    {
        self::log($message, 'WARNING', $context);
    }

    // Convenience method for error level
    public static function error($message, array $context = []): void 
    {
        self::log($message, 'ERROR', $context);
    }
}
