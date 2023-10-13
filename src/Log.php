<?php
use Abstracts\AScripts;

class Log {
    static protected $conf;
    
    static function init(AppConfig $conf) {
        static::$conf = $conf;
        
        if (!file_exists($dir = dirname($conf->get('log.file_diffs'))))
            mkdir($dir, 0755, true);
    
        if (!file_exists($dir = dirname($conf->get('log.file_changes'))))
            mkdir($dir, 0755, true);
    }
    
    static function logDiff(AScripts $clazz, string $path, array $diff) {
        file_put_contents(static::$conf->get('log.file_diffs'),
            "# {$clazz->classConf} [{$path}]:" . PHP_EOL .
            json_encode($diff, JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL,
            FILE_APPEND
        );
    }
    
    static function logChange(AScripts $clazz, string $path, array $changes) {
        file_put_contents(static::$conf->get('log.file_changes'),
            "# {$clazz->classConf} [{$path}]:" . PHP_EOL .
            json_encode($changes, JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL,
            FILE_APPEND
        );
    }
}