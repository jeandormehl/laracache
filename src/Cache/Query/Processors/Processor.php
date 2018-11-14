<?php

namespace Laracache\Cache\Query\Processors;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\Processor as IlluminateProcessor;

class Processor extends IlluminateProcessor
{
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        $query->getConnection()->insert($sql, $values);
        $id = $this->getLastInsertId($query, $sequence);

        return \is_numeric($id) ? (int) $id : $id;
    }

    public function getLastInsertId(Builder $query, $sequence = null)
    {
        $result = $query->getConnection()
            ->table($query->from)
            ->latest('id')
            ->first();

        return (\is_object($result))
            ? $result->{$sequence}
            : $result[$sequence];
    }
}
