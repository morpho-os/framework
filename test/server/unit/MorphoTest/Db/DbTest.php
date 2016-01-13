<?php
namespace MorphoTest\Db;

use Morpho\Db\SchemaManager;
use Morpho\Test\DbTestCase;
use Morpho\Db\Db;

class DbTest extends DbTestCase {
    protected $db, $schemaManager;

    public function setUp() {
        $this->db = new Db($this->getDbConfig());
        $this->schemaManager = new SchemaManager($this->db);
        $this->schemaManager->deleteAllTables();
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

    public function testSqlReturnsUniqueInstance() {
        $sql1 = $this->db->sqlQuery();
        $this->assertInstanceOf('Morpho\Db\SqlQuery', $sql1);
        $this->assertNotSame($this->db->sqlQuery(), $sql1);
    }

    public function testSelectCell() {
        $this->createTestTableWithData();
        $this->assertEquals('some value', $this->db->selectCell("foo FROM test"));
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
            $this->db->selectRows('foo FROM test')
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