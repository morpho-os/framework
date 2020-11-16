<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */

namespace Morpho\Test\Unit\Tech\Sql\MySql;

use Morpho\Tech\Sql\DeleteQuery;
use Morpho\Tech\Sql\IDbClient;
use Morpho\Tech\Sql\InsertQuery;
use Morpho\Tech\Sql\ReplaceQuery;
use Morpho\Tech\Sql\Result;
use Morpho\Tech\Sql\SelectQuery;
use Morpho\Tech\Sql\UpdateQuery;
use Morpho\Testing\DbTestCase;
use PDO;
use function Morpho\Tech\Sql\mkDbClient;

class DbClientTest extends DbTestCase {
    private $db;
    private PDO $pdo;

    public function setUp(): void {
        parent::setUp();
        $this->pdo = $this->mkPdo();
        $this->db = mkDbClient($this->pdo);
        foreach ($this->pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $tableName) {
            $this->pdo->exec('DROP TABLE ' . $tableName);
        }
        $this->createTestTables();
    }

    public function testInterface() {
        $this->assertInstanceOf(IDbClient::class, $this->db);
    }

    public function testPdo() {
        $this->assertInstanceOf(PDO::class, $this->db->pdo());
    }

    public function testEval_NamedPlaceholders() {
        $result = $this->db->eval('SELECT * FROM cars WHERE color = :color', ['color' => 'red']);
        $this->assertInstanceOf(Result::class, $result);
        $rows = $result->rows();
        $this->assertSame([['name' => "Comaro", 'color' => 'red', 'country' => 'US', 'type1' => 1, 'type2' => 'US']], $rows);
    }

    public function testEval_PositionalPlaceholders() {
        $result = $this->db->eval('SELECT * FROM cars WHERE color = ?', ['red']);
        $this->assertInstanceOf(Result::class, $result);
        $rows = $result->rows();
        $this->assertSame([['name' => "Comaro", 'color' => 'red', 'country' => 'US', 'type1' => 1, 'type2' => 'US']], $rows);
    }

    public function testExec() {
        $this->assertSame(3, $this->db->exec('DELETE FROM cars'));
    }

    public function testCanSwitchDb() {
        $dbConf = $this->dbConf();
        $curDbName = $this->db->dbName();

        $this->assertSame($dbConf['db'], $curDbName);
        $newDbName = 'mysql';
        $this->assertNotSame($newDbName, $curDbName);

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($this->db->useDb($newDbName));

        $this->assertSame($newDbName, $this->db->dbName());

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($this->db->useDb($curDbName));

        $this->assertSame($curDbName, $this->db->dbName());
    }

    public function testLastInsertId_NonAutoincrementCol() {
        $this->db->eval(<<<SQL
CREATE TABLE foo (
    bar varchar(255)
)
SQL
        );
        $this->db->eval('INSERT INTO foo VALUES (?)', ['test']);

        $this->assertEquals('0', $this->db->lastInsertId());
        $this->assertEquals('0', $this->db->lastInsertId('bar'));
    }

    public function testLastInsertId_AutoincrementCol() {
        $this->db->eval(<<<SQL
CREATE TABLE foo (
    baz int PRIMARY KEY AUTO_INCREMENT,
    bar varchar(255)
)
SQL
        );
        $this->db->eval('INSERT INTO foo (bar) VALUES (?)', ['test']);
        $this->assertEquals('1', $this->db->lastInsertId());
        $this->assertEquals('1', $this->db->lastInsertId('baz'));
    }

    public function testDriverName() {
        $driverName = $this->db->driverName();
        $this->assertNotEmpty($driverName);
        $this->assertEquals($this->dbConf()['driver'], $driverName);
    }

    public function testInsertQuery() {
        $query = $this->db->insert();
        $this->assertInstanceOf(InsertQuery::class, $query);
        $this->assertNotSame($query, $this->db->insert());
    }

    public function testSelectQuery() {
        $query = $this->db->select();
        $this->assertInstanceOf(SelectQuery::class, $query);
        $this->assertNotSame($query, $this->db->select());
    }

    public function testUpdateQuery() {
        $query = $this->db->update();
        $this->assertInstanceOf(UpdateQuery::class, $query);
        $this->assertNotSame($query, $this->db->update());
    }

    public function testDeleteQuery() {
        $query = $this->db->delete();
        $this->assertInstanceOf(DeleteQuery::class, $query);
        $this->assertNotSame($query, $this->db->delete());
    }

    public function testReplaceQuery() {
        $query = $this->db->replace();
        $this->assertInstanceOf(ReplaceQuery::class, $query);
        $this->assertNotSame($query, $this->db->replace());
    }

    private function createTestTables() {
        $this->pdo->query('DROP TABLE IF EXISTS cars');
        $this->pdo->query("CREATE TABLE cars (
            name varchar(20),
            color varchar(20),
            country varchar(20),
            type1 int,
            type2 enum('US', 'Japan', 'EU')
        )");
        $rows = [
            ['name' => "Comaro", 'color' => 'red', 'country' => 'US', 'type1' => 1, 'type2' => 'US'],
            ['name' => 'Mazda 6', 'color' => 'green', 'country' => 'JP', 'type1' => 2, 'type2' => 'Japan'],
            ['name' => 'Mazda CX-3', 'color' => 'green', 'country' => 'JP', 'type1' => 2, 'type2' => 'EU'],
        ];
        foreach ($rows as $row) {
            $sql = 'INSERT INTO cars (name, color, country, type1, type2) VALUES (:name, :color, :country, :type1, :type2)';
            $this->pdo->prepare($sql)->execute($row);
        }
    }
}
