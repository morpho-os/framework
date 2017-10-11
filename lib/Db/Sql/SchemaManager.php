<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Db\Sql;

abstract class SchemaManager {
    protected $db;

    public function __construct(Db $db) {
        $this->db = $db;
    }

    abstract public function createDatabase(string $dbName): void;

    public function createTables(iterable $tableDefinitions): void {
        foreach ($tableDefinitions as $tableName => $tableDefinition) {
            $this->createTable($tableName, $tableDefinition);
        }
    }

    public function createTable(string $tableName, array $tableDefinition): void {
        list($sql, $args) = $this->tableDefinitionToSql($tableName, $tableDefinition);
        $this->db->eval($sql, $args);
    }

    public function recreateTable(string $tableName, array $tableDefinition): void {
        $this->deleteTableIfExists($tableName);
        $this->createTable($tableName, $tableDefinition);
    }

    public function deleteTables(iterable $tableNames): void {
        foreach ($tableNames as $tableName) {
            $this->deleteTable($tableName);
        }
    }

    public function deleteAllTables(): void {
        $this->deleteTables($this->tableNames());
    }
    
    abstract public function tableExists(string $tableName): bool;

    abstract public function deleteTable(string $tableName): void;

    abstract public function createTableSql(string $tableName): string;

    abstract public function tableDefinitionToSql(string $tableName, array $tableDefinition): array;

    abstract public function deleteTableIfExists(string $tableName): void;

    abstract public function tableNames(): iterable;
}