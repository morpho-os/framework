<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Db\Sql\MySql;

use Morpho\Base\Must;
use Morpho\Base\NotImplementedException;
use Morpho\Db\Sql\Schema as BaseSchema;

class Schema extends BaseSchema {
    const ENGINE    = 'InnoDB';
    const CHARSET   = 'utf8';
    const COLLATION = 'utf8_general_ci';
    
    public function databaseNames(): array {
        return $this->db->eval("SHOW DATABASES")->column();
    }

    /**
     * Note: the all arguments will not be escaped and therefore SQL-injection is possible. It is responsibility
     * of the caller to provide safe arguments.
     */
    public function createDatabase(string $dbName, string $charset = null, string $collation = null): void {
        $this->db->eval("CREATE DATABASE " . $this->db->query()->quoteIdentifier($dbName)
            . " CHARACTER SET " . ($charset ?: self::CHARSET)
            . " COLLATE " . ($collation ?: self::COLLATION)
        );
    }
    
    public function databaseExists(string $dbName): bool {
        return in_array($dbName, $this->databaseNames(), true);
    }

    public function renameDatabase(string $oldName, string $newName) {
        throw new NotImplementedException();
    }
    
    /**
     * Note: the all arguments will not be escaped and therefore SQL-injection is possible. It is responsibility
     * of the caller to provide safe arguments.
     */
    public function deleteDatabase(string $dbName): void {
        $this->db->eval("DROP DATABASE " . $this->db->query()->quoteIdentifier($dbName));
    }

    public function sizeOfDatabases() {
        throw new NotImplementedException();
    }

    /**
     * Returns size of the $dbName in bytes.
     * Note: the all arguments will not be escaped and therefore SQL-injection is possible. It is responsibility
     * of the caller to provide safe arguments.
     */
    public function sizeOfDatabase(string $dbName) {
        return $this->db->select(
            'SUM(DATA_LENGTH + INDEX_LENGTH)
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = ?',
            [$dbName]
        )->cell();
    }

    public function userExists(string $userName): bool {
        return $this->db->select('1 FROM mysql.user WHERE User = ?', [$userName])->bool();
    }

    public function tableNames(): iterable {
        return $this->db->eval("SHOW TABLES")->column();
    }
    
    public function tableExists(string $tableName): bool {
        // @TODO: Use `mysql` table?
        // or SHOW TABLES like `$tableName`.
        return in_array($tableName, $this->tableNames(), true);
    }

    public function deleteTable(string $tableName): void {
        $this->db->transaction(function ($db) use ($tableName) {
            /*
            $isMySql = $this->connection->getDriver() instanceof MySqlDriver;
            if ($isMySql) {
            */
            $db->eval('SET FOREIGN_KEY_CHECKS=0;');
            $db->eval('DROP TABLE IF EXISTS ' . $this->db->query()->quoteIdentifier($tableName));
            /*
            if ($isMySql) {
            }
            */
            $db->eval('SET FOREIGN_KEY_CHECKS=1;');
        });
    }

    public function renameTable(string $oldTableName, string $newTableName): void {
        throw new NotImplementedException();
    }

    /**
     * Note: the all arguments will not be escaped and therefore SQL-injection is possible. It is responsibility
     * of the caller to provide safe arguments.
     */
    public function deleteTableIfExists(string $tableName): void {
        $this->db->eval('DROP TABLE IF EXISTS ' . $this->db->query()->quoteIdentifier($tableName));
    }

    /**
     * Note: the all arguments will not be escaped and therefore SQL-injection is possible. It is responsibility
     * of the caller to provide safe arguments.
     */
    public function tableDefinition(string $tableName, string $dbName = null): array {
        // The code fragment from the Doctrine MySQL, @TODO: specify where
        $stmt = $this->db->eval(
            "SELECT
                COLUMN_NAME AS Field,
                COLUMN_TYPE AS Type,
                IS_NULLABLE AS `Null`,
                COLUMN_KEY AS `Key`,
                COLUMN_DEFAULT AS `Default`,
                EXTRA AS Extra,
                COLUMN_COMMENT AS Comment,
                CHARACTER_SET_NAME AS CharacterSet,
                COLLATION_NAME AS Collation
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = " . (null === $dbName ? 'DATABASE()' : "'$dbName'") . " AND TABLE_NAME = '" . $tableName . "'"
        );
        if (!$stmt->rowCount()) {
            throw new \RuntimeException("The table '" . (null === $dbName ? $tableName : $dbName . '.' . $tableName) . "' does not exist");
        }
        return $stmt->rows();
    }

