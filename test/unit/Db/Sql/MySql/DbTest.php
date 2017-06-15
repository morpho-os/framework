<?php declare(strict_types=1);
namespace MorphoTest\Db\Sql\MySql;

use Morpho\Db\Sql\MySql\Query;
use Morpho\Db\Sql\MySql\SchemaManager;
use MorphoTest\Db\Sql\DbTest as BaseDbTest;

class DbTest extends BaseDbTest {
    public function testInsertRows() {
        $this->db->eval('CREATE TABLE cars (name varchar(20), color varchar(20), country varchar(20))');
        $rows = [
            ['name' => "Comaro", 'color' => 'red', 'country' => 'US'],
            ['name' => 'Mazda RX4', 'color' => 'yellow', 'country' => 'JP'],
        ];
        $this->db->insertRows('cars', $rows);
        $this->assertEquals($rows, $this->db->select('* FROM cars')->rows());
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