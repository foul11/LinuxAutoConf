<?php
use Arturka\CLI\Debug;
use Arturka\CLI\Test;

$part1 = <<<INI
;An orphan comment at the top of the file
 #Another orphan comment

#Comment above a group
[Group1] #Group comment 1
Key1=Value1#Comment 1
Key2=Value 2
Key 3=Value 3
 Key 4=Value4
Key5=Value \\#5;Comment 2

;Another comment above a group

INI;

$part2 = <<<INI
 [Group 2] ;Group comment 2
Key1 = Value1 #Comment 3
Key2 = Value 2
Key 3 = Value 3
Key 4 = Value4
 Key 5 = Value\\;5 ;Comment 4
 Key 6 = Value\\;6\\#1 ;Comment 5
; Key 7 ;Comment 6
 Key 8 = Value;7 \\\\;Comment 7
 Key 8 =;Comment 7
 Key 9 = Value\\\\;8 ;Comment 8



; with quotes

;An orphan comment at the top of the file
 #Another orphan comment

#Comment above a group

INI;

$part3 = <<<INI
[Group3] #Group comment 1
Key1="Value1"#Comment 1
Key2="Value 2"
Key 3="Value 3" 
 Key 4="Value4"
Key5="Value \\#5";Comment 2

;Another comment above a group

INI;

$part4 = <<<INI
[Group 4] ;Group comment 2
Key1 = "Value1" #Comment 3
Key2 = "Value 2"
Key 3 =" Value 3"
Key 4 = "Value4"
 Key 5 = "Value\\;5 ;Comment 4"
 Key 6 = "Value\\;6\\#1 ";Comment 5
; Key 7 ;Comment 6
 Key 8 = "Value;7 \\";Comment 7
 Key 80 ="";Comment 7
 Key 9 = "Value\\\\";8 ;Comment 8

INI;

$testData = $part1 . $part2 . $part3 . $part4;

Test::run('Validate testData', function($name, $attempt) use($testData) {
    $conf = Config::fromSource($testData, ConfType::INI, [
        'parse_quotes' => true,
    ]);
    
    _assert($conf->get('[Group1] Key1')    === 'Value1',                $conf->get('[Group1] Key1'));
    _assert($conf->get('[Group1] Key2')    === 'Value 2',               $conf->get('[Group1] Key2'));
    _assert($conf->get('[Group1] Key 3')   === 'Value 3',               $conf->get('[Group1] Key 3'));
    _assert($conf->get('[Group1] Key 4')   === 'Value4',                $conf->get('[Group1] Key 4'));
    _assert($conf->get('[Group1] Key5')    === 'Value \\#5',            $conf->get('[Group1] Key5'));
    _assert($conf->get('[Group 2] Key1')   === 'Value1',                $conf->get('[Group 2] Key1'));
    _assert($conf->get('[Group 2] Key2')   === 'Value 2',               $conf->get('[Group 2] Key2'));
    _assert($conf->get('[Group 2] Key 3')  === 'Value 3',               $conf->get('[Group 2] Key 3'));
    _assert($conf->get('[Group 2] Key 4')  === 'Value4',                $conf->get('[Group 2] Key 4'));
    _assert($conf->get('[Group 2] Key 5')  === 'Value\\;5',             $conf->get('[Group 2] Key 5'));
    _assert($conf->get('[Group 2] Key 6')  === 'Value\\;6\\#1',         $conf->get('[Group 2] Key 6'));
    _assert($conf->get('[Group 2] Key 8')  === '',                      $conf->get('[Group 2] Key 8'));
    _assert($conf->get('[Group 2] Key 9')  === 'Value\\\\',             $conf->get('[Group 2] Key 9'));
    _assert($conf->get('[Group3] Key1')    === 'Value1',                $conf->get('[Group3] Key1'));
    _assert($conf->get('[Group3] Key2')    === 'Value 2',               $conf->get('[Group3] Key2'));
    _assert($conf->get('[Group3] Key 3')   === 'Value 3',               $conf->get('[Group3] Key 3'));
    _assert($conf->get('[Group3] Key 4')   === 'Value4',                $conf->get('[Group3] Key 4'));
    _assert($conf->get('[Group3] Key5')    === 'Value \\#5',            $conf->get('[Group3] Key5'));
    _assert($conf->get('[Group 4] Key1')   === 'Value1',                $conf->get('[Group 4] Key1'));
    _assert($conf->get('[Group 4] Key2')   === 'Value 2',               $conf->get('[Group 4] Key2'));
    _assert($conf->get('[Group 4] Key 3')  === ' Value 3',              $conf->get('[Group 4] Key 3'));
    _assert($conf->get('[Group 4] Key 4')  === 'Value4',                $conf->get('[Group 4] Key 4'));
    _assert($conf->get('[Group 4] Key 5')  === 'Value\\;5 ;Comment 4',  $conf->get('[Group 4] Key 5'));
    _assert($conf->get('[Group 4] Key 6')  === 'Value\\;6\\#1 ',        $conf->get('[Group 4] Key 6'));
    _assert($conf->get('[Group 4] Key 8')  === 'Value;7 \\',            $conf->get('[Group 4] Key 8'));
    _assert($conf->get('[Group 4] Key 80') === '',                      $conf->get('[Group 4] Key 80'));
    _assert($conf->get('[Group 4] Key 9')  === 'Value\\\\',             $conf->get('[Group 4] Key 9'));
    
    Test::complite(true);
});

Test::run('__ToString', function() use($testData, $part1, $part2, $part3, $part4) {
    $nl = substr($part1, strlen($part1) - 1, 1);
    $conf = Config::fromSource($testData, ConfType::INI, [
        'parse_quotes' => true,
    ]);
    
    $conf->set(' [Group 2] NewKey ', 'valueee');
    Debug::notice($conf->__toString());
    
    if ($conf->__toString() == $part1 . $part2 . 'NewKey=valueee' . $nl . $part3 . $part4)
        Test::complite(true);
});