    /**
     * Note: the all arguments will not be escaped and therefore SQL-injection is possible. It is responsibility
     * of the caller to provide safe arguments.
     */
    public function createTableSql(string $tableName): string {
        return $this->db->eval("SHOW CREATE TABLE " . $this->db->query()->quoteIdentifier($tableName))
            ->row()['Create Table'];
    }

    /**
     * Note: the all arguments will not be escaped and therefore SQL-injection is possible. It is responsibility
     * of the caller to provide safe arguments.
     */
    public function tableDefinitionToSql(string $tableName, array $tableDefinition): array {
        Must::haveOnlyKeys($tableDefinition, ['columns', 'foreignKeys', 'indexes', 'primaryKey', 'description', 'uniqueKeys']);

        list($pkColumns, $columns) = $this->columnsDefinitionToSqlArray($tableDefinition['columns']);
        
        $query = $this->db->query();

        if (isset($tableDefinition['foreignKeys'])) {
            foreach ($tableDefinition['foreignKeys'] as $fkDefinition) {
                $columns[] = 'FOREIGN KEY (' . $query->quoteIdentifier($fkDefinition['childColumn']) . ')'
                    . ' REFERENCES ' . $query->quoteIdentifier($fkDefinition['parentTable'])
                    . '(' . $query->quoteIdentifier($fkDefinition['parentColumn']) . ')';
            }
        }

        if (isset($tableDefinition['indexes'])) {
            // 'indexes' => 'indexedCol1',
            // or 'indexes' => ['indexedCol1', 'indexedCol2', ...]
            // or 'indexes' => [
            //     [
            //         'name' => ...
            //         'columns' => ...
            //         'type' => ...
            //         'option' => ...
            //     ],
            // ]
            foreach ((array)$tableDefinition['indexes'] as $indexName => $indexDefinition) {
                // @TODO: Merge common logic with 'uniqueKeys' and 'primaryKey'.
                $columns[] = 'KEY'
                    . (is_numeric($indexName)
                        ? ' (' . $query->quoteIdentifier($indexDefinition) . ')'
                        : ' ' . $this->indexDefinitionToSql($indexDefinition));
            }
        }

        if (isset($tableDefinition['uniqueKeys'])) {
            // 'foreignKeys' => 'colName1'
            // or 'foreignKeys' => ['colName1', 'colName2', ...]]
            // or 'foreignKeys' => [
            //     [
            //         'name' => ...
            //         'columns' => ...
            //         'type' => ...
            //         'option' => ...
            //     ],
            // ]
            foreach ((array)$tableDefinition['uniqueKeys'] as $uniqueKeyDefinition) {
                if (is_string($uniqueKeyDefinition)) {
                    $columns[] = 'UNIQUE ' . $this->indexDefinitionToSql(['columns' => $uniqueKeyDefinition]);
                } else {
                    $columns[] = 'UNIQUE ' . $this->indexDefinitionToSql($uniqueKeyDefinition);
                }
            }
        }

        if (count($pkColumns)) {
            if (isset($tableDefinition['primaryKey'])) {
                throw new \RuntimeException("Only one PK can be present");
            }
            $columns[] = 'PRIMARY KEY ' . $this->indexDefinitionToSql(['columns' => $pkColumns]);
        } elseif (isset($tableDefinition['primaryKey'])) {
            if (isset($tableDefinition['primaryKey'][0])) {
                // 'primaryKey' => 'colName',
                // or 'primaryKey' => ['pkCol1', 'pkCol2'],
                $columns[] = 'PRIMARY KEY ' . $this->indexDefinitionToSql(['columns' => (array)$tableDefinition['primaryKey']]);
            } else {
                /**
                 * 'primaryKey' => [
                 *     'name'   => ...
                 *     'columns => ...
                 *     'type'   => ...
                 *     'option' => ...
                 * ]
                 */
                $columns[] = 'PRIMARY KEY ' . $this->indexDefinitionToSql($tableDefinition['primaryKey']);
            }
        }

        $sql = "CREATE TABLE " . $query->quoteIdentifier($tableName)
            . " (\n"
            . implode(",\n", $columns)
            . "\n) " . $this->createTableOptions();

        $args = [];
        if (isset($tableDefinition['description'])) {
            $sql .= "\n, COMMENT=?";
            $args[] = $tableDefinition['description'];
        }

        return [$sql, $args];
    }

