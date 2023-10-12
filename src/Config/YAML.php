<?php
namespace Config;
use Abstracts\AConfig;

class YAML extends AConfig {
    static function newInstance(string &$source, $opts = []) {
        $clazz = parent::newInstance($source, $opts);
        $clazz->store = yaml_parse($clazz->source) ?? [];
        
        return $clazz;
    }
    
    function __toString() {
        return yaml_emit($this->store);
    }
}