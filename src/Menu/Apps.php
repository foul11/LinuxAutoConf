<?php
namespace Menu;
use Abstracts\AMenu;
use Abstracts\AScripts;
use MenuItem\LazyMenuMenuItem;
use PhpSchool\CliMenu\Action\GoBackAction;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;

class Apps extends AMenu {
    const TITLE = 'Apps';
    
    function __construct($conf, $builder, $parent) {
        parent::__construct($conf, $builder, $parent);
        
        ($walker = function(CliMenuBuilder $menu, $dir) use(&$walker) {
            $files = [];
            
            foreach (new \DirectoryIterator(__DIR__ . '/../' . $dir) as $fileInfo) {
                if ($fileInfo->isDot())
                    continue;
                
                if ($fileInfo->isDir()) {
                    $bname = $fileInfo->getBasename();
                    $menu->addSubMenu("→ {$bname}", function(CliMenuBuilder $menu) use(&$walker, $bname, $dir) {
                        $menu->disableDefaultItems();
                        $menu->setTitle(self::TITLE . ' / ' . $bname);
                        
                        $walker($menu, "$dir/{$bname}");
                    });
                } elseif ($fileInfo->getExtension() == 'php') {
                    $files[] = $fileInfo->getBasename(".{$fileInfo->getExtension()}");
                }
            }
            
            $menu->addLineBreak(' ');
            
            foreach ($files as $file) {
                $menu
                    ->addMenuItem(new LazyMenuMenuItem("$file", function(CliMenuBuilder $menu) use($dir, $file) {
                        $script = (["\\". str_replace('/', '\\', $dir) ."\\$file", 'init'])($this->conf, $menu);
                        
                        if (!$script instanceof AScripts)
                            throw new \Exception('Script returned a strange object');
                    }));
            }
            
            $menu->addLineBreak(' ');
            $menu->addLineBreak('-');
            $menu->addItem('[B]ack', new GoBackAction());
        })($this->builder, 'Scripts');
    }
}