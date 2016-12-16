<?php
namespace Morpho\Db\Sql;

use Morpho\Base\NotImplementedException;

class Db {
    protected $db;

    protected $schemaManager;

    protected $query;

    const MYSQL_DRIVER  = 'mysql';
    const SQLITE_DRIVER = 'sqlite';

    public function __construct($optionsOrConnection) {
        $this->db = $db = $optionsOrConnection instanceof \PDO
            ? $optionsOrConnection
            : static::connect($optionsOrConnection);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $db->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [__NAMESPACE__ . '\\Result', []]);
        // @TODO
        //$db->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false);
    }

    public static function connect(array $options): \PDO {
        $driver = $options['driver'];
        unset($options['driver']);
        switch ($driver) {
            case self::MYSQL_DRIVER:
                $pdo = MySql\Db::connect($options);
                break;
            case self::SQLITE_DRIVER:
                $pdo = Sqlite\Db::connect($options);
                break;
            default:
                throw new \UnexpectedValueException();
        }
        return $pdo;
    }

    public function query(): Query {
        if (null === $this->query) {
            $class = $this->implNs() . '\\Query';
            $this->query = new $class();
        }
        return $this->query;
    }

    public function schemaManager(): SchemaManager {
        if (null === $this->schemaManager) {
            $class = $this->implNs() . '\\SchemaManager';
            $this->schemaManager = new $class($this);
        }
        return $this->schemaManager;
    }

    public function select(string $sql, array $args = null): \PDOStatement {
        return $this->eval('SELECT ' . $sql, $args);
    }

    public function lastInsertId(string $seqName = null): string {
        return $this->db->lastInsertId($seqName);
    }

    public function insertRow(string $tableName, array $row)/*: void*/ {
        $query = $this->query();
        $sql = 'INSERT INTO ' . $query->identifier($tableName) . '(';
        $sql .= implode(', ', $query->identifiers(array_keys($row))) . ') VALUES (' . implode(', ', $query->positionalPlaceholders($row)) . ')';
        $this->eval($sql, array_values($row));
    }

    public function insertRows(string $tableName, array $rows/* @TODO:, int $rowsInBlock = 100*/)/*: void */ {
        // @TODO: Handle $rowsInBlock
        $args = [];
        $keys = null;
        foreach ($rows as $row) {
            if (null === $keys) {
                $keys = array_keys($row);
            }
            $args = array_merge($args, array_values($row));
        }
        $query = $this->query();
        $valuesClause = ', (' . implode(', ', $query->positionalPlaceholders($rows)) . ')';
        $sql = 'INSERT INTO ' . $query->identifier($tableName) . ' (' . implode(', ', $query->identifiers($keys)) . ') VALUES ' . ltrim(str_repeat($valuesClause, count($rows)), ', ');
        $this->eval($sql, $args);
    }

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
    public function updateRows(string $tableName, array $row, $whereCondition, array $whereConditionArgs = null)/*: void */ {
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

    public function getCurrentDriverName(): string {
        return $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    public static function getAvailableDrivers(): array {
        return \PDO::getAvailableDrivers();
    }

    protected function implNs(): string {
        $driver = $this->getCurrentDriverName();
        switch ($driver) {
            case self::MYSQL_DRIVER:
                $ns = __NAMESPACE__ . '\\MySql';
                break;
            case self::SQLITE_DRIVER:
                $ns = __NAMESPACE__ . '\\Sqlite';
                break;
            default:
                throw new NotImplementedException("Implementation of the SchemaManager for the driver '$driver' is missing");
        }
        return $ns;
    }
}
