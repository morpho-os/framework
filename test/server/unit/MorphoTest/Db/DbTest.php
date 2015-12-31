<?php
namespace MorphoTest\Db;

use Morpho\Test\DbTestCase;
use Morpho\Db\Db;

class DbTest extends DbTestCase {
    protected $db;

    public function setUp() {
        $this->db = new Db($this->getDbConfig());
        $this->db->deleteAllTables();
    }

    public function testLastInsertId_ForNonAutoincrementCol() {
        $this->db->createTable('foo', [
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
        $this->db->createTable('foo', [
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

    public function testGetTableDefinitionForNonExistingTable() {
        $this->setExpectedException('\RuntimeException', "The table 'foo' does not exist");
        $this->db->getTableDefinition('foo');
    }

    public function testCreateTablesWithFksOnOneColumn() {
        $this->db->createTables([
            'product'      => [
                'columns'     => [
                    'id'          => [
                        'type' => 'primaryKey',
                    ],
                    'title'       => [
                        'type'   => 'varchar',
                        'length' => 100,
                    ],
                    'description' => [
                        'type' => 'text',
                    ],
                ],
                'description' => 'Stores products',
            ],
            'order'        => [
                'columns' => [
                    'id' => [
                        'type' => 'primaryKey',
                    ],
                ],
            ],
            'productOrder' => [
                'columns' => [
                    'productId' => [
                        'type'     => 'int',
                        'unsigned' => true,
                    ],
                    'orderId'   => [
                        'type'     => 'int',
                        'unsigned' => true,
                    ],
                ],
                'foreignKeys'     => [
                    [
                        'childColumn'  => 'productId',
                        // @TODO: add support of the product.id notation.
                        'parentTable'  => 'product',
                        'parentColumn' => 'id',
                    ],
                    [
                        'childColumn'  => 'orderId',
                        'parentTable'  => 'order',
                        'parentColumn' => 'id',
                    ],
                ],
            ],
        ]);

        $this->assertCreateTableSql();
    }

    public function testForeignKeyOnMultipleColumns() {
        $this->markTestIncomplete();
    }

    public function testGetCreateTableSqlFromDefinition() {

    }

    public function testRenameColumn() {
        $this->markTestIncomplete();
    }

    public function testRenameTable() {
        $this->markTestIncomplete();
    }

    private function assertCreateTableSql() {
        $actualTableNames = $this->db->listTables();
        sort($actualTableNames);
        $this->assertEquals(['order', 'product', 'productOrder'], $actualTableNames);

        $this->assertEquals(<<<OUT
CREATE TABLE `product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores products'
OUT
            ,
            $this->db->getCreateTableSql('product')
        );
        $this->assertEquals(<<<OUT
CREATE TABLE `order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
OUT
            ,
            $this->db->getCreateTableSql('order')
        );

        $this->assertEquals(<<<OUT
CREATE TABLE `productOrder` (
  `productId` int(10) unsigned NOT NULL,
  `orderId` int(10) unsigned NOT NULL,
  KEY `productId` (`productId`),
  KEY `orderId` (`orderId`),
  CONSTRAINT `productOrder_ibfk_1` FOREIGN KEY (`productId`) REFERENCES `product` (`id`),
  CONSTRAINT `productOrder_ibfk_2` FOREIGN KEY (`orderId`) REFERENCES `order` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
OUT
            ,
            $this->db->getCreateTableSql('productOrder')
        );
    }

    public function testCreateTablesWithIndexes() {
        $tableDefinition = [
            'columns' => [
                'id'   => [
                    'type' => 'primaryKey',
                ],
                'path' => [
                    'type' => 'varchar',
                ],
                'type' => [
                    'type'   => 'varchar',
                    'length' => 10,
                ],
            ],
            'indexes' => [
                'path',
                'type',
            ],
        ];
        $this->db->createTable('file', $tableDefinition);

        $this->assertEquals(['file'], $this->db->listTables());

        $this->assertEquals(<<<OUT
CREATE TABLE `file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(255) NOT NULL,
  `type` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `path` (`path`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
OUT
            ,
            $this->db->getCreateTableSql('file')
        );
    }

    public function testSelectCell() {
        $this->createTestTableWithData();
        $this->assertEquals('some value', $this->db->selectCell("foo FROM test"));
    }

    public function testColumnDefinitionToSql_Nullable() {
        $columnName = 'foo';

        $columnDefinition = [
            'type' => 'tinyint(1)',
            'nullable' => false,
        ];
        $this->assertEquals(<<<OUT
`$columnName` tinyint(1) NOT NULL
OUT
            ,
            $this->db->columnDefinitionToSql($columnName, $columnDefinition)
        );

        $columnDefinition = [
            'type' => 'tinyint(1)',
            'nullable' => true,
        ];
        $this->assertEquals(<<<OUT
`$columnName` tinyint(1)
OUT
            ,
            $this->db->columnDefinitionToSql($columnName, $columnDefinition)
        );
    }

    public function testTableDefinitionToSql_UniqueKeys() {
        $tableName = 'test';
        $tableDefinition = [
            'columns' => [
                'login' => [
                    'type' => 'varchar',
                ],
                'email' => [
                    'type'   => 'varchar',
                    'length' => 10,
                ],
            ],
            'uniqueKeys' => [
                [
                    'columns' => ['login', 'email']
                    /* @TODO:
                    'indexName' => 'myUniqueIdx',
                    'indexType' =>
                    'indexOption' =>
                    */
                ],
            ],
        ];
        list($sql, $args) = $this->db->tableDefinitionToSql($tableName, $tableDefinition);
        $this->assertEquals(<<<OUT
CREATE TABLE `$tableName` (
`login` varchar(255) NOT NULL,
`email` varchar(10) NOT NULL,
UNIQUE (`login`, `email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
OUT
            ,
            $sql
        );
        $this->assertEquals([], $args);
    }

    private function createTestTableWithData() {
        $this->db->createTable(
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
        $this->db->insertRow('test', ['foo' => 'some value']);
    }
    /*
            public function testAutoSetLastInsertId()
            {
                $this->assertBoolAccessor([$this->db, 'autoSetLastInsertId'], true);
            }

            public function testOperations()
            {
                $db = $this->db;
                $db->createTableForClass(__NAMESPACE__ . '\\MyTable');
                $sql = 'SELECT * FROM my_table';
                $this->assertEquals([], $db->selectRows($sql));
                $db->insertRow('my_table', ['title' => 'ok1']);
                $db->insertRow('my_table', ['title' => 'ok2']);
                $db->deleteRows('my_table', ['id' => 1]);
                $this->assertEquals(
                    [
                        ['id' => '2', 'title' => 'ok2'],
                    ],
                    $db->selectRows($sql)
                );
                $db->updateRows('my_table', ['title' => 'FooBar'], ['id' => 2]);
                $this->assertEquals(
                    [
                        ['id' => '2', 'title' => 'FooBar'],
                    ],
                    $db->selectRows($sql)
                );

                $db = new Db($this->getDbConfig());
                $this->assertEquals(
                    [
                        ['id' => '2', 'title' => 'FooBar'],
                    ],
                    $db->selectRows($sql)
                );
            }
    */
}