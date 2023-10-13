<?php
namespace Abstracts;

use AppConfig;
use Arturka\CLI\Debug;
use Interfaces\IScripts;
use PhpSchool\CliMenu\Action\GoBackAction;
use Overwrite\CliMenu;
use Overwrite\CliMenuBuilder;
use PhpSchool\CliMenu\MenuItem\LineBreakItem;
use PhpSchool\CliMenu\MenuItem\SelectableItem;

abstract class AScripts implements IScripts {
    protected CliMenu $menu;
    protected AppConfig $conf;
    
    public string $classConf;
    
    static ?self $self = null;
    
    protected function __construct(AppConfig $conf, CliMenu $menu) {
        $this->conf = $conf;
        $this->menu = $menu;
        $this->classConf = preg_match_first("/^Scripts\\\\(.+)$/", static::class);
        
        $items = $this->getMenuElements();
        $items[] = new LineBreakItem(' ');
        $items[] = new LineBreakItem('-');
        $items[] = new SelectableItem('[B]ack', new GoBackAction());
        
        $menu->setItems($items);
        $this->setTitle(preg_match_first("/^(?:.+\\\\)?(.+)$/", static::class));
    }
    
    static function init(AppConfig $conf, CliMenuBuilder $builder) : static {
        $self = static::$self;
        
        if ($self === null) {
            $builder->enableAutoShortcuts();
            $builder->disableDefaultItems();
            $menu = \CliMenuReflects::getMenu($builder);
            
            $self = new static($conf, $menu);
            
            foreach ($menu->getItems() as $item)
                \CliMenuReflects::callProcessItemShortcut($builder, $item);
            
            $builder->build();
            
            static::$self = $self;
        }
        
        return $self;
    }
    
    protected function getMenuElements($RemoveDisable = true) {
        if ($this->checkVersion() === null) {
            return [
                new SelectableItem('Install', [$this, 'install']),
            ];
        } else {
            return [
                new SelectableItem('Update', [$this, 'update']),
                new SelectableItem('Remove', [$this, 'remove'], false, $RemoveDisable),
            ];
        }
    }
    
    function install() {
        $this->update();
        
        \Store::setInstall($this);
    }
    
    function remove() {
        \Store::setRemove($this);
    }
    
    function update() {
        $this->updateConfigFiles();
        
        \Store::setUpdate($this);
    }
    
    function checkVersion() : ?string {
        return null;
    }
    
    function updateConfigFiles() {
        if (($cfg = $this->conf->get("scripts.{$this->classConf}.files", null)) === null)
            Debug::error("[{$this->classConf}] updateConfigFiles failed, cfg obj is null");
        
        foreach ($cfg as $path => $data) {
            if (!isset($data['type']))
                throw new \ExeptionApp("In script '{$this->classConf}' in file config, file '{$path}' does not have a type description");
            
            $input = \Config::fromPath($path, match ($data['type']) {
                'INI' => \ConfType::INI,
                'JSON' => \ConfType::JSON,
                'KEYVAL' => \ConfType::KEYVAL,
                'XML' => \ConfType::XML,
                'YAML' => \ConfType::YAML,
            }, $data['opt'] ?? []);
            
            foreach ($data['data'] as $key => $val) { // todo: if val callable then call with args (AScripts $clazz, ?string $version installed)
                if (is_callable($val)) {
                    if (($ret = $val($this, $key)) !== false)
                        $input->set($key, $ret);
                } else {
                    $input->set($key, $val);
                }
            }
            
            \Log::logChange($this, $path, $input->changes());
            \Log::logDiff($this, $path, $input->diff());
            
            if (!file_exists($dir = dirname($path)))
                mkdir($dir, 0755, true);
            
            if (file_put_contents($path, (string)$input) === false)
                Debug::error("failed write [{$path}]");
        }
    }
    
    protected function setTitle($title) {
        $this->menu->setTitle($title);
    }
}