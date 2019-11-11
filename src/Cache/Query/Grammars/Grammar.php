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
        }

        $components['columns'] = $this->compileOver($this->columnize($query->columns));
        $sql                   = $this->concatenate($components);

        return $this->compileTableExpression($sql, $query);
    }

    protected function compileOver($orderings)
    {
        return "select top all {$orderings}";
    }

    protected function compileTableExpression($sql, $query)
    {
        $constraint = $this->compileRowConstraint($query);

        return "select *, %vid from ({$sql}) where %vid {$constraint}";
    }
}
