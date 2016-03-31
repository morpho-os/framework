<?php
namespace Morpho\Db\Sql;

class Db {
    protected $conn;
    
    protected $schemaManager;
    
    protected $query;

    const MYSQL_DRIVER = 'mysql';

    public function __construct($configOrConnection) {
        $this->conn = $db = $configOrConnection instanceof \PDO
            ? $configOrConnection
            : static::createConnection($configOrConnection);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }

    public static function createConnection($config): \PDO {
        $dsn = is_string($config)
            ? $config
            : $config['driver'] . ':dbname=' . $config['db'] . ';' . $config['host'] . ';charset=UTF8';
        return new \PDO(
            $dsn,
            isset($config['user']) ? $config['user'] : '',
            isset($config['password']) ? $config['password'] : ''
        );
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

    public function selectRows(string $sql, array $args = []): array {
        return $this->fetchRows('SELECT ' . $sql, $args);
    }

    /**
     * @return false|array
     */
    public function selectRow(string $sql, array $args) {
        return $this->fetchRow('SELECT ' . $sql, $args);
    }

    public function selectColumn(string $sql, array $args = []): array {
        return $this->fetchColumn('SELECT ' . $sql, $args);
    }

    /**
     * @return string|null|false
     */
    public function selectCell(string $sql, array $args = []) {
        return $this->fetchCell('SELECT ' . $sql, $args);
    }

    public function selectBool(string $sql, array $args = []): bool {
        return (bool)$this->selectCell($sql, $args);
    }

    public function selectMap(string $sql, array $args = []): array {
        return $this->fetchMap('SELECT ' . $sql, $args);
    }

    public function fetchRows(string $sql, array $args = []): array {
        return $this->runQuery($sql, $args)
            ->fetchAll();
    }

    /**
     * @return false|array
     */
    public function fetchRow(string $sql, array $args = []) {
        return $this->runQuery($sql, $args)
            ->fetch(\PDO::FETCH_ASSOC);
    }

    public function fetchColumn(string $sql, array $args = []): array {
        return $this->runQuery($sql, $args)
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function fetchCell(string $sql, array $args) {
        return $this->runQuery($sql, $args)
            ->fetchColumn(0);
    }

    public function fetchMap(string $sql, array $args): array {
        return $this->runQuery($sql, $args)
            ->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    public function lastInsertId(string $seqName = null): string {
        return $this->conn->lastInsertId($seqName);
    }

    public function insertRow(string $tableName, array $row)/*: void*/ {
        $query = $this->query();
        $sql = "INSERT INTO " . $query->identifier($tableName) . '(';
        $sql .= implode(', ', $query->identifiers(array_keys($row))) . ') VALUES (' . implode(', ', $query->positionalPlaceholders($row)) . ')';
        $this->runQuery($sql, array_values($row));
    }

    public function deleteRows(string $tableName, $whereCondition, array $whereConditionArgs = null): int {
        $query = $this->query();
        if (is_array($whereCondition) && count($whereCondition)) {
            $whereConditionArgs = array_values($whereCondition);
            $whereCondition = $query->logicalAnd($query->namedPlaceholders($whereCondition));
        }
        $sql = 'DELETE FROM ' . $query->identifier($tableName)
            . (!empty($whereCondition) ? ' WHERE ' . $whereCondition : '');
        $stmt = $this->runQuery($sql, $whereConditionArgs);
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
        $this->runQuery($sql, $args);
    }

    public function runQuery(string $sql, array $args = null): \PDOStatement {
        if ($args) {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($args);
            return $stmt;
        }
        return $this->conn->query($sql);
    }

    public function transaction(callable $transaction) {
        $this->conn->beginTransaction();
        try {
            $result = $transaction($this);
            $this->conn->commit();
        } catch (\Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
        return $result;
    }

    public function inTransaction(): bool {
        return $this->conn->inTransaction();
    }

    public function getCurrentDriverName(): string {
        return $this->conn->getAttribute(\PDO::ATTR_DRIVER_NAME);
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