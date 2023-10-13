<?php
use Abstracts\AScripts;

class Store { // todo: 
    static protected $conf;
    
    static function init(AppConfig $conf) {
        static::$conf = $conf;
    }
    
    static function setInstall(AScripts $clazz) {
        
    }
    
    static function setUpdate(AScripts $clazz) {
        
    }
    
    static function setRemove(AScripts $clazz) {
        
    }
}