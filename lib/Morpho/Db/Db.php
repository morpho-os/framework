<?php
//declare(strict_types=1);
namespace Morpho\Db;

use Morpho\Base\ArrayTool;
use Morpho\Base\NotImplementedException;
use function Morpho\Base\any;

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
        return (bool) $this->selectCell($sql, $args);
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

    protected function fetchColumn(string $sql, array $args = []): array {
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

    public function insertRow(string $tableName, array $row) {
        $sql = "INSERT INTO " . $this->quoteIdentifier($tableName) . '(';
        $sql .= implode(', ', $this->quoteIdentifiers(array_keys($row))) . ') VALUES (' . implode(', ', $this->positionalPlaceholders($row)) . ')';
        $this->query($sql, array_values($row));
    }
    
    public function lastInsertId(string $seqName = null): string {
        return $this->conn->lastInsertId($seqName);
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

    public function updateRow(string $tableName, array $row, $whereCondition, array $whereConditionArgs = null) {
        $sql = 'UPDATE ' . $this->quoteIdentifier($tableName)
            . ' SET ' . implode(', ', $this->namedPlaceholders($row));
        $args = array_values($row);
        if (null !== $whereCondition) {
            if (!is_array($whereCondition)) {
                throw new NotImplementedException();
            }
            $sql .= ' ' . $this->whereSql(
                $this->andSql(
                    $this->namedPlaceholders($whereCondition)
                )
            );
            $args = array_merge($args, array_values($whereCondition));
            if (null !== $whereConditionArgs) {
                throw new NotImplementedException();
                //$args = array_merge($args, array_values($whereConditionArgs));
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

    public function createDatabase(string $dbName) {
        $this->query("CREATE DATABASE $dbName CHARACTER SET utf8 COLLATE utf8_general_ci");
    }

    public function deleteDatabase(string $dbName) {
        throw new NotImplementedException(__METHOD__);
    }

    public function createTables(array $tableDefinitions) {
        foreach ($tableDefinitions as $tableName => $tableDefinition) {
            $this->createTable($tableName, $tableDefinition);
        }
    }

    public function createTable(string $tableName, array $tableDefinition) {
        list($sql, $args) = $this->getCreateTableSqlArgsFromDefinition($tableName, $tableDefinition);
        $this->query($sql, $args);
    }

    public function getCreateTableSqlArgsFromDefinition(string $tableName, array $tableDefinition): array {
        ArrayTool::ensureHasOnlyKeys($tableDefinition, ['columns', 'fks', 'indexes', 'pk', 'description']);

        list($pkColumns, $columns) = $this->columnsDefinitionToSqlArray($tableDefinition['columns']);

        if (isset($tableDefinition['fks'])) {
            foreach ($tableDefinition['fks'] as $fkDefinition) {
                $columns[] = 'FOREIGN KEY (' . $this->quoteIdentifier($fkDefinition['childColumn']) . ')'
                    . ' REFERENCES ' . $this->quoteIdentifier($fkDefinition['parentTable'])
                    . '(' . $this->quoteIdentifier($fkDefinition['parentColumn']) . ')';
            }
        }

        if (isset($tableDefinition['indexes'])) {
            foreach ($tableDefinition['indexes'] as $indexName => $indexDefinition) {
                $columns[] = 'KEY'
                    . (is_numeric($indexName)
                        ? ' (' . $this->quoteIdentifier($indexDefinition) . ')'
                        : ' ' . $this->indexDefinitionToSql($indexDefinition));
            }
        }

        if (count($pkColumns)) {
            if (isset($tableDefinition['pk'])) {
                throw new \RuntimeException("Only one PK can be present");
            }
            $columns[] = 'PRIMARY KEY ' . $this->indexDefinitionToSql(['columns' => $pkColumns]);
        } elseif (isset($tableDefinition['pk'])) {
            $columns[] = 'PRIMARY KEY ' . $this->indexDefinitionToSql($tableDefinition['pk']);
        }

        $sql = "CREATE TABLE " . $this->quoteIdentifier($tableName)
            . " (\n"
            . implode(",\n", $columns)
            . "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $args = [];
        if (isset($tableDefinition['description'])) {
            $sql .= "\n, COMMENT=?";
            $args[] = $tableDefinition['description'];
        }
        return [$sql, $args];
    }

    public function getTableDefinition(string $tableName, string $dbName = null): array {
        // The code fragment from the Doctrine MySQL, @TODO: specify where
        $stmt = $this->query("SELECT COLUMN_NAME AS Field, COLUMN_TYPE AS Type, IS_NULLABLE AS `Null`, COLUMN_KEY AS `Key`, COLUMN_DEFAULT AS `Default`, EXTRA AS Extra, COLUMN_COMMENT AS Comment, CHARACTER_SET_NAME AS CharacterSet, COLLATION_NAME AS Collation FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = " . (null === $dbName ? 'DATABASE()' : "'$dbName'") . " AND TABLE_NAME = '" . $tableName . "'");
        if (!$stmt->rowCount()) {
            throw new \RuntimeException("The table '" . (null === $dbName ? $tableName : $dbName . '.' . $tableName) . "' does not exist");
        }
        return $stmt->fetchAll();
    }

    public function getCreateTableSql(string $tableName): string {
        return $this->fetchRows("SHOW CREATE TABLE " . $this->quoteIdentifier($tableName))[0]['Create Table'];
    }

    public function deleteAllTables() {
        $this->deleteTables($this->listTables());
    }

    public function deleteTables(array $tableNames) {
        foreach ($tableNames as $tableName) {
            $this->deleteTable($tableName);
        }
    }

    public function deleteTable(string $tableName) {
        $this->transaction(function () use ($tableName) {
            /*
            $isMySql = $this->connection->getDriver() instanceof MySqlDriver;
            if ($isMySql) {
            */
            $this->query('SET FOREIGN_KEY_CHECKS=0;');
            $this->query('DROP TABLE IF EXISTS ' . $this->quoteIdentifier($tableName));
            /*
            if ($isMySql) {
            }
            */
            $this->query('SET FOREIGN_KEY_CHECKS=1;');
        });
    }

    public function listTables(): array {
        return $this->fetchColumn("SHOW TABLES");
    }

    public function renameTable() {
        throw new NotImplementedException();
    }

    public function renameColumn() {
        throw new NotImplementedException();
    }

    public function listDatabases(): array {
        return $this->fetchColumn("SHOW DATABASES");
    }

    public static function quoteIdentifier(string $name) {
        // @see http://dev.mysql.com/doc/refman/5.7/en/identifiers.html
        return '`' . $name . '`';
    }

    public static function quoteIdentifiers(array $identifiers): array {
        $ids = [];
        foreach ($identifiers as $identifier) {
            $ids[] = self::quoteIdentifier($identifier);
        }
        return $ids;
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

    protected function isIntegerType($type) {
        return $this->isOneOfTypes($type, ['tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint']);
    }

    protected function isFloatingPointType($type) {
        return $this->isOneOfTypes($type, ['float', 'double', 'real', 'double precision']);
    }

    protected function isOneOfTypes(string $type, array $types) {
        return any(
            function ($expectedType) use ($type) {
                return $this->typesEqual($type, $expectedType);
            },
            $types
        );
    }

    protected function typesEqual(string $type1, string $type2) {
        return 0 === stripos($type1, $type2);
    }

    protected function columnsDefinitionToSqlArray(array $columnsDefinition) {
        $sql = [];
        $pkColumns = [];
        foreach ($columnsDefinition as $columnName => $columnDefinition) {
            ArrayTool::ensureHasOnlyKeys($columnDefinition, ['type', 'nullable', 'scale', 'precision', 'default', 'unsigned', 'length']);

            $columnDefinitionSql = '';
            $columnType = $columnDefinition['type'];

            // @TODO: Add 'fk' type.

            if ($this->typesEqual($columnType, 'pk')) {
                $columnDefinitionSql .= 'int unsigned NOT NULL AUTO_INCREMENT';
                $pkColumns[] = $columnName;
            } else {
                $columnDefinitionSql .= $columnType;

                if ($this->isIntegerType($columnType)) {
                    $columnDefinitionSql .= isset($columnDefinition['unsigned']) ? ' unsigned' : '';
                } elseif ($this->isFloatingPointType($columnType)) {
                    // Precision is the total number of digits in a number.
                    // Scale is the number of digits to the right of the decimal point in a number.
                    // For the number -999.9999, precision == 7 and scale == 4.
                    $columnDefinitionSql .= '(' . $columnDefinition['precision'] . ',' . $columnDefinition['scale'] . ')';
                } elseif ($this->isOneOfTypes($columnType, ['char', 'varchar'])) {
                    $columnDefinitionSql .= '(' . (isset($columnDefinition['length']) ? $columnDefinition['length'] : '255') . ')';
                }

                if (!isset($columnDefinition['nullable'])) {
                    // By default a column can't contain NULL.
                    $columnDefinitionSql .= ' NOT NULL';
                }
                if (isset($columnDefinition['default'])) {
                    $columnDefinitionSql .= ' DEFAULT ' . $columnDefinition['default'];
                }
            }

            $sql[] = $this->quoteIdentifier($columnName) . ' ' . $columnDefinitionSql;
        }
        return [$pkColumns, $sql];
    }

    protected function indexDefinitionToSql(array $indexDefinition) {
        $sql = [];
        if (isset($indexDefinition['name'])) {
            $sql[] = $indexDefinition['name'];
        }
        if (isset($indexDefinition['type'])) {
            $sql[] = $indexDefinition['type'];
        }
        $sql[] = '('
            . (is_array($indexDefinition['columns'])
                ? implode(', ', array_map([$this, 'quoteIdentifier'], $indexDefinition['columns']))
                : $this->quoteIdentifier($indexDefinition['columns']))
            . ')';
        if (isset($indexDefinition['option'])) {
            $sql[] = $indexDefinition['option'];
        }
        return implode(' ', $sql);
    }
}