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
    protected $db;

    public const MYSQL_DRIVER  = 'mysql';
    public const SQLITE_DRIVER = 'sqlite';

    public function __construct($optionsOrConnection) {
        $this->db = $db = $optionsOrConnection instanceof \PDO
            ? $optionsOrConnection
            : $this->newPdoConnection($optionsOrConnection);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $db->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [__NAMESPACE__ . '\\Result', []]);
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

    public function pdo(): \PDO {
        return $this->db;
    }

    // For SELECT use prepare feature.
    public function quote($val, int $type = \PDO::PARAM_STR): string {
        return $this->db->quote($val, $type);
    }

    abstract public function query(): Query;

    abstract public function schemaManager(): SchemaManager;

    public function select(string $sql, array $args = null): \PDOStatement {
        return $this->eval('SELECT ' . $sql, $args);
    }

    public function lastInsertId(string $seqName = null): string {
        return $this->db->lastInsertId($seqName);
    }

    public function insertRow(string $tableName, array $row): void {
        $query = $this->query();
        $sql = 'INSERT INTO ' . $query->identifier($tableName) . '(';
        $sql .= implode(', ', $query->identifiers(array_keys($row))) . ') VALUES (' . implode(', ', $query->positionalPlaceholders($row)) . ')';
        $this->eval($sql, array_values($row));
    }

    abstract public function insertRows(string $tableName, array $rows): void;

    public function deleteRows(string $tableName, $whereCondition, array $whereConditionArgs = null): int {
        $query = $this->query();
        if (is_array($whereCondition) && count($whereCondition)) {
            $whereConditionArgs = array_values($whereCondition);
            $whereCondition = $query->logicalAnd($query->namedPlaceholders($whereCondition));
        }
        $sql = 'DELETE FROM ' . $query->identifier($tableName)
            . (!empty($whereCondition) ? ' WHERE ' . $whereCondition : '');
        $stmt = $this->eval($sql, $whereConditionArgs);
        return $stmt->rowCount();
    }

    /**
     * @param array|string $whereCondition
     * @param array|null $whereConditionArgs
     */
    public function updateRows(string $tableName, array $row, $whereCondition, array $whereConditionArgs = null): void {
        $query = $this->query();
        $sql = 'UPDATE ' . $query->identifier($tableName)
            . ' SET ' . implode(', ', $query->namedPlaceholders($row));
        $args = array_values($row);
        if (null !== $whereCondition) {
            if (!is_array($whereCondition)) {
                $sql .= ' ' . $query->whereClause($whereCondition);
                if (null !== $whereConditionArgs) {
                    $args = array_merge($args, $whereConditionArgs);
                }
            } else {
                if (null !== $whereConditionArgs) {
                    throw new \LogicException('The $whereConditionArgs argument must be empty when the $whereCondition is array');
                }
                $sql .= ' ' . $query->whereClause(
                    $query->logicalAnd(
                        $query->namedPlaceholders($whereCondition)
                    )
                );
                $args = array_merge($args, array_values($whereCondition));
            }
        }
        $this->eval($sql, $args);
    }

    public function eval(string $sql, array $args = null): \PDOStatement {
        if ($args) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($args);
            return $stmt;
        }
        return $this->db->query($sql);
    }

    public function transaction(callable $transaction) {
        $this->db->beginTransaction();
        try {
            $result = $transaction($this);
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
        return $result;
    }

    public function inTransaction(): bool {
        return $this->db->inTransaction();
    }

    public function driverName(): string {
        return $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    public static function availableDrivers(): array {
        return \PDO::getAvailableDrivers();
    }

    abstract protected function newPdoConnection(array $options): \PDO;
}
