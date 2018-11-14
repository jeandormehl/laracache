<?php

namespace Laracache\Cache\Eloquent;

use Illuminate\Database\Eloquent\Model as IlluminateModel;
use Illuminate\Database\Query\Builder as IlluminateQueryBuilder;
use Laracache\Cache\Query\Builder;
use Laracache\Cache\Query\Grammars\Grammar;

class Model extends IlluminateModel
{
    protected function newBaseQueryBuilder()
    {
        $conn    = $this->getConnection();
        $grammar = $conn->getQueryGrammar();

        if ($grammar instanceof Grammar) {
            return new Builder($conn, $grammar, $conn->getPostProcessor());
        }

        return new IlluminateQueryBuilder(
            $conn,
            $grammar,
            $conn->getPostProcessor()
        );
    }
}
