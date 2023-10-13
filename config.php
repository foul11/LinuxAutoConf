<?php
use Abstracts\AScripts;

return [
    'include' => [ # Array config files or directories
        __DIR__ . '/conf.d',
    ],
    
    'log' => [
        'file_diffs' => '/var/local/linux_auto_conf/diffs.jsons',
        'file_changes' => '/var/local/linux_auto_conf/changes.jsons',
    ],
    
    'data' => '/var/local/linux_auto_conf/data.json',
    
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
                    'type' => 'INI', # One of (INI | JSON | KEYVAL | XML | YAML)
                    'opt' => [],     # Options for method (check code)
                    'data' => [
                        '[section] key1' => 'value',
                        '[section] key2' => fn(AScripts $clazz, string $key) => false or 'new value',
                    ],
                ]
            ]
        ]
    ]
];