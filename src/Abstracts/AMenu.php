<?php
namespace Abstracts;

use Interfaces\IMenu;
use Overwrite\CliMenu;
use Overwrite\CliMenuBuilder;

abstract class AMenu implements IMenu {
    protected CliMenuBuilder $builder;
    protected CliMenu $menu;
    protected \AppConfig $conf;
    protected ?AMenu $parent;
    
    const TITLE = 'NO TITLE';
    
    function __construct($conf, $builder = new CliMenuBuilder(), AMenu $parentAMenu = null) {
        $this->parent = $parentAMenu;
        $this->builder = $builder;
        $this->conf = $conf;
        $this->menu = \CliMenuReflects::getMenu($builder);
        
        $builder->disableDefaultItems();
        $builder->setTitle(static::TITLE);
    }
    
    function execute() {
        $this->menu->open();
    }
    
    function setTitle(string $title) {
        $this->menu->setTitle($title);
    }
}