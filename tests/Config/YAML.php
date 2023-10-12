<?php
use Arturka\CLI\Debug;
use Arturka\CLI\Test;

Test::run('Config/YAML', function($name, $attempt) {
    $answer = [
        'one' => 0,
        'two' => [ '10' => [5,4,3,2,1] ],
    ];
    
    $conf = Config::fromSource('{"one":0}', ConfType::YAML);
    
    $conf->set('two', [
        '10' => [
            5,4,3,2,1,
        ]
    ]);
    
    $o1 = $conf->__toString();
    $o2 = yaml_emit($answer);
    
    Debug::var_dump($o1);
    Debug::var_dump($o2);
    
    Test::complite($o1 == $o2);
});