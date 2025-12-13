# InterSystems Caché provider for Laravel (ODBC)

<p>
  <img src="https://img.shields.io/packagist/l/jeandormehl/laracache" /> 
  <img src="https://codecov.io/gh/jeandormehl/laracache/branch/master/graph/badge.svg"/>
  </a>
  <img src="https://img.shields.io/packagist/v/jeandormehl/laracache.svg" /> 
  <img src="https://img.shields.io/packagist/dt/jeandormehl/laracache.svg" /> 
</p>

## Laracaché

Laracaché is an InterSystems Caché database driver package for [Laravel](http://laravel.com/). Laracaché is an extension of [Illuminate/Database](https://github.com/illuminate/database) that uses the [php-odbc](http://php.net/odbc) extension to communicate with Caché. This package plays well with [Eloquent](https://laravel.com/docs/master/eloquent).

## Quick Installation

PHP >= 8.5 and Laravel >= 12
```bash
composer require jeandormehl/laracache
```

## Environment variables

Modify your` .env` file to look similar to this.

```conf
DB_CONNECTION=isc
DB_WIN_DSN=
DB_UNIX_DRIVER=/usr/local/cache/2018/bin/libcacheodbcur64.so
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

## Setup

### 
Create this file `/etc/odbc.ini`

```conf
[ODBC Data Sources]
cache=cache

[cache]
Driver                = /usr/local/cache/2018/bin/libcacheodbcur64.so
Description           = InterSystems Cache ODBC Connection
Protocol              = TCP
Query Timeout         = 1
Static Cursors        = 0
Authentication Method = 0
```

Install these extra packages and extract the Caché driver file.

[ODBC-2018.1.7.721.0-lnxubuntux64.tar.gz]()

```bash
# Extra packages
apt update && apt install -y php8.4-odbc unixodbc libodbccr2 odbcinst

# Create this folder
mkdir -p /usr/local/cache/2018

# Extract the driver to the folder above
tar xvzf ODBC-2018.1.7.721.0-lnxubuntux64.tar.gz -C /usr/local/cache/2018

# Install Caché Driver
cd /usr/local/cache/2018 && ./ODBCinstall 

odbcinst -i -s -f /etc/odbc.ini 

# Binds
ln -s /usr/lib/x86_64-linux-gnu/libodbccr.so.2.0.0 /etc/libodbccr.so

```




# Contribute

You can run this project on VSCODE with Remote Container. Make sure you will use internal VSCODE terminal (inside running container).

```bash
composer install
composer test
composer test:coverage
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
