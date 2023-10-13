#!/bin/env php
<?php
use Arturka\CLI\Debug;
use Arturka\CLI\Test;

require('linterHacks.php');
require('vendor/autoload.php');
Debug::init();

spl_autoload_register(function($class){
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $fileClass = "src/$class.php";
    
    if (file_exists($fileClass)) {
        include_once($fileClass);
    }
});

if (version_compare(PHP_VERSION, '8.1', '>=')) {
    require('src/Enums.php');
}

foreach($argv as $k => $val) {
    if ($val == '--test') {
        function _assert($expr, $str = null) {
            if (!$expr) throw new \Exception('Assert is false' . (isset($str) ? ": $str" : ''));
        }
        
        Test::init($argv);
        Test::execute('tests/');
        
        exit(0);
    }
}

function preg_match_first(string $pattern, string $subject, $offset = 0) {
    preg_match($pattern, $subject, $match, 0, $offset);
    
    return $match[1] ?? $match[0] ?? '';
}

function f(string $format_str, ...$parm) {
    return sprintf($format_str, ...$parm);
}

try {
    App::init($argv);
} catch (ExeptionApp $e) {
    Debug::error($e->getMessage());
}