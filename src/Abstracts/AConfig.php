<?php
namespace Abstracts;
// сделать save/restore, или хотябы save поменяв методы __get на get и сделалв методы _get
abstract class AConfig implements \Interfaces\IConfig {
    protected array $opts;
    protected string $source;
    protected ?AConfig $parent = null;
    protected ?string $parentKey = null;
    protected array $store = [];
    protected array $diff = [];
    protected array $changes = [];
    
    protected function __construct(&$opts) {
        $this->opts = $opts;
        
        if (!empty($opts)) {
            throw new \Exception("Config object doesn't expect settings");
        }
    }
    
    static function newInstance(string &$source, $opts = []) {
        $clazz = new static($opts);
        $clazz->source = $source;
        
        return $clazz;
    }
    
    protected static function wrapper($parent, $name, &$obj, &$opts) {
        $clazz = new static($opts);
        
        if ($parent) {
            $clazz->store = &$obj;
            $clazz->parent = $parent;
            $clazz->parentKey = $name;
            $clazz->source = &$parent->source;
        }
        
        return $clazz;
    }
    
    protected function _get(string $name) {
        if (!isset($this->store[$name]))
            return null;
        
        $value = &$this->store[$name];
        
        if (is_scalar($value) || is_null($value))
            return $value;
        
        return $this::wrapper($this, $name, $value, $this->opts);
    }
    
    protected function _set(string $name, $value) {
        if ($value instanceof $this)
            return $this->store[$name] = $value->store;
        
        $this->store[$name] = $value;
    }
    
    protected function _unset(string $name) {
        unset($this->store[$name]);
    }
    
    protected function _isset(string $name) {
        return isset($this->store[$name]);
    }
    
    public function get(string $name) {
        return $this->_get($name);
    }
    
    public function set(string $name, $value) {
        $dPath = [];
        $it = $this;
        
        while ($it->parentKey !== null) {
            $dPath[] = $it->parentKey;
            $it = $it->parent;
        }
        
        if ($it === null)
            throw new \Exception('Why is parent equal to null?');
        
        $pDiff = &$it->diff;
        $dPath = array_reverse($dPath);
        
        foreach ($dPath as $key) {
            if (!isset($pDiff[$key])) {
                $pDiff[$key] = [];
            }
            
            $pDiff = &$pDiff[$key];
        }
        
        $old = $this->get($name);
        if ($old instanceof $this)
            $old = $old->store;
        
        $chg = [
            'path' => $dPath,
            'key' => $name,
            'old' => $old ?? null,
            'new' => null,
        ];
        
        if ($value instanceof $this)
            $pDiff[$name] = $value->store;
        else $pDiff[$name] = $value;
        
        $chg['new'] = $pDiff[$name];
        $it->changes[] = $chg;
        
        return $this->_set($name, $value);
    }
    
    public function isset(string $name) {
        return $this->_isset($name);
    }
    
    public function unset(string $name) {
        return $this->_unset($name);
    }
    
    public function diff() {
        return $this->diff;
    }
    
    public function changes() {
        return $this->changes;
    }
}
