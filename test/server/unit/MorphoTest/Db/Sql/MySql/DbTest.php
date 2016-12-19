<?php
namespace MorphoTest\Db\Sql\MySql;

use Morpho\Db\Sql\MySql\Query;
use Morpho\Db\Sql\MySql\SchemaManager;
use MorphoTest\Db\Sql\DbTest as BaseDbTest;

class DbTest extends BaseDbTest {
    public function testInsertRows() {
        $this->markTestIncomplete();
    }

    public function testQuery_ReturnsTheSameObject() {
        $query = $this->db->query();
        $this->assertNotSame($query, $this->db->query());
        $this->assertInstanceOf(Query::class, $query);
    }

    public function testSchemaManager_ReturnsNotUniqueInstance() {
        $schemaManager = $this->db->schemaManager();
        $this->assertSame($schemaManager, $this->db->schemaManager());
        $this->assertInstanceOf(SchemaManager::class, $schemaManager);
    }
}