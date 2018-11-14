# InterSystems Caché provider for Laravel 5 (ODBC)

## Laracaché

Laracaché is an InterSystems Caché database driver package for [Laravel](http://laravel.com/). Laracaché is an extension of [Illuminate/Database](https://github.com/illuminate/database) that uses the [php-odbc](http://php.net/odbc) extension to communicate with Caché. This package plays well with [Eloquent](https://laravel.com/docs/master/eloquent).

## Quick Installation

```bash
composer require jeandormehl/laracache
```

## Service Provider (Optional on Laravel 5.5+)

Register Laracaché by editing `config/app.php`, find the providers key and add:

```php
Laracache\Cache\ServiceProvider::class
```

## Configuration

Publish a configuration file by running the following Artisan command.

```bash
php artisan vendor:publish --tag=isc
```
This will copy the configuration file to `config/isc.php`.

```php
'isc' => [
    'driver'   => 'odbc',
    'dsn'      => env('DB_DSN', 'odbc:cache'), // 'odbc:' prefix is required
    'host'     => env('DB_HOST', '127.0.0.1'), // intersystems cache server ip
    'database' => env('DB_DATABASE', ''), // namespace
    'username' => env('DB_USERNAME', ''),
    'password' => env('DB_PASSWORD', ''),
    'schema'   => env('DB_SCHEMA', 'SQLUser'), // SQLUser is default, avoid changing if possible (had some strange results)
    'options'  => [
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
        'processor' => \Laracache\Cache\Query\Processors\Processor::class,
        'grammar' => [
            'query'  => \Laracache\Cache\Query\Grammars\Grammar::class,
            'schema' => \Laracache\Cache\Schema\Grammars\Grammar::class,
        ],
    ],
],
```

## ODBC Drivers for Caché
You will need to ensure that you have the relevant ODBC drivers installed. For Linux distributions I recommended using the unixODBC driver, in specific, **libcacheodbcur6435.so for 64-bit.

[ODBC Installation and Validation on UNIX® Systems](https://docs.intersystems.com/latest/csp/docbook/DocBook.UI.Page.cls?KEY=BGOD_unixinst)

For Windows, setup the ODBC data source in Administrative Tools.

## Test
Check out the [tests](https://github.com/jeandormehl/laracache/tree/master/tests) directory for grammar and connection tests.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
