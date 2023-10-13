<?php
use Abstracts\AScripts;

class Shell { // todo: 
    static protected $conf;
    
    static function init(AppConfig $conf) {
        static::$conf = $conf;
    }
    
    static function exec(string $cmd, array &$out = null, int &$code = null) : bool {
        if (exec($cmd, $out, $code) === false)
            throw new ExeptionApp('exec failed');
        
        return $code != 0;
    }
    
    static function serviceReload(string $service, array &$out = null, int &$code = null) : bool {
        return exec("systemctl restart '{$service}'", $out, $code);
    }
    
    static function serviceStart(string $service, array &$out = null, int &$code = null) : bool {
        return exec("systemctl start '{$service}'", $out, $code);
    }
    
    static function serviceStop(string $service, array &$out = null, int &$code = null) : bool {
        return exec("systemctl stop '{$service}'", $out, $code);
    }
    
    static function readChar() : string {
        $in = '';
        
        readline_callback_handler_install('', function() { });
        while (true) {
            $r = array(STDIN);
            $w = NULL;
            $e = NULL;
            $n = stream_select($r, $w, $e, null);
            
            if ($n && in_array(STDIN, $r)) {
                $in = stream_get_contents(STDIN, 1);
                break;
            }
        }
        readline_callback_handler_remove();
        
        return $in;
    }
}