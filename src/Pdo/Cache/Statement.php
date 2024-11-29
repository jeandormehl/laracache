<?php

namespace Laracache\Pdo\Cache;

use Laracache\Pdo\Cache;
use Laracache\Pdo\Cache\Exceptions\CacheException;
use PDO;
use PDOStatement;
use stdClass;

class Statement extends PDOStatement
{
    private $statement;

    private $connection;

    private $dbh;

    private $options = [];

    private $parameters = [];

    private $results = [];

    private $fetchMode = PDO::FETCH_OBJ;

    /**
     * Constructor.
     */
    public function __construct(Cache $connection, $dbh, $statement, array $options = [])
    {
        $this->connection = $connection;
        $this->parameters = $this->parameters($statement);
        $this->statement = @\odbc_prepare(
            $dbh,
            \preg_replace('/(?<=\s|^):[^\s:]++/um', '?', $statement)
        );

        if (\odbc_error()) {
            $this->throwException();
        }

        /* if (\strtolower(\get_resource_type($this->statement)) !== 'odbc result') {
            throw new CacheException(
                'Resource expected of type odbc result; '
                .(string) \get_resource_type($this->statement).' received instead.'
            );
        } */

        $fetchMode = $connection->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE);

        if ($fetchMode) {
            $this->setFetchMode($fetchMode);
        }
    }

    /**
     * Executes a prepared statement.
     *
     * @param array
     *
     * @return bool
     */
    public function execute($inputParameters = null): bool
    {
        $result = @\odbc_execute($this->statement, $this->parameters);
        $this->parameters = [];

        if (\odbc_error()) {
            $this->throwException();
        }

        return $result;
    }

    /**
     * Fetches the next row from a result set.
     *
     * @param null|int $fetchMode
     * @param int      $cursorOrientation
     * @param int      $cursorOffset
     *
     * @return mixed
     */
    public function fetch($fetchMode = null, $cursorOrientation = PDO::FETCH_ORI_NEXT, $cursorOffset = 0): mixed
    {
        if ($fetchMode === null) {
            $fetchMode = $this->fetchMode;
        }

        $toLowercase = ($this->connection->getAttribute(PDO::ATTR_CASE) === PDO::CASE_LOWER);
        $nullToString = ($this->connection->getAttribute(PDO::ATTR_ORACLE_NULLS) === PDO::NULL_TO_STRING);
        $nullEmptyString = ($this->connection->getAttribute(PDO::ATTR_ORACLE_NULLS) === PDO::NULL_EMPTY_STRING);

        switch ($fetchMode) {
            case PDO::FETCH_BOTH:
            case PDO::FETCH_ASSOC:
                $resultSet = @\odbc_fetch_array($this->statement);

                if (\odbc_error()) {
                    $this->throwException();
                }

                if ($resultSet === false) {
                    return false;
                }

                if ($toLowercase) {
                    $resultSet = \array_change_key_case($resultSet);
                }

                return $resultSet;

            case PDO::FETCH_OBJ:
                $resultSet = @\odbc_fetch_array($this->statement);

                if (\odbc_error()) {
                    $this->throwException();
                }

                if ($resultSet === false) {
                    return false;
                }

                if ($toLowercase) {
                    $resultSet = \array_change_key_case($resultSet);
                }

                $object = new stdClass();

                foreach ($resultSet as $field => $value) {
                    if (\is_null($value) && $nullToString) {
                        $resultSet[$field] = '';
                    }

                    if (empty($resultSet[$field]) && $nullEmptyString) {
                        $resultSet[$field] = null;
                    }

                    $object->{$field} = $value;
                }

                return $object;
        }

        return false;
    }

    /**
     * Returns an array containing all of the result set rows.
     *
     * @param int   $fetchMode
     * @param mixed $fetchArgument
     * @param array $ctorArgs
     *
     * @return array
     */
    public function fetchAll(int $mode = PDO::FETCH_OBJ, mixed ...$args): array
    {
        $this->setFetchMode($mode);
        $this->results = [];

        while ($row = $this->fetch()) {
            $mangledObj = \get_mangled_object_vars($row);

		    if ((\is_array($row) || \is_object($row)) && \is_resource(\reset($mangledObj))) {
			    $stmt = new self($this->connection, \reset($mangledObj), $this->options);
                $stmt->execute();
                $stmt->setFetchMode($mode);

                while ($rs = $stmt->fetch()) {
                    $this->results[] = $rs;
                }
            } else {
                $this->results[] = $row;
            }
        }

        return $this->results;
    }

    /**
     * Bind value to statement.
     */
    public function bindValue($parameter, $value, $dataType = null): bool
    {
        $this->parameters[$parameter] = $value;

        return true;
    }

    /**
     * ODBC row count.
     *
     * @return int
     */
    public function rowCount(): int
    {
        return \odbc_num_rows($this->statement);
    }

    /**
     * Set PDO fetch mode.
     *
     * @return bool
     */
    public function setFetchMode(int $mode, mixed ...$args)
    {
        switch ($mode) {
            case PDO::FETCH_ASSOC:
            case PDO::FETCH_BOTH:
            case PDO::FETCH_OBJ:
                $this->fetchMode = $mode;

                break;
            default:
                throw new CacheException('Requested fetch mode is not yet supported.');
        }

        return true;
    }

    /**
     * Extract parameters from statement.
     *
     * @return array
     */
    private function parameters($statement)
    {
        $parameters = [];
        $values = \explode(' ', $statement);
        $count = 0;

        while (\array_key_exists($count, $values)) {
            if (\preg_match('/^:/', $values[$count])) {
                $parameters[$values[$count]] = null;
            }

            ++$count;
        }

        return $parameters;
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
