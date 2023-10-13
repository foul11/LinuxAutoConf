<?php
use Arturka\CLI\Debug;
use Noodlehaus\Exception\EmptyDirectoryException;
use Noodlehaus\Exception\FileNotFoundException;
use Noodlehaus\Parser\Ini;


class App {
    protected AppConfig $conf;
    protected Menu $MenuMain;
    protected string $title = 'localhost';
    
    function __construct(array $argv, AppConfig $conf) {
        $this->parseOpt($argv, $conf);
        
        $this->conf = $conf;
        $this->MenuMain = new Menu($conf);
        
        $this->main($argv);
    }
    
    function parseOpt(array $argv, AppConfig $conf) {
        for ($i = 1; $i < count($argv); $i++) {
            $val = $argv[$i];
            
            if ($val == '--title') {
                if (!isset($argv[++$i]))
                    throw new ExeptionApp("An argument is required after the '$val' option");
                
                $this->title = $argv[$i];
            } elseif ($val == '--option' || $val == '-o') {
                if (!isset($argv[++$i]))
                    throw new ExeptionApp("An argument is required after the '$val' option");
                
                $conf->merge(new AppConfig($argv[$i], new Ini(), true));
            } else {
                throw new ExeptionApp('Unrecognize options');
            }
        }
    }
    
    function main(array $argv) {
        $this->MenuMain->setTitle("SELECTED [{$this->title}] HOST");
        $this->MenuMain->execute();
    }
    
    /**
     * Entry point
     */
    static function init($argv) {
        CliMenuReflects::init();
        
        $conf = new AppConfig([__DIR__ . '/../config.php']);
        
        try {
            $confd = new AppConfig($conf->get('include', []));
        } catch (EmptyDirectoryException $e) {
            Debug::notice($e->getMessage());
        } catch (FileNotFoundException $e) {
            Debug::error($e->getMessage());
        }
        
        if (!empty($confd)) {
            $conf->merge($confd);
        }
        
        Log::init($conf);
        Store::init($conf);
        Shell::init($conf);
        
        Debug::notice('Press any key to continue');
        Shell::readChar();
        
        $app = new App($argv, $conf);
    }
}

// $itemCallable = function (CliMenu $menu) {
//     echo $menu->getSelectedItem()->getText();
// };

// $art = <<<ART
//         _ __ _
//        / |..| \
//        \/ || \/
//         |_''_|
//       PHP SCHOOL
// LEARNING FOR ELEPHANTS
// ART;

// $menu = (new CliMenuBuilder)
//     ->addAsciiArt($art, AsciiArtItem::POSITION_CENTER)
//     ->build()
//     ;

// $menu->open();

// $menu = (new CliMenuBuilder)
//     ->setTitle('Basic CLI Menu Disabled Items')
//     ->addItem('First Item', $itemCallable)
//     ->addItem('Second Item', $itemCallable, false, true)
//     ->addItem('Third Item', $itemCallable, false, true)
//     ->addSubMenu('Submenu', function(CliMenuBuilder $menu) use($itemCallable){
//         $menu
//             ->setTitle('Basic CLI Menu Disabled Items > Submenu')
//             ->addItem('You can go in here!', $itemCallable)
//             ;
//     })
//     ->addSubMenu('Disabled Submenu', function($menu) use($itemCallable){
//         $menu
//             ->setTitle('Basic CLI Menu Disabled Items > Disabled Submenu')
//             ->addItem('Nope can\'t see this!', $itemCallable)
//             ->disableMenu()
//             ;
//     })
//     ->addLineBreak('-')
//     ->setBackgroundColour('black')
//     ->build()
//     ;

// $menu->open();

// Debug::notice('looooop');