    public function createTableOptions(): string {
        //CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB
        return "ENGINE=" . self::ENGINE . " DEFAULT CHARSET=" . self::CHARSET;
    }

    public function viewNames(): array {
        throw new NotImplementedException();
        // SELECT TABLE_NAME FROM information_schema.VIEWS;
    }

    /**
     * Returns size of all tables in $dbName in bytes.
     * Note: the all arguments will not be escaped and therefore SQL-injection is possible. It is responsibility
     * of the caller to provide safe arguments.
     */
    public function sizeOfTables(string $dbName): array {
        return $this->db->select(
            'TABLE_NAME AS tableName,
            TABLE_TYPE AS tableType,
            DATA_LENGTH + INDEX_LENGTH as sizeInBytes 
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = ? ORDER BY sizeInBytes DESC',
            [$dbName]
        )->rows();
    }

    /**
     * Returns a size of the $tableName in bytes.
     * The $tableName can contain dot (.) to refer to any table, e.g.: 'mysql.user'.
     */
    public function sizeOfTable(string $tableName) {
        throw new NotImplementedException();
    }

    public function renameColumn()/*: void*/ {
        throw new NotImplementedException();
    }

    /**
     * Note: the all arguments will not be escaped and therefore SQL-injection is possible. It is responsibility
     * of the caller to provide safe arguments.
     */
    public function columnDefinitionToSql(string $columnName, array $columnDefinition): string {
        Must::haveOnlyKeys($columnDefinition, ['type', 'nullable', 'scale', 'precision', 'default', 'unsigned', 'length']);

        $columnDefinitionSql = '';
        $columnType = $columnDefinition['type'];

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

        return $this->db->query()->quoteIdentifier($columnName) . ' ' . $columnDefinitionSql;
    }

    /**
     * Returns the all available charsets, each result row will contain the default collation for the respective charset.
     */
    public function availableCharsetsWithDefaultCollation(array $charsets = null): array {
        $sql = 'SHOW CHARACTER SET';
        $where = '';
        if ($charsets) {
            $where .= ' WHERE CHARSET IN (' . GeneralQuery::positionalPlaceholdersString($charsets) . ')';
        }
        return $this->db->eval($sql . $where, $charsets)->rows();
    }

    /**
     * Returns list of available collations for the given charset.
     */
    public function availableCollationsForCharset(string $charset): array {
        return $this->db->eval('SHOW COLLATION WHERE CHARSET = ?', [$charset])->rows();
    }

    /**
     * Returns a map where key is variable name and value is its value. The list of the returned variables:
     *     - character_set_client
     *     - character_set_connection
     *     - character_set_database
     *     - character_set_filesystem
     *     - character_set_results
     *     - character_set_server
     *     - character_set_system
     *     - character_sets_dir
     *     - collation_connection
     *     - collation_database
     *     - collation_server
     * The following variables in the list depend from the current database:
     *     - character_set_database
     *     - collation_database
     */
    public function charsetAndCollationVars(): array {
        return array_merge(
            $this->charsetVars(),
            $this->collationVars()
        );
    }

    public function collationVars(): array {
        return $this->db->eval('SHOW VARIABLES LIKE "collation%"')->map();
    }

    public function charsetVars(): array {
        return $this->db->eval('SHOW VARIABLES LIKE "character_set%"')->map();
    }

    /**
     * Returns an array in format ['charset' => $charset, 'collation' => $collation].
     * @return array|false
     */
    public function charsetAndCollationOfDatabase(string $dbName): array {
        return $this->db->select(
            'DEFAULT_CHARACTER_SET_NAME AS charset,
            DEFAULT_COLLATION_NAME AS collation
            FROM information_schema.SCHEMATA
            WHERE SCHEMA_NAME = ?',
            [$dbName]
        )->row();
    }
    
