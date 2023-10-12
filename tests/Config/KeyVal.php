<?php
use Arturka\CLI\Debug;
use Arturka\CLI\Test;

$testData = <<<KV
;An orphan comment at the top of the file
 #Another orphan comment

#Comment above a group
Key%1=Value1#Comment 1
Key%2=Value 2
Key %3=Value 3
 Key %4=Value4
Key%5=Value \\#5;Comment 2

;Another comment above a group
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
Key*_1="Value1"#Comment 1
Key*_2="Value 2"
Key *_3="Value 3" 
 Key *_4="Value4"
Key*_5="Value \\#5";Comment 2

;Another comment above a group
Key_1 = "Value1" #Comment 3
Key_2 = "Value 2"
Key _3 =" Value 3"
Key _4 = "Value4"
 Key _5 = "Value\\;5 ;Comment 4"
 Key _6 = "Value\\;6\\#1 ";Comment 5
; Key _7 ;Comment 6
 Key _8 = "Value;7 \\";Comment 7
 Key _80 ="";Comment 7
 Key _9 = "Value\\\\";8 ;Comment 8

KV;

Test::run('Validate testData', function($name, $attempt) use($testData) {
    $conf = Config::fromSource($testData, ConfType::KEYVAL, [
        'delimiter' => '=',
        'comment' => ['#', ';'],
        'parse_quotes' => true,
    ]);
    
    _assert($conf->get('Key%1') === 'Value1');
    _assert($conf->get('Key%2') === 'Value 2');
    _assert($conf->get('Key %3') === 'Value 3');
    _assert($conf->get('Key %4') === 'Value4');
    _assert($conf->get('Key%5') === 'Value \\#5');
    _assert($conf->get('Key1') === 'Value1');
    _assert($conf->get('Key2') === 'Value 2');
    _assert($conf->get('Key 3') === 'Value 3');
    _assert($conf->get('Key 4') === 'Value4');
    _assert($conf->get('Key 5') === 'Value\\;5');
    _assert($conf->get('Key 6') === 'Value\\;6\\#1');
    _assert($conf->get('Key 8') === '');
    _assert($conf->get('Key 9') === 'Value\\\\');
    _assert($conf->get('Key*_1') === 'Value1');
    _assert($conf->get('Key*_2') === 'Value 2');
    _assert($conf->get('Key *_3') === 'Value 3');
    _assert($conf->get('Key *_4') === 'Value4');
    _assert($conf->get('Key*_5') === 'Value \\#5');
    _assert($conf->get('Key_1') === 'Value1');
    _assert($conf->get('Key_2') === 'Value 2');
    _assert($conf->get('Key _3') === ' Value 3');
    _assert($conf->get('Key _4') === 'Value4');
    _assert($conf->get('Key _5') === 'Value\\;5 ;Comment 4');
    _assert($conf->get('Key _6') === 'Value\\;6\\#1 ');
    _assert($conf->get('Key _8') === 'Value;7 \\');
    _assert($conf->get('Key _80') === '');
    _assert($conf->get('Key _9') === 'Value\\\\');
    
    Test::complite(true);
});

Test::run('__ToString', function() use($testData) {
    $conf = Config::fromSource($testData, ConfType::KEYVAL, [
        'delimiter' => '=',
        'comment' => ['#', ';'],
        'parse_quotes' => true,
    ]);
    
    $conf->set('NewKey', 'valueee');
    
    Debug::notice($conf->__toString());
    
    if ($conf->__toString() == $testData . substr($testData, strlen($testData) - 1, 1) . 'NewKey=valueee')
        Test::complite(true);
});