<?php

use Laracache\Cache\Eloquent\Model;
use Laracache\Cache\Query\Builder;
use Laracache\Cache\Query\Grammars\Grammar;
use Laracache\Cache\Query\Processors\Processor;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class CacheEloquentTest extends TestCase
{
    public function tearDown(): void
    {
        m::close();
    }

    public function testNewBaseQueryBuilderReturnsCacheBuilderForCacheGrammar()
    {
        $model = new CacheEloquentStub();

        $this->mockConnectionForModel($model, 'Cache');

        $builder = $model->newQuery()->getQuery();

        $this->assertInstanceOf(Builder::class, $builder);
        $this->assertInstanceOf(Grammar::class, $builder->getGrammar());
        $this->assertInstanceOf(Processor::class, $builder->getProcessor());
    }

    protected function getConnection()
    {
        $connection =  m::mock('Illuminate\Database\Connection');

        $connection->shouldReceive('getSchemaGrammar')->andReturn(new Grammar($connection));
        $connection->shouldReceive('getSchemaBuilder')->andReturn(Builder::class);
        $connection->shouldReceive('getConfig')->with('prefix_indexes')->andReturn(true);
        $connection->shouldReceive('getTablePrefix')->andReturn('');

        return $connection;
    }

    protected function mockConnectionForModel($model, $database)
    {
        if ($database === 'Cache') {
            $grammarClass = Grammar::class;
            $processorClass = Processor::class;
            $grammar = new $grammarClass($this->getConnection());
            $processor = new $processorClass();

            $connection = m::mock('Illuminate\Database\ConnectionInterface', [
                'getQueryGrammar' => $grammar,
                'getPostProcessor' => $processor,
            ]);

            $resolver = m::mock(
                'Illuminate\Database\ConnectionResolverInterface',
                ['connection' => $connection]
            );

            $class = \get_class($model);
            $class::setConnectionResolver($resolver);

            return;
        }

        $grammarClass = 'Illuminate\Database\Query\Grammars\\' . $database . 'Grammar';
        $processorClass = 'Illuminate\Database\Query\Processors\\' . $database . 'Processor';
        $grammar = new $grammarClass();
        $processor = new $processorClass();

        $connection = m::mock('Illuminate\Database\ConnectionInterface', [
            'getQueryGrammar' => $grammar,
            'getPostProcessor' => $processor,
        ]);

        $resolver = m::mock(
            'Illuminate\Database\ConnectionResolverInterface',
            ['connection' => $connection]
        );

        $class = \get_class($model);

        $class::setConnectionResolver($resolver);
    }
}

class CacheEloquentStub extends Model {}
