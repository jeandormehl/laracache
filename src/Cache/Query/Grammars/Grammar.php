<?php

namespace Laracache\Cache\Query\Grammars;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\SqlServerGrammar;

class Grammar extends SqlServerGrammar
{
    protected function wrapValue($value)
    {
        return $value;
    }

    protected function compileAnsiOffset(Builder $query, $components)
    {
        if (!$query->limit) {
            $query->limit = 'all';
        }

        if (empty($components['orders'])) {
            $components['orders'] = 'order by 1';
            $components['offset'] = null;
            $components['limit'] = null;
        }

        $components['columns'] = $this->compileOver($this->columnize($query->columns));
        $sql = $this->concatenate($components);

        return $this->compileTableExpression($sql, $query);
    }

    protected function compileOver($orderings)
    {
        return "select top all {$orderings}";
    }

    protected function compileRowConstraint($query)    {
        $start = $query->offset + 1;
        if ($query->limit > 0) {
            $finish = $query->offset + $query->limit;
            return "between {$start} and {$finish}";
        }
        return ">= {$start}";
    }

    protected function compileTableExpression($sql, $query)
    {
        $start = $query->offset+1;
        $constraint = $this->compileRowConstraint($query);

        return "select *, %vid from ({$sql}) where %vid {$constraint}";
    }

     /**
     *
     *  This is a compatible method that usually worked with old version of SqlServerGramar.
     *
     *  This PR introduced a braking change, once Laravel now supports only SQLServer 2017+
     *  https://github.com/laravel/framework/pull/39863 (see changed files)
     *
     *  Just copied over old method, before Laravel PR got merged.
     *
     **/
     public function compileSelect(Builder $query)
     {
         if (! $query->offset) {
             return parent::compileSelect($query);
         }

         // If an offset is present on the query, we will need to wrap the query in
         // a big "ANSI" offset syntax block. This is very nasty compared to the
         // other database systems but is necessary for implementing features.
         if (is_null($query->columns)) {
             $query->columns = ['*'];
         }

         return $this->compileAnsiOffset(
             $query,
             $this->compileComponents($query)
         );
     }
}
