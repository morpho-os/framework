<?php
namespace Morpho\Db;

class Db {
    private $conn;

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

    public function sqlQuery(): SqlQuery {
        return new SqlQuery();
    }

    public function selectRows(string $sql, array $args = []): array {
        return $this->fetchRows('SELECT ' . $sql, $args);
    }

    /**
     * @return false|null|string
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
        return $this->query($sql, $args)
            ->fetchAll();
    }

    /**
     * @return false|null|string
     */
    public function fetchRow(string $sql, array $args = []) {
        return $this->query($sql, $args)
            ->fetch(\PDO::FETCH_ASSOC);
    }

    public function fetchColumn(string $sql, array $args = []): array {
        return $this->query($sql, $args)
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function fetchCell(string $sql, array $args) {
        return $this->query($sql, $args)
            ->fetchColumn(0);
    }

    public function fetchMap(string $sql, array $args): array {
        return $this->query($sql, $args)
            ->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    public function lastInsertId(string $seqName = null): string {
        return $this->conn->lastInsertId($seqName);
    }

    public function insertRow(string $tableName, array $row) {
        $sql = "INSERT INTO " . $this->quoteIdentifier($tableName) . '(';
        $sql .= implode(', ', $this->quoteIdentifiers(array_keys($row))) . ') VALUES (' . implode(', ', $this->positionalPlaceholders($row)) . ')';
        $this->query($sql, array_values($row));
    }

    public function deleteRows(string $tableName, $whereCondition, array $whereConditionArgs = null): int {
        if (is_array($whereCondition) && count($whereCondition)) {
            $whereConditionArgs = array_values($whereCondition);
            $whereCondition = $this->andSql($this->namedPlaceholders($whereCondition));
        }
        $sql = 'DELETE FROM ' . $this->quoteIdentifier($tableName)
            . (!empty($whereCondition) ? ' WHERE ' . $whereCondition : '');
        $stmt = $this->query($sql, $whereConditionArgs);
        return $stmt->rowCount();
    }

    /**
     * @param array|string $whereCondition
     * @param array|null $whereConditionArgs
     */
    public function updateRows(string $tableName, array $row, $whereCondition, array $whereConditionArgs = null) {
        $sql = 'UPDATE ' . $this->quoteIdentifier($tableName)
            . ' SET ' . implode(', ', $this->namedPlaceholders($row));
        $args = array_values($row);
        if (null !== $whereCondition) {
            if (!is_array($whereCondition)) {
                $sql .= ' ' . $this->whereSql($whereCondition);
                if (null !== $whereConditionArgs) {
                    $args = array_merge($args, $whereConditionArgs);
                }
            } else {
                if (null !== $whereConditionArgs) {
                    throw new \LogicException('The $whereConditionArgs argument must be empty when the $whereCondition is array');
                }
                $sql .= ' ' . $this->whereSql(
                    $this->andSql(
                        $this->namedPlaceholders($whereCondition)
                    )
                );
                $args = array_merge($args, array_values($whereCondition));
            }
        }
        $this->query($sql, $args);
    }

    public function query(string $sql, array $args = null): \PDOStatement {
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

    public function useDatabase(string $dbName) {
        $this->query("USE $dbName");
    }

    public static function quoteIdentifiers(array $identifiers): array {
        $ids = [];
        foreach ($identifiers as $identifier) {
            $ids[] = self::quoteIdentifier($identifier);
        }
        return $ids;
    }

    public static function quoteIdentifier(string $name) {
        // @see http://dev.mysql.com/doc/refman/5.7/en/identifiers.html
        return '`' . $name . '`';
    }

    public static function andSql(array $expr): string {
        return implode(' AND ', $expr);
    }

    public static function orSql(array $expr): string {
        return implode(' OR ', $expr);
    }

    public static function whereSql(string $sql): string {
        return 'WHERE ' . $sql;
    }

    public static function namedPlaceholders(array $row): array {
        $placeholders = [];
        foreach ($row as $key => $value) {
            $placeholders[] = self::quoteIdentifier($key) . ' = ?';
        }
        return $placeholders;
    }

    public static function positionalPlaceholders(array $row): array {
        return array_fill(0, count($row), '?');
    }
}