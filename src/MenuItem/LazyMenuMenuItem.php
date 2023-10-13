<?php
declare(strict_types=1);

namespace MenuItem;

use Overwrite\CliMenuBuilder;
use Overwrite\CliMenu;
use PhpSchool\CliMenu\MenuItem\MenuItemInterface;
use PhpSchool\CliMenu\MenuItem\PropagatesStyles;
use PhpSchool\CliMenu\MenuItem\SelectableItemRenderer;
use PhpSchool\CliMenu\MenuStyle;
use PhpSchool\CliMenu\Style\ItemStyle;
use PhpSchool\CliMenu\Style\SelectableStyle;

class LazyMenuMenuItem implements MenuItemInterface, PropagatesStyles {
    protected string $text;
    protected ?CliMenu $subMenu = null;
    protected \Closure $buildCallback;
    protected ?CliMenuBuilder $parentBulder;
    protected bool $showItemExtra = false;
    protected bool $disabled = false;
    protected SelectableStyle $style;
    protected ?CliMenu $parentStyle = null;
    
    public function __construct(
        string $text,
        \Closure $callback,
        CliMenuBuilder $builder = null
    ) {
        $this->text = $text;
        $this->buildCallback = $callback;
        $this->parentBulder = $builder;

        $this->style = new SelectableStyle();
    }
    
    
    public function getRows(MenuStyle $style, bool $selected = false) : array {
        return (new SelectableItemRenderer())->render($style, $this, $selected, $this->disabled);
    }
    
    public function getText() : string {
        return $this->text;
    }
    
    public function setText(string $text) : void {
        $this->text = $text;
    }
    
    public function getSelectAction() : ?callable {
        return function (CliMenu $parentMenu) {
            if ($this->subMenu === null) {
                $builder = new CliMenuBuilder($parentMenu->getTerminal());
                
                // if ($this->parentBulder)
                //     foreach ([
                //         'goBackButtonText',
                //         'exitButtonText',
                //         'disableDefaultItems',
                //         'autoShortcuts',
                //         'autoShortcutsRegex',
                //     ] as $name) {
                //         $prop = new \ReflectionProperty($builder, $name);
                //         $prop->setAccessible(true);
                        
                //         $prop->setValue($builder, $prop->getValue($this->parentBulder));
                //     }
                
                \CliMenuReflects::getMenu($builder)->setParent($parentMenu);
                
                if (($this->buildCallback)($builder) === false)
                    return false;
                
                $menu = $builder->build();
                $this->subMenu = $menu;
                
                if ($this->parentStyle !== null)
                    $this->propagateStyles($this->parentStyle);
            }
            
            $parentMenu->closeThis();
            $this->subMenu->open();
        };
    }
    
    public function canSelect() : bool {
        return !$this->disabled;
    }
    
    public function showItemExtra() : void {
        $this->showItemExtra = true;
    }
    
    public function showsItemExtra() : bool {
        return $this->showItemExtra;
    }
    
    public function hideItemExtra() : void {
        $this->showItemExtra = false;
    }
    
    
    public function getStyle() : ItemStyle {
        return $this->style;
    }
    
    public function setStyle(SelectableStyle $style) : void {
        $this->style = $style;
    }
    
    public function propagateStyles(\PhpSchool\CliMenu\CliMenu $parent): void {
        if ($this->subMenu === null) {
            $this->parentStyle = $parent;
            return;
        }
        
        $this->subMenu->importStyles($parent);
        $this->subMenu->propagateStyles();
    }
}
