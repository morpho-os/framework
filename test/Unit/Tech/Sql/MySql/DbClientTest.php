<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Tech\Sql\MySql;

use Morpho\Tech\Sql\IDbClient;
use Morpho\Tech\Sql\IQuery;
use Morpho\Tech\Sql\ISchema;
use Morpho\Tech\Sql\Result;
use PDO;

class DbClientTest extends DbTestCase {
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

        $this->assertSame($this->db, $this->db->useDb($newDbName));

        $this->assertSame($newDbName, $this->db->dbName());

        $this->assertSame($this->db, $this->db->useDb($curDbName));

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

    public function testQueries() {
        foreach (['insert', 'select', 'update', 'delete', 'replace'] as $method) {
            $query = $this->db->$method();
            $this->assertInstanceOf(IQuery::class, $query);
            $this->assertNotSame($query, $this->db->$method());
        }
    }

    public function testQuoteIdentifers() {
        $this->assertSame('`foo`.`bar`', $this->db->quoteIdentifiers('foo.bar'));
        $this->assertSame('`foo`', $this->db->quoteIdentifiers('foo'));

        $this->assertSame(['`foo`.`bar`'], $this->db->quoteIdentifiers(['foo.bar']));
        $this->assertSame(['`foo`'], $this->db->quoteIdentifiers(['foo']));
    }

    public function testPositionalArgs() {
        $this->assertSame([], $this->db->positionalArgs([]));
        $this->assertSame(['?', '?'], $this->db->positionalArgs(['foo', 'bar']));
        $this->assertSame(['?', '?'], $this->db->positionalArgs(['foo' => 123, 'bar' => 456]));
    }

    public function testNameValArgs() {
        $this->assertSame([], $this->db->positionalArgs([]));
        $this->assertSame(['`foo` = ?', '`bar` = ?'], $this->db->nameValArgs(['foo' => 123, 'bar' => 456]));
    }

    public function testSchema() {
        $this->assertInstanceOf(ISchema::class, $this->db->schema());
    }

    protected function createFixtures($db): void {
        $this->createCarsTable(true);
    }
}
