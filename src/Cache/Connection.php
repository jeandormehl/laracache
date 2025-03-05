<?php

namespace Laracache\Cache;

use Illuminate\Database\Connection as IlluminateConnection;
use Laracache\Cache\Query\Builder as QueryBuilder;
use Laracache\Cache\Query\Grammars\Grammar as QueryGrammar;
use Laracache\Cache\Query\Processors\Processor;
use Laracache\Cache\Schema\Builder as SchemaBuilder;
use Laracache\Cache\Schema\Grammars\Grammar as SchemaGrammar;

class Connection extends IlluminateConnection
{
    public function table($table, $as = null)
    {
        $processor = $this->getPostProcessor();
        $query = new QueryBuilder($this, $this->getQueryGrammar(), $processor);

        return $query->from($table);
    }

    public function getDefaultQueryGrammar()
    {
        $queryGrammar = $this->getConfig('options.grammar.query');

        return ($queryGrammar)
            ? new $queryGrammar()
            : new QueryGrammar($this);
    }

    public function getDefaultSchemaGrammar()
    {
        $schemaGrammar = $this->getConfig('options.grammar.schema');

        return ($schemaGrammar)
            ? new $schemaGrammar()
            : new SchemaGrammar();
    }

    public function getSchemaBuilder()
    {
        return new SchemaBuilder($this);
    }

    protected function getDefaultPostProcessor()
    {
        $processor = $this->getConfig('options.processor');

        return ($processor)
            ? new $processor()
            : new Processor();
    }
}
