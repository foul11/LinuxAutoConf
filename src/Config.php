<?php
use Config\INI;
use Config\JSON;
use Config\KeyVal;
use Config\XML;
use Config\YAML;
use Interfaces\IConfig;

class Config {
    private ?string $path = null;
    private ConfType $type;
    private IConfig $conf;
    
    private function __construct(string $source, ConfType $type, $conf) {
        $conf = match ($type) {
            ConfType::INI => INI::newInstance($source, $conf),
            ConfType::JSON => JSON::newInstance($source, $conf),
            ConfType::KEYVAL => KeyVal::newInstance($source, $conf),
            ConfType::XML => XML::newInstance($source, $conf),
            ConfType::YAML => YAML::newInstance($source, $conf),
        };
        
        $this->conf = $conf;
        $this->type = $type;
    }
    
    static function fromSource(string $source, ConfType $type, $conf = []) {
        return new static($source, $type, $conf);
    }
    
    static function fromPath(string $path, ConfType $type, $conf = []) {
        $clazz = new static(file_get_contents($path), $type, $conf);
        $clazz->path = $path;
        
        return $clazz;
    }
    
    function __toString() {
        return $this->conf->__toString();
    }
    
    function get(string $name) {
        return $this->conf->get($name);
    }
    
    function set(string $name, $value) {
        $this->conf->set($name, $value);
    }
    
    function unset(string $name) {
        $this->conf->unset($name);
    }
    
    function isset(string $name) {
        return $this->conf->isset($name);
    }
    
    function diff() {
        return $this->conf->diff();
    }
    
    function changes() {
        return $this->conf->changes();
    }
}