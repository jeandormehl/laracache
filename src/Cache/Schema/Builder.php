<?php

namespace Laracache\Cache\Schema;

use Illuminate\Database\Schema\Builder as IlluminateBuilder;
use Illuminate\Support\Facades\DB;
use Laracache\Cache\Connection;

class Builder extends IlluminateBuilder
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);

        if (!$this->grammar) {
            $this->grammar = $connection->getDefaultSchemaGrammar();
        }
    }

    public function dropAllTables()
    {
        $tables = DB::table('information_schema.tables')
            ->select('table_name')
            ->where(
                'table_schema',
                config('database.connections.odbc.schema')
            )
            ->get();

        $this->connection->getPdo()->beginTransaction();

        foreach ($tables as $table) {
            $tableName = (\is_object($table))
                ? $table->TABLE_NAME
                : $table['TABLE_NAME'];

            $this->connection
                ->getPdo()
                ->exec(DB::raw('drop table '.$tableName));
        }

        $this->connection->getPdo()->commit();
    }
}
