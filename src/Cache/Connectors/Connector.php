<?php

namespace Laracache\Cache\Connectors;

use Illuminate\Database\Connectors\Connector as IlluminateConnector;
use Illuminate\Database\Connectors\ConnectorInterface;
use InvalidArgumentException;
use Laracache\Pdo\Cache;

class Connector extends IlluminateConnector implements
    ConnectorInterface
{
    public function connect(array $config)
    {
        $options = $this->getOptions($config);
        $dsn     = $this->buildDsn($config);

        return $this->createConnection($dsn, $config, $options);
    }

    protected function createPdoConnection($dsn, $username, $password, $options)
    {
        return new Cache($dsn, $username, $password, $options);
    }

    private function buildDsn($config)
    {
        // windows
        if (\strtoupper(\substr(PHP_OS, 0, 3)) === 'WIN') {
            if (!$config['win_dsn']) {
                throw new InvalidArgumentException('DSN not set in configuration.');
            }

            return $config['win_dsn'];
        }

        if (!$config['unix_driver']
            || !$config['host']
            || !$config['port']
            || !$config['database']
        ) {
            throw new InvalidArgumentException(
                'Invalid configuration. Driver path, host, port and namespace ' .
                'are required.'
            );
        }

        // unix
        $dsn  = 'Driver={' . $config['unix_driver'] . '};';
        $dsn .= 'Server=' . $config['host'] . ';';
        $dsn .= 'PORT=' . $config['port'] . ';';
        $dsn .= 'DATABASE=' . $config['database'];

        return $dsn;
    }
}
