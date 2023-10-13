<?php
namespace Overwrite;
use MenuItem\LazyMenuMenuItem;
use PhpSchool\CliMenu\MenuStyle;
use PhpSchool\CliMenu\Terminal\TerminalFactory;
use PhpSchool\Terminal\Terminal;
use function PhpSchool\CliMenu\Util\each;

class CliMenuBuilder extends \PhpSchool\CliMenu\Builder\CliMenuBuilder {
    public function __construct(Terminal $terminal = null) {
        $terminal = $terminal ?? TerminalFactory::fromSystem();
        $style = new MenuStyle($terminal);
        
        \CliMenuReflects::setTerminal($this, $terminal);
        \CliMenuReflects::setStyle($this, $style);
        \CliMenuReflects::setMenu($this, new CliMenu(null, [], $terminal, $style));
    }
    
    // public static function newSubMenu(Terminal $terminal) : static {
    //     $instance = new static($terminal);
    //     \CliMenuReflects::setSubMenu($instance, true);
        
    //     return $instance;
    // }
    
    // public function addSubMenu(string $text, \Closure $callback) : self {
    //     return parent::addSubMenu($text, function(CliMenuBuilder $menu) use($callback) {
    //         \CliMenuReflects::getMenu($menu)->setParent(\CliMenuReflects::getMenu($this));
    //         $callback($menu);
    //     });
    // }
    
    public function addSubMenu(string $text, \Closure $callback) : static {
        \CliMenuReflects::getMenu($this)->addItem($item = new LazyMenuMenuItem($text, function(CliMenuBuilder $builder) use($callback) {
            \CliMenuReflects::setSubMenu($builder, true);
            
            if (\CliMenuReflects::getAutoShortcuts($this)) {
                $builder->enableAutoShortcuts(\CliMenuReflects::getAutoShortcutsRegex($this));
            }
            
            each(\CliMenuReflects::getExtraItemStyles($this), function (int $i, array $extraItemStyle) use ($builder) {
                $builder->registerItemStyle($extraItemStyle['class'], $extraItemStyle['style']);
            });
            
            return $callback($builder);
        }, $this));
        
        \CliMenuReflects::callProcessItemShortcut($this, $item);

        return $this;
    }
    
    public function build() : CliMenu {
        if (!\CliMenuReflects::isSubMenu($this)) {
            \CliMenuReflects::getMenu($this)->propagateStyles();
        }
        
        return \CliMenuReflects::getMenu($this);
    }
}