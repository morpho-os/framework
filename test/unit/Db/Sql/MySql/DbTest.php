<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Db\Sql\MySql;

use Morpho\Db\Sql\MySql\Db;
use Morpho\Db\Sql\MySql\Query;
use Morpho\Db\Sql\MySql\SchemaManager;
use Morpho\Test\DbTestCase;

class DbTest extends DbTestCase {
    /**
     * @var \Morpho\Db\Sql\MySql\Db
     */
    private $db;

    private $schemaManager;

    public function setUp() {
        $this->db = $this->newDbConnection();
        $this->schemaManager = new SchemaManager($this->db);
        $this->schemaManager->deleteAllTables();
    }

    public function testConnection() {
        $connection = $this->db->connection();
        $this->assertInstanceOf(\PDO::class, $connection);
        $this->assertSame($connection, $this->db->connection());
    }

    public function testLastInsertId_ForNonAutoincrementCol() {
        $this->schemaManager->createTable('foo', [
            'columns' => [
                'some' => [
                    'type' => 'varchar',
                ],
            ],
        ]);
        $this->db->insertRow('foo', ['some' => 'test']);
        $this->assertEquals('0', $this->db->lastInsertId());
        $this->assertEquals('0', $this->db->lastInsertId('some'));
    }

    public function testLastInsertId_ForAutoincrementCol() {
        $this->schemaManager->createTable('foo', [
            'columns' => [
                'some' => [
                    'type' => 'primaryKey',
                ],
            ],
        ]);
        $this->db->insertRow('foo', ['some' => '']);
        $this->assertEquals('1', $this->db->lastInsertId());
        $this->assertEquals('1', $this->db->lastInsertId('some'));
    }

    public function testSelectCell() {
        $this->createTestTableWithData();
        $this->assertEquals('some value', $this->db->select("foo FROM test")->cell());
    }

    public function testUpdateRows_WhereConditionArray() {
        $this->setTestDataForUpdateRows();
        $this->db->updateRows('test', ['foo' => 'second row changed'], ['foo' => 'second row']);
        $this->assertSecondRowChanged();
    }

    public function testUpdateRows_WhereConditionString_NoWhereConditionArgs() {
        $this->setTestDataForUpdateRows();
        $this->db->updateRows('test', ['foo' => 'second row changed'], "foo = 'second row'");
        $this->assertSecondRowChanged();
    }

    public function testUpdateRows_WhereConditionString_WithWhereConditionArgs() {
        $this->setTestDataForUpdateRows();
        $this->db->updateRows('test', ['foo' => 'second row changed'], "foo = ?", ['second row']);
        $this->assertSecondRowChanged();
    }

    public function testDriverName() {
        $this->assertEquals(Db::MYSQL_DRIVER, $this->db->driverName());
    }

    public function testInsertRows() {
        $this->db->eval('CREATE TABLE cars (name varchar(20), color varchar(20), country varchar(20))');
        $rows = [
            ['name' => "Comaro", 'color' => 'red', 'country' => 'US'],
            ['name' => 'Mazda RX4', 'color' => 'yellow', 'country' => 'JP'],
        ];
        $this->db->insertRows('cars', $rows);
        $this->assertEquals($rows, $this->db->select('* FROM cars')->rows());
    }

    public function testNewQuery_ReturnsTheSameObject() {
        $query = $this->db->newQuery();
        $this->assertNotSame($query, $this->db->newQuery());
        $this->assertInstanceOf(Query::class, $query);
    }

    public function testSchemaManager_ReturnsNotUniqueInstance() {
        $schemaManager = $this->db->schemaManager();
        $this->assertSame($schemaManager, $this->db->schemaManager());
        $this->assertInstanceOf(SchemaManager::class, $schemaManager);
    }

    private function setTestDataForUpdateRows() {
        $this->createTestTable();
        $this->db->insertRow('test', ['foo' => 'first row']);
        $this->db->insertRow('test', ['foo' => 'second row']);
        $this->db->insertRow('test', ['foo' => 'third row']);
    }

    private function assertSecondRowChanged() {
        $this->assertEquals(
            [
                ['foo' => 'first row'],
                ['foo' => 'second row changed'],
                ['foo' => 'third row'],
            ],
            $this->db->select('foo FROM test')->rows()
        );
    }

    private function createTestTableWithData() {
        $this->createTestTable();
        $this->db->insertRow('test', ['foo' => 'some value']);
    }

    private function createTestTable() {
        $this->schemaManager->createTable(
            'test',
            [
                'columns' => [
                    'id'  => [
                        'type' => 'primaryKey',
                    ],
                    'foo' => [
                        'type' => 'varchar',
                    ],
                ],
            ]
        );
    }
}