<?php
use Abstracts\AMenu;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\MenuItem\MenuItemInterface;
use PhpSchool\CliMenu\MenuStyle;
use PhpSchool\Terminal\Terminal;

class CliMenuReflects {
    static protected \ReflectionClass $refCliMenu;
    static protected \ReflectionClass $refCliMenuBuilder;
    static protected \ReflectionClass $refAMenu;
    
    static protected \ReflectionProperty $refCliMenu_parent;
    
    static protected \ReflectionProperty $refCliMenuBuilder_menu;
    static protected \ReflectionProperty $refCliMenuBuilder_style;
    static protected \ReflectionProperty $refCliMenuBuilder_terminal;
    static protected \ReflectionProperty $refCliMenuBuilder_subMenu;
    static protected \ReflectionProperty $refCliMenuBuilder_autoShortcuts;
    static protected \ReflectionProperty $refCliMenuBuilder_extraItemStyles;
    static protected \ReflectionProperty $refCliMenuBuilder_autoShortcutsRegex;
    static protected \ReflectionProperty $refCliMenuBuilder_disableDefaultItems;
    
    static protected \ReflectionMethod $refCliMenuBuilder_processItemShortcut;
    
    static protected \ReflectionProperty $refAMenu_parent;
    static protected \ReflectionProperty $refAMenu_builder;
    
    static function init() {
        static::$refCliMenu = new \ReflectionClass('\PhpSchool\CliMenu\CliMenu');
        static::$refCliMenuBuilder = new \ReflectionClass('\PhpSchool\CliMenu\Builder\CliMenuBuilder');
        static::$refAMenu = new \ReflectionClass('\Abstracts\AMenu');
        
        static::$refCliMenu_parent = static::$refCliMenu->getProperty('parent');
        
        static::$refCliMenuBuilder_menu = static::$refCliMenuBuilder->getProperty('menu');
        static::$refCliMenuBuilder_style = static::$refCliMenuBuilder->getProperty('style');
        static::$refCliMenuBuilder_subMenu = static::$refCliMenuBuilder->getProperty('subMenu');
        static::$refCliMenuBuilder_terminal = static::$refCliMenuBuilder->getProperty('terminal');
        static::$refCliMenuBuilder_autoShortcuts = static::$refCliMenuBuilder->getProperty('autoShortcuts');
        static::$refCliMenuBuilder_extraItemStyles = static::$refCliMenuBuilder->getProperty('extraItemStyles');
        static::$refCliMenuBuilder_autoShortcutsRegex = static::$refCliMenuBuilder->getProperty('autoShortcutsRegex');
        static::$refCliMenuBuilder_disableDefaultItems = static::$refCliMenuBuilder->getProperty('disableDefaultItems');
        
        static::$refCliMenuBuilder_processItemShortcut = static::$refCliMenuBuilder->getMethod('processItemShortcut');
        
        static::$refAMenu_parent = static::$refAMenu->getProperty('parent');
        static::$refAMenu_builder = static::$refAMenu->getProperty('builder');
    }
    
    static function isSubMenu(CliMenuBuilder $builder) : bool {
        return static::$refCliMenuBuilder_subMenu->getValue($builder);
    }
    
    static function getMenu(CliMenuBuilder $builder) : CliMenu {
        return static::$refCliMenuBuilder_menu->getValue($builder);
    }
    
    static function getdisableDefaultItems(CliMenuBuilder $builder) : bool {
        return static::$refCliMenuBuilder_disableDefaultItems->getValue($builder);
    }
    
    
    static function getSuperParentStyle(CliMenuBuilder | CliMenu | AMenu $menu) : MenuStyle {
        if ($menu instanceof CliMenuBuilder) {
            $menu = static::getMenu($menu);
        } elseif ($menu instanceof AMenu) {
            $parent = $menu;
            
            while (static::$refAMenu_parent->getValue($parent)) {
                $parent = static::$refAMenu_parent->getValue($parent);
            }
            
            return static::getMenu(static::$refAMenu_builder->getValue($parent))->getStyle();
        }
        
        $parent = $menu;
        
        while (static::getParent($parent))
            $parent = static::getParent($parent);
        
        return $parent->getStyle();
    }
    
    static function getWidth(CliMenuBuilder | CliMenu | AMenu $menu) : int {
        return static::getSuperParentStyle($menu)->getWidth();
    }
    
    static function genCentredTitle(CliMenuBuilder | CliMenu | AMenu $menu, string $title) : string {
        $width = static::getWidth($menu);
        
        $width /= 2.1;
        $width -= ceil(strlen($title) / 2);
        
        return str_repeat(' ', floor($width)) . $title;
    }
    
    static function setTerminal(CliMenuBuilder $builder, Terminal $terminal) : void {
        static::$refCliMenuBuilder_terminal->setValue($builder, $terminal);
    }
    
    static function setStyle(CliMenuBuilder $builder, MenuStyle $style) : void {
        static::$refCliMenuBuilder_style->setValue($builder, $style);
    }
    
    static function setMenu(CliMenuBuilder $builder, CliMenu $menu) : void {
        static::$refCliMenuBuilder_menu->setValue($builder, $menu);
    }
    
    static function setSubMenu(CliMenuBuilder $builder, bool $subMenu) : void {
        static::$refCliMenuBuilder_subMenu->setValue($builder, $subMenu);
    }
    
    static function getAutoShortcuts(CliMenuBuilder $builder) : bool {
        return static::$refCliMenuBuilder_autoShortcuts->getValue($builder);
    }
    
    static function getExtraItemStyles(CliMenuBuilder $builder) : array {
        return static::$refCliMenuBuilder_extraItemStyles->getValue($builder);
    }
    
    static function getAutoShortcutsRegex(CliMenuBuilder $builder) : string {
        return static::$refCliMenuBuilder_autoShortcutsRegex->getValue($builder);
    }
    
    static function callProcessItemShortcut(CliMenuBuilder $builder, MenuItemInterface $item) : void {
        static::$refCliMenuBuilder_processItemShortcut->invoke($builder, $item);
    }
    
    static function getParent(CliMenu $menu) : ?CliMenu {
        return static::$refCliMenu_parent->getValue($menu);
    }
}