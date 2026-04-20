<?php
namespace App\Core;

class Logger {
    private static $logFile = __DIR__ . '/../../app.log';  

    public static function log($message, $level = 'INFO') {
        $timestamp = date("Y-m-d H:i:s");
        $entry = "[$timestamp] [$level] $message" . PHP_EOL;
        file_put_contents(self::$logFile, $entry, FILE_APPEND);
    }

    public static function error($message) {
        self::log($message, 'ERROR');
    }
    
    public static function info($message) {  // Add info method
        self::log($message, 'INFO');
    }
    
    public static function warning($message) {  // Add warning method
        self::log($message, 'WARNING');
    }
}

