<?php
namespace Morpho\Db\Sql\Sqlite;

use Morpho\Base\NotImplementedException;
use Morpho\Db\Sql\Result;
use Morpho\Db\Sql\SchemaManager as BaseSchemaManager;

class SchemaManager extends BaseSchemaManager {
    /**
     * This function uses slightly changed getListTablesSQL() method from the doctrine/dbal package
     * (https://github.com/doctrine/dbal/blob/master/lib/Doctrine/DBAL/Platforms/SqlitePlatform.php)
     */
    public function tableNames(): Result {
        return $this->db->select("name
            FROM sqlite_master
            WHERE type = 'table' AND name != 'sqlite_sequence' AND name != 'geometry_columns' AND name != 'spatial_ref_sys'
            UNION ALL
            SELECT name
            FROM sqlite_temp_master
            WHERE type = 'table'
            ORDER BY name");
    }

    public function tableExists(string $tableName): bool {
        throw new NotImplementedException();
    }

    public function deleteTable(string $tableName)/*: void */ {
        throw new NotImplementedException();
    }

    public function tableDefinitionToSql(string $tableName, array $tableDefinition): array {
        throw new NotImplementedException();
    }

    public function deleteTableIfExists(string $tableName)/*: void */ {
        throw new NotImplementedException();
    }
}