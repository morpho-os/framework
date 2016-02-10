<?php
namespace Morpho\Db\Sql\MySql;

use Morpho\Base\Assert;
use Morpho\Base\NotImplementedException;
use Morpho\Db\Sql\SchemaManager as BaseSchemaManager;

class SchemaManager extends BaseSchemaManager {
    public function listDatabases(): array {
        return $this->db->fetchColumn("SHOW DATABASES");
    }

    public function createDatabase(string $dbName) {
        $this->db->runQuery("CREATE DATABASE " . $this->db->quoteIdentifier($dbName) . " CHARACTER SET utf8 COLLATE utf8_general_ci");
    }

    public function deleteDatabase(string $dbName) {
        $this->db->runQuery("DROP DATABASE " . $this->db->quoteIdentifier($dbName));
    }

    public function listTables(): array {
        return $this->db->fetchColumn("SHOW TABLES");
    }

    public function deleteTable(string $tableName) {
        $this->db->transaction(function ($db) use ($tableName) {
            /*
            $isMySql = $this->connection->getDriver() instanceof MySqlDriver;
            if ($isMySql) {
            */
            $db->runQuery('SET FOREIGN_KEY_CHECKS=0;');
            $db->runQuery('DROP TABLE IF EXISTS ' . $this->db->quoteIdentifier($tableName));
            /*
            if ($isMySql) {
            }
            */
            $db->runQuery('SET FOREIGN_KEY_CHECKS=1;');
        });
    }

    public function renameTable(string $oldTableName, string $newTableName) {
        throw new NotImplementedException();
    }

    public function deleteTableIfExists(string $tableName) {
        $this->db->runQuery('DROP TABLE IF EXISTS ' . $this->db->quoteIdentifier($tableName));
    }

    public function renameColumn() {
        throw new NotImplementedException();
    }

    public function tableDefinitionToSql(string $tableName, array $tableDefinition): array {
        Assert::hasOnlyKeys($tableDefinition, ['columns', 'foreignKeys', 'indexes', 'primaryKey', 'description', 'uniqueKeys']);

        list($pkColumns, $columns) = $this->columnsDefinitionToSqlArray($tableDefinition['columns']);

        if (isset($tableDefinition['foreignKeys'])) {
            foreach ($tableDefinition['foreignKeys'] as $fkDefinition) {
                $columns[] = 'FOREIGN KEY (' . $this->db->quoteIdentifier($fkDefinition['childColumn']) . ')'
                    . ' REFERENCES ' . $this->db->quoteIdentifier($fkDefinition['parentTable'])
                    . '(' . $this->db->quoteIdentifier($fkDefinition['parentColumn']) . ')';
            }
        }

        if (isset($tableDefinition['indexes'])) {
            foreach ($tableDefinition['indexes'] as $indexName => $indexDefinition) {
                $columns[] = 'KEY'
                    . (is_numeric($indexName)
                        ? ' (' . $this->db->quoteIdentifier($indexDefinition) . ')'
                        : ' ' . $this->indexDefinitionToSql($indexDefinition));
            }
        }

        if (isset($tableDefinition['uniqueKeys'])) {
            foreach ($tableDefinition['uniqueKeys'] as $uniqueKeyDefinition) {
                $columns[] = 'UNIQUE '
                    . $this->indexDefinitionToSql($uniqueKeyDefinition);
            }
        }

        if (count($pkColumns)) {
            if (isset($tableDefinition['primaryKey'])) {
                throw new \RuntimeException("Only one PK can be present");
            }
            $columns[] = 'PRIMARY KEY ' . $this->indexDefinitionToSql(['columns' => $pkColumns]);
        } elseif (isset($tableDefinition['primaryKey'])) {
            if (isset($tableDefinition['primaryKey'][0])) { // 'primaryKey' => ['firstCol', 'secondCol'] or 'firstCol'
                $columns[] = 'PRIMARY KEY ' . $this->indexDefinitionToSql(['columns' => (array)$tableDefinition['primaryKey']]);
            } else {
                //throw new \RuntimeException("The 'primaryKey' has invalid format");
                $columns[] = 'PRIMARY KEY ' . $this->indexDefinitionToSql($tableDefinition['primaryKey']);
            }
        }

        $sql = "CREATE TABLE " . $this->db->quoteIdentifier($tableName)
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

    public function columnDefinitionToSql($columnName, array $columnDefinition): string {
        Assert::hasOnlyKeys($columnDefinition, ['type', 'nullable', 'scale', 'precision', 'default', 'unsigned', 'length']);

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

        return $this->db->quoteIdentifier($columnName) . ' ' . $columnDefinitionSql;
    }

    public function getTableDefinition(string $tableName, string $dbName = null): array {
        // The code fragment from the Doctrine MySQL, @TODO: specify where
        $stmt = $this->db->runQuery("SELECT COLUMN_NAME AS Field, COLUMN_TYPE AS Type, IS_NULLABLE AS `Null`, COLUMN_KEY AS `Key`, COLUMN_DEFAULT AS `Default`, EXTRA AS Extra, COLUMN_COMMENT AS Comment, CHARACTER_SET_NAME AS CharacterSet, COLLATION_NAME AS Collation FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = " . (null === $dbName ? 'DATABASE()' : "'$dbName'") . " AND TABLE_NAME = '" . $tableName . "'");
        if (!$stmt->rowCount()) {
            throw new \RuntimeException("The table '" . (null === $dbName ? $tableName : $dbName . '.' . $tableName) . "' does not exist");
        }
        return $stmt->fetchAll();
    }

    public function getCreateTableSql(string $tableName): string {
        return $this->db->fetchRows("SHOW CREATE TABLE " . $this->db->quoteIdentifier($tableName))[0]['Create Table'];
    }

    protected function columnsDefinitionToSqlArray(array $columnsDefinition) {
        $sql = [];
        $pkColumns = [];
        foreach ($columnsDefinition as $columnName => $columnDefinition) {
            $sql[] = $this->columnDefinitionToSql($columnName, $columnDefinition);
            if ($columnDefinition['type'] === 'primaryKey') {
                $pkColumns[] = $columnName;
            }
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
                ? implode(', ', array_map([$this->db, 'quoteIdentifier'], $indexDefinition['columns']))
                : $this->db->quoteIdentifier($indexDefinition['columns']))
            . ')';
        if (isset($indexDefinition['option'])) {
            $sql[] = $indexDefinition['option'];
        }
        return implode(' ', $sql);
    }
}