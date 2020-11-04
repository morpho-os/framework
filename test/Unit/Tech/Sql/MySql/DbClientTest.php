<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Tech\Sql\MySql;

use Countable;
use Morpho\Tech\Sql\IQuery;
use Morpho\Tech\Sql\MySql\DbClient;
use Morpho\Tech\Sql\DbClient as BaseDbClient;
use Morpho\Tech\Sql\MySql\Schema;
use Morpho\Tech\Sql\Query;
use Morpho\Tech\Sql\Result;
use Morpho\Test\Unit\Tech\Sql\DbClientTest as BaseDbClientTest;
use PDO;
use PDOException;
use function count;

class DbClientTest extends BaseDbClientTest {
    private BaseDbClient $db;

    public function setUp(): void {
        parent::setUp();
        $this->db = $this->mkDbClient();
        $schema = new Schema($this->db);
        $schema->deleteAllTables();
    }

    public function testCanSwitchDb() {
        // As there is no global state and db connection is
        $dbConf = $this->dbConf();
        $curDbName = $this->db->dbName();

        $this->assertSame($dbConf['db'], $curDbName);
        $newDbName = 'mysql';
        $this->assertNotSame($newDbName, $curDbName);

        $this->assertIsInt($this->db->useDb($newDbName));

        $this->assertSame($newDbName, $this->db->dbName());

        $this->assertIsInt($this->db->useDb($curDbName));

        $this->assertSame($curDbName, $this->db->dbName());
    }

    public function testConnect_UsesMySqlByDefault() {
        $dbConf = $this->dbConf();
        $this->assertInstanceOf(DbClient::class, DbClient::connect([
            'user' => $dbConf['user'],
            'password' => $dbConf['password'],
        ]));
    }

    public function testConnection() {
        $connection = $this->db->pdo();
        $this->assertInstanceOf(PDO::class, $connection);
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
CREATE TABLE test (
    foo int PRIMARY KEY AUTO_INCREMENT,
    bar varchar(255)
)
SQL
        );
        $this->db->insertRow('test', ['bar' => 'test']);
        $this->assertEquals('1', $this->db->lastInsertId());
        $this->assertEquals('1', $this->db->lastInsertId('foo'));
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
        $this->createCarsTable();
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
        $this->expectException(PDOException::class, 'SQLSTATE[42000]: Syntax error or access violation');
        $this->db->eval('invalid sql');
    }

    public function testEval_Result() {
        $res = $this->db->eval('SELECT 1');
        $this->assertInstanceOf(Result::class, $res);

        $checkRes = function ($res, $expectedCount) {
            $this->assertInstanceOf(Countable::class, $res);
            $this->assertSame($expectedCount, count($res));
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

    public function testEval_InsertQuery() {
        $this->createCarsTable();
        $row = ['name' => "Comaro", 'color' => 'red', 'country' => 'US', 'type1' => 1, 'type2' => 'US'];
        $allRows = fn () => $this->db->pdo()->query('SELECT * FROM cars')->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertSame([], $allRows());
        $result = $this->db->eval($this->db->mkInsertQuery()->table('cars')->row($row)->build());
        $this->assertInstanceOf(Result::class, $result);
        $this->assertSame(1, $result->rowCount());
        $this->assertSame([$row], $allRows());
    }

    public function testConnect_PdoInstanceArgument() {
        $dbConf = $this->dbConf();
        $dsn = 'mysql:dbname=;' . $dbConf['host'];
        $pdo = new PDO($dsn, $dbConf['user'], $dbConf['password']);
        $connection = BaseDbClient::connect($pdo);
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

    private function createCarsTable(): void {
        $this->db->eval("CREATE TABLE cars (
            name varchar(20),
            color varchar(20),
            country varchar(20),
            type1 int,
            type2 enum('US', 'Japan')
        )");
    }
}
