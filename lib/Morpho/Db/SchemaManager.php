<?php
namespace Morpho\Db;

use Morpho\Base\ArrayTool;

class SchemaManager {
    public function __construct(Db $db) {
        $this->db = $db;
    }

    public function listDatabases(): array {
        return $this->db->fetchColumn("SHOW DATABASES");
    }

    public function createDatabase(string $dbName) {
        $this->db->query("CREATE DATABASE $dbName CHARACTER SET utf8 COLLATE utf8_general_ci");
    }

    public function deleteDatabase(string $dbName) {
        $this->db->query("DROP DATABASE $dbName");
    }

    public function createTables(array $tableDefinitions) {
        foreach ($tableDefinitions as $tableName => $tableDefinition) {
            $this->createTable($tableName, $tableDefinition);
        }
    }

    public function createTable(string $tableName, array $tableDefinition) {
        list($sql, $args) = $this->tableDefinitionToSql($tableName, $tableDefinition);
        $this->db->query($sql, $args);
    }

    public function listTables(): array {
        return $this->db->fetchColumn("SHOW TABLES");
    }

    public function deleteTables(array $tableNames) {
        foreach ($tableNames as $tableName) {
            $this->deleteTable($tableName);
        }
    }

    public function deleteTable(string $tableName) {
        $this->db->transaction(function ($db) use ($tableName) {
            /*
            $isMySql = $this->connection->getDriver() instanceof MySqlDriver;
            if ($isMySql) {
            */
            $db->query('SET FOREIGN_KEY_CHECKS=0;');
            $db->query('DROP TABLE IF EXISTS ' . Db::quoteIdentifier($tableName));
            /*
            if ($isMySql) {
            }
            */
            $db->query('SET FOREIGN_KEY_CHECKS=1;');
        });
    }

    public function deleteAllTables() {
        $this->deleteTables($this->listTables());
    }

    public function renameTable(string $oldTableName, string $newTableName) {
        throw new NotImplementedException();
    }

    public function renameColumn() {
        throw new NotImplementedException();
    }

