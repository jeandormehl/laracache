<?php

use Laracache\Cache\Connectors\Connector;
use Laracache\Pdo\Cache;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class CacheConnectorTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testCreateConnection()
    {
        $connector = new CacheConnectorStub();
        $dsn       = 'Connection String';
        $config    = [
            'driver'   => 'odbc',
            'host'     => 'host',
            'database' => 'database',
            'port'     => 'port',
            'username' => 'username',
            'password' => 'password',
            'charset'  => 'charset',
            'options'  => [],
        ];

        $pdo = $connector->createConnection($dsn, $config, []);

        $this->assertInstanceOf(Cache::class, $pdo);
    }

    public function testOptionResolution()
    {
        $connector = new Connector();

        $connector->setDefaultOptions([0 => 'foo', 1 => 'bar']);
        $this->assertEquals(
            [0 => 'baz', 1 => 'bar', 2 => 'boom'],
            $connector->getOptions(['options' => [0 => 'baz', 2 => 'boom']])
        );
    }
}

class CacheConnectorStub extends Connector
{
    public function createConnection($dsn, array $config, array $options)
    {
        return new CacheStub(
            $dsn,
            $config['username'],
            $config['password'],
            $config['options']
        );
    }
}

class CacheStub extends Cache
{
    public function __construct($dsn, $username, $password, array $options = [])
    {
        return true;
    }
}
