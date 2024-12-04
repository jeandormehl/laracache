<?php

namespace Laracache\Pdo;

use Laracache\Pdo\Cache\Exceptions\CacheException;
use Laracache\Pdo\Cache\Statement;
use PDO;
use PDOStatement;

class Cache extends PDO
{
    /**
     * @var resource
     */
    protected $dbh;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var bool
     */
    protected $inTransaction = false;

    /**
     * Creates a PDO instance representing a connection to a database.
     *
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param array  $options
     */
    public function __construct($dsn, $username, $password, array $options = [])
    {
        if (\mb_substr($dsn, 0, 5) !== 'odbc:') {
            $dsn = 'odbc:'.$dsn;
        }

        // must call pdo constructor - exception thrown
        parent::__construct($dsn, $username, $password, $options);

        $dsn = \preg_replace('/^odbc:/', '', $dsn);
        $this->options = $options;

        $this->initializeConnection($dsn, $username, $password, $options);
    }

    /**
     * Prepares a statement for execution and returns a statement object.
     *
     * @param string $statement
     * @param array  $options
     *
     * @return Statement
     */
    public function prepare($statement, $options = null): PDOStatement|false
    {
        $options = ($options === null)
            ? $this->options
            : $options;

        if (!\is_array($options)) {
            $options = [];
        }

        return new Statement($this, $this->dbh, $statement, $options);
    }

    /**
     * Executes an SQL statement.
     *
     * @param string
     *
     * @return int
     */
    public function exec($statement): int
    {
        return $this->prepare($statement)->execute();
    }

    /**
     * Initiates a transaction.
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        $this->setAutoCommit(false);

         return $this->exec('START TRANSACTION');
    }

    /**
     * Commits a transaction.
     *
     * @return bool
     */
    public function commit(): bool
    {
        $this->exec('COMMIT');
        $this->setAutoCommit(true);

        return (!\odbc_error($this->dbh))
            ? @\odbc_commit($this->dbh)
            : $this->rollBack();
    }

    /**
     * Rolls back a transaction.
     *
     * @return bool
     */
    public function rollBack(): bool
    {
        $this->exec('ROLLBACK');
        $status = @\odbc_rollback($this->dbh);

        if (!$status) {
            if (\odbc_error($this->dbh)) {
                $this->throwException();
            }
        }

        return $status;
    }

    /**
     * Checks if inside a transaction.
     *
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->inTransaction;
    }

    /**
     * Get driver options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Connect to cache via odbc.
     */
    private function initializeConnection($dsn, $username, $password, array $options)
    {
        $this->dbh = (\array_key_exists(PDO::ATTR_PERSISTENT, $options)
            && $options[PDO::ATTR_PERSISTENT])
            ? @\odbc_pconnect($dsn, $username, $password)
            : @\odbc_connect($dsn, $username, $password);

        if (!$this->dbh) {
            $this->throwException();
        }
    }

    /**
     * Set the odbc_autocommit value.
     */
    private function setAutoCommit($boolean = true): bool
    {
        @\odbc_autocommit($this->dbh, $boolean);
        $this->inTransaction = !$boolean;

        return $boolean;
    }

    /**
    * Required quote function.
    */
    public function quote(string $string, int $type = PDO::PARAM_STR): string|false
    {
        return $string;
    }

    /**
     * Throws an odbc exception object.
     *
     * @throw CacheException
     */
    private function throwException()
    {
        throw new CacheException(\odbc_errormsg(), (int) \odbc_error());
    }
}
