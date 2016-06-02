<?php
namespace MorphoTest\Db\Sql\MySql;

use Morpho\Db\Sql\MySql\SchemaManager;
use Morpho\Test\DbTestCase;
use Morpho\Db\Sql\Db;

class SchemaManagerTest extends DbTestCase {
    protected $schemaManager;

    public function setUp() {
        parent::setUp();
        $db = new Db($this->getDbConfig());
        $this->schemaManager = new SchemaManager($db);
        $this->schemaManager->deleteAllTables();
    }

    public function testGetTableDefinitionForNonExistingTable() {
        $this->setExpectedException('\RuntimeException', "The table 'foo' does not exist");
        $this->schemaManager->getTableDefinition('foo');
    }

    public function testCreateTablesWithFksOnOneColumn() {
        $this->schemaManager->createTables([
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
        $this->markTestIncomplete();
    }

    public function testRenameColumn() {
        $this->markTestIncomplete();
    }

    public function testRenameTable() {
        $this->markTestIncomplete();
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
        $this->schemaManager->createTable('file', $tableDefinition);

        $this->assertEquals(['file'], $this->schemaManager->tableNames());

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
            $this->schemaManager->getCreateTableSql('file')
        );
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
            $this->schemaManager->columnDefinitionToSql($columnName, $columnDefinition)
        );

        $columnDefinition = [
            'type' => 'tinyint(1)',
            'nullable' => true,
        ];
        $this->assertEquals(<<<OUT
`$columnName` tinyint(1)
OUT
            ,
            $this->schemaManager->columnDefinitionToSql($columnName, $columnDefinition)
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
        list($sql, $args) = $this->schemaManager->tableDefinitionToSql($tableName, $tableDefinition);
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

    private function assertCreateTableSql() {
        $actualTableNames = $this->schemaManager->tableNames();
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
            $this->schemaManager->getCreateTableSql('product')
        );
        $this->assertEquals(<<<OUT
CREATE TABLE `order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
OUT
            ,
            $this->schemaManager->getCreateTableSql('order')
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
            $this->schemaManager->getCreateTableSql('productOrder')
        );
    }
}