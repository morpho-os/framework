<?php
namespace Morpho\Db\Sql;

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
    }

    public static function connect($options): \PDO {
        $driver = $options['driver'];
        unset($options['driver']);
        switch ($driver) {
            case self::MYSQL_DRIVER:
                return MySql\Db::connect($options);
            case self::SQLITE_DRIVER:
                return Sqlite\Db::connect($options);
            default:
                throw new \UnexpectedValueException();
        }
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

    public function selectRows(string $sql, array $args = null): array {
        return $this->fetchRows('SELECT ' . $sql, (array) $args);
    }

    /**
     * @return array|false
     */
    public function selectRow(string $sql, array $args) {
        return $this->fetchRow('SELECT ' . $sql, $args);
    }

    public function selectColumn(string $sql, array $args = null): array {
        return $this->fetchColumn('SELECT ' . $sql, (array) $args);
    }

    /**
     * @return string|null|false
     */
    public function selectCell(string $sql, array $args = null) {
        return $this->fetchCell('SELECT ' . $sql, (array) $args);
    }

    public function selectBool(string $sql, array $args = null): bool {
        return (bool)$this->selectCell($sql, (array) $args);
    }

    public function selectMap(string $sql, array $args = null): array {
        return $this->fetchMap('SELECT ' . $sql, (array) $args);
    }

    public function fetchRows(string $sql, array $args = null): array {
        return $this->eval($sql, (array) $args)
            ->fetchAll();
    }

    /**
     * @return array|false
     */
    public function fetchRow(string $sql, array $args = null) {
        return $this->eval($sql, (array) $args)
            ->fetch(\PDO::FETCH_ASSOC);
    }

    public function fetchColumn(string $sql, array $args = null): array {
        return $this->eval($sql, (array) $args)
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function fetchCell(string $sql, array $args) {
        return $this->eval($sql, $args)
            ->fetchColumn(0);
    }

    public function fetchMap(string $sql, array $args = null): array {
        return $this->eval($sql, (array) $args)
            ->fetchAll(\PDO::FETCH_KEY_PAIR);
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
            default:
                throw new \RuntimeException("Unable to find Schema Manager for the driver '$driver'");
        }
        return $ns;
    }
}
