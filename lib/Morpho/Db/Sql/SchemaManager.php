<?php
namespace Morpho\Db\Sql;

abstract class SchemaManager {
    protected $db;

    public function __construct(Db $db) {
        $this->db = $db;
    }

    public function createTables(array $tableDefinitions) {
        foreach ($tableDefinitions as $tableName => $tableDefinition) {
            $this->createTable($tableName, $tableDefinition);
        }
    }

    public function createTable(string $tableName, array $tableDefinition) {
        list($sql, $args) = $this->tableDefinitionToSql($tableName, $tableDefinition);
        $this->db->runQuery($sql, $args);
    }

    public function recreateTable(string $tableName, array $tableDefinition) {
        $this->deleteTableIfExists($tableName);
        $this->createTable($tableName, $tableDefinition);
    }

    public function deleteTables(array $tableNames) {
        foreach ($tableNames as $tableName) {
            $this->deleteTable($tableName);
        }
    }

    public function deleteAllTables() {
        $this->deleteTables($this->listTables());
    }

    abstract public function deleteTable(string $tableName);

    abstract public function tableDefinitionToSql(string $tableName, array $tableDefinition): array;

    abstract public function deleteTableIfExists(string $tableName);

    abstract public function listTables(): array;
}