<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Db\Sql\MySql;

use Morpho\Db\Sql\IQuery;
use Morpho\Db\Sql\MySql\DbClient;
use Morpho\Db\Sql\MySql\Schema;
use Morpho\Db\Sql\Query;
use Morpho\Db\Sql\Result;
use Morpho\Test\Unit\Db\Sql\DbClientTest as BaseDbClientTest;

class DbClientTest extends BaseDbClientTest {
    /**
     * @var \Morpho\Db\Sql\MySql\DbClient
     */
    private $db;

    private $schema;

    public function setUp() {
        parent::setUp();
        $this->db = $this->mkDbClient();
        $this->schema = new Schema($this->db);
        $this->schema->deleteAllTables();
    }

    public function testDbName() {
        $dbConfig = $this->dbConfig();
        $this->assertSame($dbConfig['db'], $this->db->dbName());
    }

    public function testConnect_UsesMySqlWhenIfNoArgsPassed() {
        $this->assertInstanceOf(DbClient::class, DbClient::connect());
    }

    public function testConnection() {
        $connection = $this->db->pdo();
        $this->assertInstanceOf(\PDO::class, $connection);
        $this->assertSame($connection, $this->db->pdo());
    }

    public function testLastInsertId_ForNonAutoincrementCol() {
        $this->db->eval(<<<SQL
CREATE TABLE foo (
    some varchar(255)
)
SQL
        );
        $this->db->insertRow('foo', ['some' => 'test']);
        $this->assertEquals('0', $this->db->lastInsertId());
        $this->assertEquals('0', $this->db->lastInsertId('some'));
    }

    public function testLastInsertId_ForAutoincrementCol() {
        $this->db->eval(<<<SQL
CREATE TABLE foo (
    some int PRIMARY KEY AUTO_INCREMENT
)
SQL
        );
        $this->db->insertRow('foo', ['some' => '']);
        $this->assertEquals('1', $this->db->lastInsertId());
        $this->assertEquals('1', $this->db->lastInsertId('some'));
    }

    public function testSelectField() {
        $this->createTestTableWithData();
        $this->assertEquals('some value', $this->db->select("foo FROM test")->field());
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
        $this->assertEquals(DbClient::MYSQL_DRIVER, $this->db->driverName());
    }

    public function testInsertRows_PreservesTypes() {
        $this->db->eval("CREATE TABLE cars (
            name varchar(20),
            color varchar(20),
            country varchar(20),
            type1 int,
            type2 enum('US', 'Japan')
        )");
        $rows = [
            ['name' => "Comaro", 'color' => 'red', 'country' => 'US', 'type1' => 1, 'type2' => 'US'],
            ['name' => 'Mazda RX4', 'color' => 'yellow', 'country' => 'JP', 'type1' => 2, 'type2' => 'Japan'],
        ];
        $this->db->insertRows('cars', $rows);
        $this->assertSame($rows, $this->db->select('* FROM cars ORDER BY name')->rows());
    }

    public function dataForMkQueryOperations() {
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
     * @dataProvider dataForMkQueryOperations
     */
    public function testMkQueryOperations($op) {
        $method = 'mk' . $op . 'Query';
        $query = $this->db->$method();
        $this->assertNotSame($query, $this->db->$method());
        $this->assertInstanceOf(Query::class, $query);
        $this->assertInstanceOf(IQuery::class, $query);
    }

    public function testQuery_ReturnsTheSameInstance() {
        $this->assertSame($this->db->query(), $this->db->query());
    }

    public function testSchema_ReturnsNotUniqueInstance() {
        $schema = $this->db->schema();
        $this->assertSame($schema, $this->db->schema());
        $this->assertInstanceOf(Schema::class, $schema);
    }

    public function testEval_ThrowsExceptionOnInvalidSql() {
        $this->expectException(\PDOException::class, 'SQLSTATE[42000]: Syntax error or access violation');
        $this->db->eval('invalid sql');
    }

    public function testEval_Result() {
        $res = $this->db->eval('SELECT 1');
        $this->assertInstanceOf(Result::class, $res);

        $checkRes = function ($res, $expectedCount) {
            $this->assertInstanceOf(\Countable::class, $res);
            $this->assertSame($expectedCount, \count($res));
        };

        $checkRes($res, 1);

        $this->createTestTable();
        $this->db->insertRow('test', ['foo' => 'first row']);
        $this->db->insertRow('test', ['foo' => 'second row']);
        $res = $this->db->eval('SELECT * FROM test');
        $checkRes($res, 2);
    }

    public function testEval_WhereClauseWithLike() {
        $this->markTestIncomplete();
    }

    public function testEval_HandlingArgsWithQuotes() {
        $this->markTestIncomplete();
    }

    public function testConnect_PdoInstanceArgument() {
        $dbConfig = $this->dbConfig();
        $dsn = 'mysql:dbname=;' . $dbConfig['host'];
        $pdo = new \PDO($dsn, $dbConfig['user'], $dbConfig['password']);
        $connection = \Morpho\Db\Sql\DbClient::connect($pdo);
        $this->assertInstanceOf(DbClient::class, $connection);
    }

    public function testUpdateRows_ArgsIsMap() {
        $this->createTestTable();
        $this->db->insertRow('test', ['foo' => 'bar']);
        $this->db->updateRows('test', ['foo' => '123'], ['foo' => 'bar']);
        $this->assertSame([['foo' => '123']], $this->db->select('foo FROM test')->rows());
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
        $this->db->eval(<<<SQL
CREATE TABLE test (
    id int PRIMARY KEY AUTO_INCREMENT,
    foo varchar(255)
)
SQL
        );
    }
}
