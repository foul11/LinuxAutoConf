<?php
use Abstracts\AMenu;
use Menu\Apps;
use Menu\Remote;
use PhpSchool\CliMenu\Action\ExitAction;
use Overwrite\CliMenu;

class Menu extends AMenu {
    protected CliMenu $menu;
    protected \AppConfig $conf;
    
    function __construct($conf) {
        parent::__construct($conf);
        
        $menu = $this->builder;
        $menu
            ->setWidth(80)
            ->setMarginAuto()
            ->enableAutoShortcuts()
            ->setBackgroundColour('black')
            ->setForegroundColour('white');
        
        $menu->addSubMenu('[A]pps', function($menu) { new Apps($this->conf, $menu, $this); });
        $menu->addSubMenu('[R]emote', function($menu) { new Remote($this->conf, $menu, $this); });
        
        $menu
            ->addLineBreak(' ')
            ->addLineBreak('-')
            ->addItem('Exit', new ExitAction());
            ;
            
        $this->menu = $menu->build();
    }
}