<?php

namespace Laracache\Cache;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Laracache\Cache\Connectors\Connector;
use Laracache\Cache\Eloquent\Model;

class ServiceProvider extends IlluminateServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        Model::setConnectionResolver($this->app['db']);
        Model::setEventDispatcher($this->app['events']);

        $this->publishes([
            __DIR__.'/../../config/isc.php' => config_path('isc.php'),
        ], 'isc');
    }

    public function register()
    {
        if (!\file_exists(config_path('isc.php'))) {
            $this->mergeConfigFrom(
                __DIR__.'/../../config/isc.php',
                'database.connections'
            );
        } else {
            $this->mergeConfigFrom(config_path('isc.php'), 'database.connections');
        }

        Connection::resolverFor('odbc', function (
            $connection,
            $database,
            $prefix,
            $config
        ) {
            $connector = new Connector();
            $pdo = $connector->connect($config);

            return new Connection(
                $pdo,
                $database,
                '',
                $config
            );
        });
    }

    public function provides()
    {
        return [];
    }
}