    public static function tableDefinitionToSql(string $tableName, array $tableDefinition): array {
        ArrayTool::assertHasOnlyKeys($tableDefinition, ['columns', 'foreignKeys', 'indexes', 'primaryKey', 'description', 'uniqueKeys']);

        list($pkColumns, $columns) = self::columnsDefinitionToSqlArray($tableDefinition['columns']);

        if (isset($tableDefinition['foreignKeys'])) {
            foreach ($tableDefinition['foreignKeys'] as $fkDefinition) {
                $columns[] = 'FOREIGN KEY (' . Db::quoteIdentifier($fkDefinition['childColumn']) . ')'
                    . ' REFERENCES ' . Db::quoteIdentifier($fkDefinition['parentTable'])
                    . '(' . Db::quoteIdentifier($fkDefinition['parentColumn']) . ')';
            }
        }

        if (isset($tableDefinition['indexes'])) {
            foreach ($tableDefinition['indexes'] as $indexName => $indexDefinition) {
                $columns[] = 'KEY'
                    . (is_numeric($indexName)
                        ? ' (' . Db::quoteIdentifier($indexDefinition) . ')'
                        : ' ' . self::indexDefinitionToSql($indexDefinition));
            }
        }

        if (isset($tableDefinition['uniqueKeys'])) {
            foreach ($tableDefinition['uniqueKeys'] as $uniqueKeyDefinition) {
                $columns[] = 'UNIQUE '
                    . self::indexDefinitionToSql($uniqueKeyDefinition);
            }
        }

        if (count($pkColumns)) {
            if (isset($tableDefinition['primaryKey'])) {
                throw new \RuntimeException("Only one PK can be present");
            }
            $columns[] = 'PRIMARY KEY ' . self::indexDefinitionToSql(['columns' => $pkColumns]);
        } elseif (isset($tableDefinition['primaryKey'])) {
            $columns[] = 'PRIMARY KEY ' . self::indexDefinitionToSql($tableDefinition['primaryKey']);
        }

        $sql = "CREATE TABLE " . Db::quoteIdentifier($tableName)
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

    public static function columnDefinitionToSql($columnName, array $columnDefinition): string {
        ArrayTool::assertHasOnlyKeys($columnDefinition, ['type', 'nullable', 'scale', 'precision', 'default', 'unsigned', 'length']);

        $columnDefinitionSql = '';
        $columnType = $columnDefinition['type'];

        // @TODO: Add 'foreignKey' type.

        if ($columnType === 'primaryKey') {
            $columnDefinitionSql .= 'int unsigned NOT NULL AUTO_INCREMENT';
            $pkColumns[] = $columnName;
        } elseif ($columnType === 'serial') {
            $columnDefinitionSql .= TypeInfoProvider::expandMacroType('serial');
        } else {
            $columnDefinitionSql .= $columnType;

            if (TypeInfoProvider::isIntegerType($columnType)) {
                $columnDefinitionSql .= isset($columnDefinition['unsigned']) ? ' unsigned' : '';
            } elseif (TypeInfoProvider::isFloatingPointType($columnType)) {
                // Precision is the total number of digits in a number.
                // Scale is the number of digits to the right of the decimal point in a number.
                // For the number -999.9999, precision == 7 and scale == 4.
                $columnDefinitionSql .= '(' . $columnDefinition['precision'] . ',' . $columnDefinition['scale'] . ')';
            } elseif (TypeInfoProvider::isOneOfTypes($columnType, ['char', 'varchar'])) {
                $columnDefinitionSql .= '(' . ($columnDefinition['length'] ?? 255) . ')';
            }

            if (!isset($columnDefinition['nullable'])) {
                // By default a column can't contain NULL.
                $columnDefinition['nullable'] = false;
            }
            if (false === $columnDefinition['nullable']) {
                $columnDefinitionSql .= ' NOT NULL';
            }
            if (isset($columnDefinition['default'])) {
                $columnDefinitionSql .= ' DEFAULT ' . $columnDefinition['default'];
            }
        }

        return Db::quoteIdentifier($columnName) . ' ' . $columnDefinitionSql;
    }

    public function getTableDefinition(string $tableName, string $dbName = null): array {
        // The code fragment from the Doctrine MySQL, @TODO: specify where
        $stmt = $this->db->query("SELECT COLUMN_NAME AS Field, COLUMN_TYPE AS Type, IS_NULLABLE AS `Null`, COLUMN_KEY AS `Key`, COLUMN_DEFAULT AS `Default`, EXTRA AS Extra, COLUMN_COMMENT AS Comment, CHARACTER_SET_NAME AS CharacterSet, COLLATION_NAME AS Collation FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = " . (null === $dbName ? 'DATABASE()' : "'$dbName'") . " AND TABLE_NAME = '" . $tableName . "'");
        if (!$stmt->rowCount()) {
            throw new \RuntimeException("The table '" . (null === $dbName ? $tableName : $dbName . '.' . $tableName) . "' does not exist");
        }
        return $stmt->fetchAll();
    }

    public function getCreateTableSql(string $tableName): string {
        return $this->db->fetchRows("SHOW CREATE TABLE " . Db::quoteIdentifier($tableName))[0]['Create Table'];
    }

    protected static function columnsDefinitionToSqlArray(array $columnsDefinition) {
        $sql = [];
        $pkColumns = [];
        foreach ($columnsDefinition as $columnName => $columnDefinition) {
            $sql[] = self::columnDefinitionToSql($columnName, $columnDefinition);
            if ($columnDefinition['type'] === 'primaryKey') {
                $pkColumns[] = $columnName;
            }
        }
        return [$pkColumns, $sql];
    }

    protected static function indexDefinitionToSql(array $indexDefinition) {
        $sql = [];
        if (isset($indexDefinition['name'])) {
            $sql[] = $indexDefinition['name'];
        }
        if (isset($indexDefinition['type'])) {
            $sql[] = $indexDefinition['type'];
        }
        $sql[] = '('
            . (is_array($indexDefinition['columns'])
                ? implode(', ', array_map(__NAMESPACE__ . '\\Db::quoteIdentifier', $indexDefinition['columns']))
                : Db::quoteIdentifier($indexDefinition['columns']))
            . ')';
        if (isset($indexDefinition['option'])) {
            $sql[] = $indexDefinition['option'];
        }
        return implode(' ', $sql);
    }
}