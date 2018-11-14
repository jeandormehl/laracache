<?php

return [
    'isc' => [
        'driver'   => 'odbc',
        'dsn'      => env('DB_DSN', 'odbc:cache'), // odbc: prefix is required
        'host'     => env('DB_HOST', '127.0.0.1'), // intersystems cache server
        'database' => env('DB_DATABASE', 'LARAVEL'), // namespace
        'username' => env('DB_USERNAME', '_SYSTEM'),
        'password' => env('DB_PASSWORD', 'SYS'),
        'schema'   => env('DB_SCHEMA', 'SQLUser'), // SQLUser is default, avoid changing if possible
        'options'  => [
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
            'processor'                   => \Laracache\Cache\Query\Processors\Processor::class,
            'grammar'                     => [
                'query'  => \Laracache\Cache\Query\Grammars\Grammar::class,
                'schema' => \Laracache\Cache\Schema\Grammars\Grammar::class,
            ],
        ],
    ],
];
