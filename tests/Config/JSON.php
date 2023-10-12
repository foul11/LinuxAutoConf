<?php
use Arturka\CLI\Debug;
use Arturka\CLI\Test;

$data = <<<JSON
{
    "differ": {
        "keep": true,
        "peace": {
            "cool": {
                "fought": {
                    "think": "sea",
                    "return": {
                        "wrapped": "battle",
                        "bridge": 1562555286.2356797,
                        "prepare": -698443528,
                        "taken": "upper",
                        "thus": "quick",
                        "seen": true
                    },
                    "browserling": false,
                    "lucky": "suit",
                    "wear": 1222731471.8296734,
                    "twice": "shape"
                },
                "least": false,
                "once": "hot",
                "union": "roll",
                "none": -1861289830.62364,
                "army": "ought"
            },
            "quite": false,
            "peace": true,
            "ranch": -988306180,
            "we": false,
            "particles": 1715110668
        },
        "building": "ear",
        "for": "truth",
        "aloud": false,
        "follow": 727760795
    },
    "plant": true,
    "regular": "adjective",
    "force": -701425083.7569714,
    "origin": "cap",
    "shown": -1778626065.8348866
}
JSON;

Test::run('Config/JSON', function($name, $attempt) {
    $answer = [
        'one' => 0,
        'two' => [ '10' => [5,4,3,2,1] ],
    ];
    
    $conf = Config::fromSource('{"one":0}', ConfType::JSON);
    
    $conf->set('two', [
        '10' => [
            5,4,3,2,1,
        ]
    ]);
    
    $o1 = $conf->__toString();
    $o2 = json_encode($answer, JSON_PRETTY_PRINT);
    
    Debug::var_dump($o1);
    Debug::var_dump($o2);
    
    Test::complite($o1 == $o2);
});


Test::run('TestDiffer', function($name, $attempt) use($data) {
    $conf = Config::fromSource($data, ConfType::JSON);
    
    $conf->get('differ')->get('peace')->get('cool')->set('union', '??????');
    $conf->get('differ')->get('peace')->get('cool')->set('army', 'adjective');
    $conf->get('differ')->set('origin', 'quick');
    $conf->get('differ')->get('peace')->set('aloud', [
        "mass" => true,
        "needed" => true,
        "ask" => "purple",
        "property" => -594560,
    ]);
    $conf->get('differ')->set('shown', $conf->get('differ')->get('peace'));
    
    $o1 = $conf->__toString();
    
    $manual = json_decode($data, true);
    $manual['differ']['peace']['cool']['union'] = '??????';
    $manual['differ']['peace']['cool']['army'] = 'adjective';
    $manual['differ']['origin'] = 'quick';
    $manual['differ']['peace']['aloud'] = [
        "mass" => true,
        "needed" => true,
        "ask" => "purple",
        "property" => -594560,
    ];
    $manual['differ']['shown'] = $manual['differ']['peace'];
    
    $o2 = json_encode($manual, JSON_PRETTY_PRINT);
    
    // Debug::notice($o2);
    // Debug::notice($o1);
    
    if ($o1 == $o2) {
        Debug::notice('Json equal');
        
        // Debug::var_export($conf->diff());
        
        Test::complite($conf->diff() === array (
            'differ' => 
            array (
              'peace' => 
              array (
                'cool' => 
                array (
                  'union' => '??????',
                  'army' => 'adjective',
                ),
                'aloud' => 
                array (
                  'mass' => true,
                  'needed' => true,
                  'ask' => 'purple',
                  'property' => -594560,
                ),
              ),
              'origin' => 'quick',
              'shown' => 
              array (
                'cool' => 
                array (
                  'fought' => 
                  array (
                    'think' => 'sea',
                    'return' => 
                    array (
                      'wrapped' => 'battle',
                      'bridge' => 1562555286.2356796,
                      'prepare' => -698443528,
                      'taken' => 'upper',
                      'thus' => 'quick',
                      'seen' => true,
                    ),
                    'browserling' => false,
                    'lucky' => 'suit',
                    'wear' => 1222731471.8296733,
                    'twice' => 'shape',
                  ),
                  'least' => false,
                  'once' => 'hot',
                  'union' => '??????',
                  'none' => -1861289830.62364,
                  'army' => 'adjective',
                ),
                'quite' => false,
                'peace' => true,
                'ranch' => -988306180,
                'we' => false,
                'particles' => 1715110668,
                'aloud' => 
                array (
                  'mass' => true,
                  'needed' => true,
                  'ask' => 'purple',
                  'property' => -594560,
                ),
              ),
            ),
          )
        );
    } else Debug::notice('Json diff');
});


