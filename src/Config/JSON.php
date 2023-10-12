<?php
namespace Config;
use Abstracts\AConfig;

class JSON extends AConfig {
    static function newInstance(string &$source, $opts = []) {
        $clazz = parent::newInstance($source, $opts);
        $clazz->store = json_decode($clazz->source, true) ?? [];
        
        return $clazz;
    }
    
    function __toString() {
        return json_encode($this->store, JSON_PRETTY_PRINT);
    }
}