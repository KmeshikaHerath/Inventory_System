<?php
namespace App\Core;

class Logger {
    // Base directory for logs
    private static $logDir = __DIR__ . '/../../storage/logs/events/';

    private static function getFilePath(): string {
        // Generates filename like: 2023-10-27_log.log
        $fileName = date("Y-m-d") . "_log.log";
        
        // Ensure the directory exists
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0775, true);
        }

        return self::$logDir . $fileName;
    }

    public static function log($message, $level = 'INFO', array $context = []) {
        $timestamp = date("Y-m-d H:i:s");
        
        // Convert context array to string if provided
        $contextString = !empty($context) ? " | Context: " . json_encode($context) : "";
        
        $entry = "[$timestamp] [$level] $message$contextString" . PHP_EOL;
        
        file_put_contents(self::getFilePath(), $entry, FILE_APPEND);
    }

    public static function info($message, array $context = []) {
        self::log($message, 'INFO', $context);
    }

    public static function warning($message, array $context = []) {
        self::log($message, 'WARNING', $context);
    }

    public static function error($message, array $context = []) {
        self::log($message, 'ERROR', $context);
    }
}
