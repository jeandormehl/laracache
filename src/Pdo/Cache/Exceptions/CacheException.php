<?php

namespace Laracache\Pdo\Cache\Exceptions;

use PDOException;
use Throwable;

class CacheException extends PDOException
{
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            \str_replace(['"', "\n"], ['', ' - '], $message),
            $code,
            $previous
        );
    }
}
