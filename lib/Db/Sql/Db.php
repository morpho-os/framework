<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Db\Sql;

abstract class Db {
    /**
     * @var \PDO
     */
    protected $connection;

    public const MYSQL_DRIVER  = 'mysql';
    public const SQLITE_DRIVER = 'sqlite';

    public function __construct($optionsOrConnection) {
        $this->connection = $connection = $optionsOrConnection instanceof \PDO
            ? $optionsOrConnection
            : $this->newPdoConnection($optionsOrConnection);
        $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $connection->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [__NAMESPACE__ . '\\Result', []]);
        // @TODO
        //$db->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false);
    }

    public static function connect(array $options): self {
        $driver = $options['driver'];
        unset($options['driver']);
        switch ($driver) {
            case self::MYSQL_DRIVER:
                $db = new MySql\Db($options);
                break;
            case self::SQLITE_DRIVER:
                $db = new Sqlite\Db($options);
                break;
            default:
                throw new \UnexpectedValueException();
        }
        return $db;
    }

    public function connection(): \PDO {
        return $this->connection;
    }

    abstract public function query(): Query;

    // For SELECT use prepare feature.
    public function quote($val, int $type = \PDO::PARAM_STR): string {
        return $this->connection->quote($val, $type);
    }

    abstract public function schemaManager(): SchemaManager;

    public function select(string $sql, array $args = null): \PDOStatement {
        return $this->eval('SELECT ' . $sql, $args);
    }

    public function lastInsertId(string $seqName = null): string {
        return $this->connection->lastInsertId($seqName);
    }

    public function insertRow(string $tableName, array $row): void {
        // @TODO: Use InsertQuery
        $query = $this->query();
        $sql = 'INSERT INTO ' . $query->identifier($tableName) . '(';
        $sql .= implode(', ', $query->identifiers(array_keys($row))) . ') VALUES (' . implode(', ', $query->positionalPlaceholders($row)) . ')';
        $this->eval($sql, array_values($row));
    }

    abstract public function insertRows(string $tableName, array $rows): void;

    /**
     * @param array|string $whereCondition
     * @param array|null $whereConditionArgs
     */
    public function deleteRows(string $tableName, $whereCondition, array $whereConditionArgs = null): void {
        // @TODO: use DeleteQuery
        $query = $this->query();
        [$whereSql, $whereArgs] = $query->whereClause($whereCondition, $whereConditionArgs);
        $sql = 'DELETE FROM ' . $query->identifier($tableName)
            . $whereSql;
        /*$stmt = */$this->eval($sql, $whereArgs);
        //return $stmt->rowCount();
    }

    /**
     * @param array|string $whereCondition
     * @param array|null $whereConditionArgs
     */
    public function updateRows(string $tableName, array $row, $whereCondition, array $whereConditionArgs = null): void {
        // @TODO: Use UpdateQuery
        $query = $this->query();
        $sql = 'UPDATE ' . $query->identifier($tableName)
            . ' SET ' . implode(', ', $query->namedPlaceholders($row));
        $args = array_values($row);
        if (null !== $whereCondition) {
            [$whereSql, $whereArgs] = $query->whereClause($whereCondition, $whereConditionArgs);
            if ($whereSql !== '') {
                $sql .= $whereSql;
                $args = array_merge($args, $whereArgs);
            }
        }
        $this->eval($sql, $args);
    }

    public function eval(string $sql, array $args = null): \PDOStatement {
        if ($args) {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($args);
            return $stmt;
        }
        return $this->connection->query($sql);
    }

    public function transaction(callable $transaction) {
        $this->connection->beginTransaction();
        try {
            $result = $transaction($this);
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
        return $result;
    }

    public function inTransaction(): bool {
        return $this->connection->inTransaction();
    }

    public function driverName(): string {
        return $this->connection->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    public static function availableDrivers(): array {
        return \PDO::getAvailableDrivers();
    }

    abstract protected function newPdoConnection(array $options): \PDO;
}
