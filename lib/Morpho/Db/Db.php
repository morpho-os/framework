<?php
declare(strict_types=1);

namespace Morpho\Db;

use Morpho\Base\ArrayTool;
use Morpho\Base\NotImplementedException;
use function Morpho\Base\some;

class Db {
    private $db;

    public function __construct(array $config) {
        $dsn = isset($config['dsn'])
            ? $config['dsn']
            : $config['driver'] . ':dbname=' . $config['db'] . ';' . $config['host'] . ';charset=UTF8';
        $this->db = $db = new \PDO(
            $dsn,
            isset($config['user'])
                ? $config['user']
                : '',
            isset($config['password'])
                ? $config['password']
                : ''
        );
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }

    public function sql() {
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

    public function deleteRows(string $tableName, $whereCondition, array $whereConditionArgs = null): int {
        throw new NotImplementedException();
/*
        $whereExpr = is_array($whereCondition)
            ? $this->andSql($this->namedPlaceholders($whereCondition))
            : $whereCondition;
        $sql = 'DELETE FROM ' . $this->quoteName($tableName)
            . ' WHERE ' . $whereExpr;
        $stmt = $this->query($sql, $whereConditionArgs);
        return $stmt->rowCount();
*/
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

    public function createTables(array $tableDefinitions) {
        foreach ($tableDefinitions as $tableName => $tableDefinition) {
            $this->createTable($tableName, $tableDefinition);
        }
    }

    public function createTable(string $tableName, array $tableDefinition) {
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

        $this->query($sql, $args);
    }

    public function getTableDefinition($tableName, $dbName = null) {
        // The code fragment from the Doctrine MySQL, @TODO: specify where
        $stmt = $this->query("SELECT COLUMN_NAME AS Field, COLUMN_TYPE AS Type, IS_NULLABLE AS `Null`, COLUMN_KEY AS `Key`, COLUMN_DEFAULT AS `Default`, EXTRA AS Extra, COLUMN_COMMENT AS Comment, CHARACTER_SET_NAME AS CharacterSet, COLLATION_NAME AS Collation FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = " . (null === $dbName ? 'DATABASE()' : "'$dbName'") . " AND TABLE_NAME = '" . $tableName . "'");
        if (!$stmt->rowCount()) {
            throw new \RuntimeException("The table '" . (null === $dbName ? $tableName : $dbName . '.' . $tableName) . "' does not exist");
        }
        return $stmt->fetchAll();
    }

    public function getCreateTableSql($tableName): string {
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

    /**
     * @param string $tableName
     */
    public function deleteTable($tableName) {
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

    public function listTables() {
        return $this->fetchColumn("SHOW TABLES");
    }

    public function renameTable() {
        throw new NotImplementedException();
    }

    public function renameColumn() {
        throw new NotImplementedException();
    }

    public static function quoteIdentifier($name) {
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

    protected function isOneOfTypes($type, array $types) {
        return some(
            function ($expectedType) use ($type) {
                return $this->typesEqual($type, $expectedType);
            },
            $types
        );
    }

    protected function typesEqual($type1, $type2) {
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