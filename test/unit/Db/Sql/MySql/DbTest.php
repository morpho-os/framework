<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Db\Sql\MySql;

use Morpho\Db\Sql\IQuery;
use Morpho\Db\Sql\MySql\Db;
use Morpho\Db\Sql\MySql\SchemaManager;
use Morpho\Db\Sql\Query;
use Morpho\Db\Sql\Result;
use MorphoTest\Unit\Db\Sql\DbTest as BaseDbTest;

class DbTest extends BaseDbTest {
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

    public function testDbName() {
        $dbConfig = $this->dbConfig();
        $this->assertSame($dbConfig['db'], $this->db->dbName());
    }

    public function testConnection() {
        $connection = $this->db->pdo();
        $this->assertInstanceOf(\PDO::class, $connection);
        $this->assertSame($connection, $this->db->pdo());
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

    public function dataForNewQueryOperations() {
        return [
            [
                'select'
            ],
            [
                'insert'
            ],
            [
                'update'
            ],
            [
                'delete'
            ],
            [
                'replace'
            ],
        ];
    }

    /**
     * @dataProvider dataForNewQueryOperations
     */
    public function testNewQueryOperations($op) {
        $method = 'new' . $op . 'Query';
        $query = $this->db->$method();
        $this->assertNotSame($query, $this->db->$method());
        $this->assertInstanceOf(Query::class, $query);
        $this->assertInstanceOf(IQuery::class, $query);
    }

    public function testQuery_ReturnsTheSameInstance() {
        $this->assertSame($this->db->query(), $this->db->query());
    }

    public function testSchemaManager_ReturnsNotUniqueInstance() {
        $schemaManager = $this->db->schemaManager();
        $this->assertSame($schemaManager, $this->db->schemaManager());
        $this->assertInstanceOf(SchemaManager::class, $schemaManager);
    }

    public function testEval_Result() {
        $res = $this->db->eval('SELECT 1');
        $this->assertInstanceOf(Result::class, $res);

        $checkRes = function ($res, $expectedCount) {
            $this->assertInstanceOf(\Countable::class, $res);
            $this->assertSame($expectedCount, count($res));
        };

        $checkRes($res, 1);

        $this->createTestTable();
        $this->db->insertRow('test', ['foo' => 'first row']);
        $this->db->insertRow('test', ['foo' => 'second row']);
        $res = $this->db->eval('SELECT * FROM test');
        $checkRes($res, 2);
    }

    public function testConnect_PdoInstanceArgument() {
        $dbConfig = $this->dbConfig();
        $dsn = 'mysql:dbname=;' . $dbConfig['host'];
        $pdo = new \PDO($dsn, $dbConfig['user'], $dbConfig['password']);
        $connection = \Morpho\Db\Sql\Db::connect($pdo);
        $this->assertInstanceOf(Db::class, $connection);
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