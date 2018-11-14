<?php

namespace Laracache\Cache\Connectors;

use Illuminate\Database\Connectors\Connector as IlluminateConnector;
use Illuminate\Database\Connectors\ConnectorInterface;
use Laracache\Pdo\Cache;

class Connector extends IlluminateConnector implements
    ConnectorInterface
{
    public function connect(array $config)
    {
        $options = $this->getOptions($config);
        $dsn     = array_get($config, 'dsn');

        return $this->createConnection($dsn, $config, $options);
    }

    protected function createPdoConnection($dsn, $username, $password, $options)
    {
        return new Cache($dsn, $username, $password, $options);
    }
}