    public function setCharsetAndCollationOfDatabase(string $dbName) {
        throw new NotImplementedException();
        // ALTER DATABASE $dbName CHARACTER SET $charset COLLATE $collation;
    }

    /**
     * The $tableName can contain dot (.) to refer to any table, e.g.: 'mysql.user'.
     */
    public function charsetAndCollationOfTables(string $dbName): array {
        return $this->db->select(
            'TABLE_SCHEMA AS dbName,
            TABLE_NAME AS tableName,
            TABLE_TYPE AS tableType,
            SUBSTRING(TABLE_COLLATION, 1, LOCATE("_", TABLE_COLLATION) - 1) AS charset,
            TABLE_COLLATION AS collation
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = ?',
            [$dbName]
        )->rows();
    }

    public function charsetAndCollationOfTable(string $tableName): array {
        throw new NotImplementedException();
    }

    public function setCharsetAndCollationOfTable(string $tableName): array {
        // ALTER TABLE $tableName CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
        // ALTER TABLE $tableName CHARACTER SET utf8, COLLATE utf8_general_ci;
        throw new NotImplementedException();
    }
    
    /**
     * The $tableName can contain dot (.) to refer to any table, e.g.: 'mysql.user'.
     */
    public function charsetAndCollationOfColumns(string $tableName): array {
        throw new NotImplementedException();
        // SHOW FULL COLUMNS FROM table_name;
/*
SELECT TABLE_SCHEMA,
  TABLE_NAME,
  CCSA.CHARACTER_SET_NAME AS DEFAULT_CHAR_SET,
  COLUMN_NAME,
  COLUMN_TYPE,
  C.CHARACTER_SET_NAME
FROM information_schema.TABLES AS T
  JOIN information_schema.COLUMNS AS C USING (TABLE_SCHEMA, TABLE_NAME)
  JOIN information_schema.COLLATION_CHARACTER_SET_APPLICABILITY AS CCSA
    ON (T.TABLE_COLLATION = CCSA.COLLATION_NAME)
WHERE TABLE_SCHEMA='$dbName'
      AND C.DATA_TYPE IN ('enum', 'varchar', 'char', 'text', 'mediumtext', 'longtext')
ORDER BY TABLE_SCHEMA,
  TABLE_NAME,
  COLUMN_NAME
;
 */
    }
    
    public function setCharsetAndCollationOfColumn() {
        //ALTER TABLE $tableName CHANGE COLUMN $columnName $columnName TEXT CHARACTER SET utf8 COLLATE utf8_general_ci;
        // SHOW FULL COLUMNS FROM $tableName;
        throw new NotImplementedException();
    }
    
    public function createUser($name, $password, $host) {
        throw new NotImplementedException();
 //       GRANT CREATE, DROP, LOCK TABLES, REFERENCES, ALTER, DELETE, INDEX, INSERT, SELECT, UPDATE, CREATE TEMPORARY TABLES, TRIGGER, CREATE VIEW, SHOW VIEW, ALTER ROUTINE, CREATE ROUTINE, EXECUTE ON $dbName.* to $userName@$hostName IDENTIFIED BY '$password';
//FLUSH PRIVILEGES;
    }

    public function deleteUser() {
        throw new NotImplementedException();
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

    protected function indexDefinitionToSql(array $indexDefinition): string {
        $sql = [];
        if (isset($indexDefinition['name'])) {
            $sql[] = $indexDefinition['name'];
        }
        if (isset($indexDefinition['type'])) {
            $sql[] = $indexDefinition['type'];
        }
        $query = $this->db->query();
        $sql[] = '('
            . (is_array($indexDefinition['columns'])
                ? implode(', ', array_map([$query, 'quoteIdentifier'], $indexDefinition['columns']))
                : $query->quoteIdentifier($indexDefinition['columns']))
            . ')';
        if (isset($indexDefinition['option'])) {
            $sql[] = $indexDefinition['option'];
        }
        return implode(' ', $sql);
    }
}