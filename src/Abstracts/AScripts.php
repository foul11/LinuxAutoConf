<?php
namespace Abstracts;

use AppConfig;
use Interfaces\IScripts;
use PhpSchool\CliMenu\Action\GoBackAction;
use Overwrite\CliMenu;
use Overwrite\CliMenuBuilder;
use PhpSchool\CliMenu\MenuItem\LineBreakItem;
use PhpSchool\CliMenu\MenuItem\SelectableItem;

abstract class AScripts implements IScripts {
    protected CliMenu $menu;
    protected AppConfig $conf;
    
    static ?self $self = null;
    
    protected function __construct(AppConfig $conf, CliMenu $menu) {
        $this->conf = $conf;
        $this->menu = $menu;
        
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
    
    protected function getMenuElements() {
        if ($this->checkVersion() === null) {
            return [
                new SelectableItem('Install', [$this, 'install']),
            ];
        } else {
            return [
                new SelectableItem('Update', [$this, 'update']),
                new SelectableItem('Remove', [$this, 'remove']),
            ];
        }
    }
    
    function install() {
        
    }
    
    function remove() {
        
    }
    
    function update() {
        
    }
    
    function checkVersion() : ?int {
        return null;
    }
    
    protected function setTitle($title) {
        $this->menu->setTitle($title);
    }
}