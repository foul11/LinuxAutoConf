<?php

return [
    'include' => [ # Array config files or directories
        __DIR__ . '/conf.d',
    ],
    
    'remote' => [ # Array server [ Name, IP, Port, Password ]
        [
            'name' => 'localhost',
            'ip' => 'localhost',
            'port' => 22,
            'password' => null,
        ],
    ],
    
    'scripts' => [ # Object scripts conf
        'Class\\Name' => [
            'files' => [ # Default config for AScripts
                '/etc/...' => [ # Key - filepath
                    'type' => 'INI', # One of (ARRAY | INI | JSON | KEYVAL | XML | YAML)
                    'data' => <<<DATA
                    [MySection]
                    key = value
                    DATA,
                ]
            ]
        ]
    ]
];