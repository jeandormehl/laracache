<?php

return [
    'isc' => [
        'driver' => 'odbc',
        'win_dsn' => env('DB_WIN_DSN', ''),             
        'unix_driver' => env('DB_UNIX_DRIVER', ''),     
        'host' => env('DB_HOST', ''),
        'port' => env('DB_PORT', 1972),
        'database' => env('DB_DATABASE', ''),        
        'username' => env('DB_USERNAME', '_SYSTEM'),
        'password' => env('DB_PASSWORD', 'SYS'),
        'schema' => env('DB_SCHEMA', 'SQLUser'),   // SQLUser is default, avoid changing if possible
        'options' => [
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
            \PDO::ATTR_CASE => \PDO::CASE_LOWER,
            'processor' => \Laracache\Cache\Query\Processors\Processor::class,
            'grammar' => [
                'query' => \Laracache\Cache\Query\Grammars\Grammar::class,
                'schema' => \Laracache\Cache\Schema\Grammars\Grammar::class,
            ],
        ],
    ],
];
