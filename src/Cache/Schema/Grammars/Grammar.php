<?php

namespace Laracache\Cache\Schema\Grammars;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\SqlServerGrammar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class Grammar extends SqlServerGrammar
{
    public function compileTableExists()
    {
        $sql = 'select * from information_schema.tables where table_name = ?';

        if (config('database.connections.odbc.schema')) {
            $sql .= ' and table_schema = \''
                . config('database.connections.odbc.schema') . '\'';
        }

        return $sql;
    }

    public function compileDropIfExists(Blueprint $blueprint, Fluent $command)
    {
        return ($this->tableExists($blueprint))
            ? $this->compileDrop($blueprint, $command)
            : '';
    }

    public function wrapTable($table)
    {
        return $table instanceof Blueprint ? $table->getTable() : $table;
    }

    public function wrap($value, $prefixAlias = false)
    {
        return $value instanceof Fluent
            ? $value->name
            : $value;
    }

    protected function tableExists(Blueprint $blueprint)
    {
        return DB::table('information_schema.tables')
            ->where('table_name', $this->wrapTable($blueprint))
            ->first();
    }
}
