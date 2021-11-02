# InterSystems Caché provider for Laravel (ODBC)

<p>
  <img src="https://img.shields.io/packagist/l/jeandormehl/laracache" /> 
  <img src="https://img.shields.io/travis/jeandormehl/laracache" /> 
  <img src="https://img.shields.io/packagist/v/jeandormehl/laracache.svg" /> 
  <img src="https://img.shields.io/packagist/dt/jeandormehl/laracache.svg" /> 
</p>

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

## Environment variables

Modify your .env file to look similar to this. Windows users only need set `DB_CONNECTION` and `DB_WIN_DSN` variables. Unix users should set all other variables as seen below:

```conf
DB_CONNECTION=isc
DB_WIN_DSN=
DB_UNIX_DRIVER=/usr/lib/intersystems/odbc/bin/libcacheodbcur6435.so
DB_HOST=127.0.0.1
DB_PORT=1972
DB_DATABASE=LARAVEL
DB_USERNAME=_SYSTEM
DB_PASSWORD=SYS
```

## Configuration

Publish a configuration file by running the following Artisan command.

```bash
php artisan vendor:publish --tag=isc
```
This will copy the configuration file to `config/isc.php`.

```php
'isc' => [
    'driver'      => 'odbc',
    'win_dsn'     => env('DB_WIN_DSN', ''),         // windows users only
    'unix_driver' => env('DB_UNIX_DRIVER', ''),     // unix users only
    'host'        => env('DB_HOST', ''),
    'port'        => env('DB_PORT', 1972),
    'database'    => env('DB_DATABASE', ''),        // namespace
    'username'    => env('DB_USERNAME', '_SYSTEM'),
    'password'    => env('DB_PASSWORD', 'SYS'),
    'schema'      => env('DB_SCHEMA', 'SQLUser'),   // SQLUser is default, avoid changing if possible
    'options'     => [
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
        'processor'                   => \Laracache\Cache\Query\Processors\Processor::class,
        'grammar'                     => [
            'query'  => \Laracache\Cache\Query\Grammars\Grammar::class,
            'schema' => \Laracache\Cache\Schema\Grammars\Grammar::class,
        ],
    ],
],
```

## ODBC Drivers for Caché
You will need to ensure that you have the relevant ODBC drivers installed. For Linux distributions I recommended using the unixODBC driver, in specific, **`libcacheodbcur6435.so`** for 64-bit. If you have any trouble try to switch to **`libcacheodbcur64.so`**.

### unixODBC
Download, untar and build the unixODBC package. This example uses version 2.3.7.

```bash
# get unixODBC
wget -q ftp://ftp.unixodbc.org/pub/unixODBC/unixODBC-2.3.7.tar.gz

# untar the package
sudo tar -xzvf unixODBC-2.3.7.tar.gz

# navigate to build file path, in this case, ~/unixODBC-2.3.7
cd ~/unixODBC-2.3.7

# build the package - modify prefix, sysconfdir and doc location as needed
sudo -s <<EOF
./configure --prefix=/usr --sysconfdir=/etc \
  && make \
  && make install \
  && find doc -name "Makefile*" -delete \
  && chmod 644 doc/{lst,ProgrammerManual/Tutorial}/* \
  && install -v -m755 -d /usr/share/doc/unixODBC-2.3.7 \
  && cp -v -R doc/* /usr/share/doc/unixODBC-2.3.7
EOF
```

### php-odbc
Ensure php-odbc extension is installed. This example uses Apache & PHP 7.2.

```bash
sudo apt-get -y update
sudo apt-get -y install php-odbc

# restart services
sudo service apache2 restart
sudo service php7.2-fpm restart
```

### InterSystems ODBC Drivers
Download, untar and install. This example uses the 2018.1.0.184.0 build for Ubuntu 64bit. Find available drivers at this link:
[InterSystems ODBC Drivers](ftp://ftp.intersys.com/pub/cache/odbc)

```bash
# download drivers
wget -q ftp://ftp.intersys.com/pub/cache/odbc/2018/ODBC-2018.1.0.184.0-lnxubuntux64.tar.gz

# create a directory to hold drivers and copy tar file to it
sudo mkdir -p /usr/lib/intersystems/odbc
sudo cp ODBC-2018.1.0.184.0-lnxubuntux64.tar.gz /usr/lib/intersystems/odbc

# untar the file and run installer
sudo tar -xzvf /usr/lib/intersystems/odbc/ODBC-2018.1.0.184.0-lnxubuntux64.tar.gz
sudo /usr/lib/intersystems/odbc/ODBCinstall
```

### /etc/odbc.ini
After completeing the above steps, you should have a file located in /etc called odbc.ini. Edit this file using vi or nano. It should look something like this:

```conf
[ODBC Data Sources]
cache=cache

[cache]
Driver                = /usr/lib/intersystems/odbc/bin/libcacheodbcur6435.so
Description           = InterSystems Cache ODBC Connection
Protocol              = TCP
Query Timeout         = 1
Static Cursors        = 0
Authentication Method = 0
```

Register and create symlink to the cursor.

```bash
# register
sudo odbcinst -i -s -f /etc/odbc.ini

# create the symlink
sudo ln -s /usr/lib/x86_64-linux-gnu/libodbccr.so.2.0.0 /usr/lib/x86_64-linux-gnu/odbc/libodbccr.so
```

[ODBC Installation and Validation on UNIX® Systems](https://docs.intersystems.com/latest/csp/docbook/DocBook.UI.Page.cls?KEY=BGOD_unixinst)

For Windows, setup the ODBC data source in Administrative Tools and set the `win_dns` setting in the config file, `isc.php` to the name of your ODBC Data Source.

## Test
Check out the [tests](https://github.com/jeandormehl/laracache/tree/master/tests) directory for grammar and connection tests.

```bash
./vendor/bin/phpunit
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