Test::run('TestChanges', function($name, $attempt) use($data) {
    $conf = Config::fromSource($data, ConfType::JSON);
    
    $conf->get('differ')->get('peace')->get('cool')->set('union', '??????');
    $conf->get('differ')->get('peace')->get('cool')->set('army', 'adjective');
    $conf->get('differ')->set('origin', 'quick');
    $conf->get('differ')->get('peace')->set('aloud', [
        "mass" => true,
        "needed" => true,
        "ask" => "purple",
        "property" => -594560,
    ]);
    $conf->get('differ')->set('shown', $conf->get('differ')->get('peace'));
    
    $o1 = $conf->__toString();
    
    $manual = json_decode($data, true);
    $manual['differ']['peace']['cool']['union'] = '??????';
    $manual['differ']['peace']['cool']['army'] = 'adjective';
    $manual['differ']['origin'] = 'quick';
    $manual['differ']['peace']['aloud'] = [
        "mass" => true,
        "needed" => true,
        "ask" => "purple",
        "property" => -594560,
    ];
    $manual['differ']['shown'] = $manual['differ']['peace'];
    
    $o2 = json_encode($manual, JSON_PRETTY_PRINT);
    
    // Debug::notice($o2);
    // Debug::notice($o1);
    
    if ($o1 == $o2) {
        Debug::notice('Json equal');
        // Debug::var_export($conf->changes());
        
        Test::complite($conf->changes() === array (
            0 => 
            array (
              'path' => 
              array (
                0 => 'differ',
                1 => 'peace',
                2 => 'cool',
              ),
              'key' => 'union',
              'old' => 'roll',
              'new' => '??????',
            ),
            1 => 
            array (
              'path' => 
              array (
                0 => 'differ',
                1 => 'peace',
                2 => 'cool',
              ),
              'key' => 'army',
              'old' => 'ought',
              'new' => 'adjective',
            ),
            2 => 
            array (
              'path' => 
              array (
                0 => 'differ',
              ),
              'key' => 'origin',
              'old' => NULL,
              'new' => 'quick',
            ),
            3 => 
            array (
              'path' => 
              array (
                0 => 'differ',
                1 => 'peace',
              ),
              'key' => 'aloud',
              'old' => NULL,
              'new' => 
              array (
                'mass' => true,
                'needed' => true,
                'ask' => 'purple',
                'property' => -594560,
              ),
            ),
            4 => 
            array (
              'path' => 
              array (
                0 => 'differ',
              ),
              'key' => 'shown',
              'old' => NULL,
              'new' => 
              array (
                'cool' => 
                array (
                  'fought' => 
                  array (
                    'think' => 'sea',
                    'return' => 
                    array (
                      'wrapped' => 'battle',
                      'bridge' => 1562555286.2356796,
                      'prepare' => -698443528,
                      'taken' => 'upper',
                      'thus' => 'quick',
                      'seen' => true,
                    ),
                    'browserling' => false,
                    'lucky' => 'suit',
                    'wear' => 1222731471.8296733,
                    'twice' => 'shape',
                  ),
                  'least' => false,
                  'once' => 'hot',
                  'union' => '??????',
                  'none' => -1861289830.62364,
                  'army' => 'adjective',
                ),
                'quite' => false,
                'peace' => true,
                'ranch' => -988306180,
                'we' => false,
                'particles' => 1715110668,
                'aloud' => 
                array (
                  'mass' => true,
                  'needed' => true,
                  'ask' => 'purple',
                  'property' => -594560,
                ),
              ),
            ),
          )
        );
    } else Debug::notice('Json diff');
});