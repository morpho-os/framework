<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Db\Sql\MySql;

use Morpho\Base\ArrayTool;
use Morpho\Db\Sql\Db;
use Morpho\Db\Sql\MySql\Schema;
use Morpho\Test\DbTestCase;

class SchemaTest extends DbTestCase {
    /**
     * @var Schema
     */
    protected $schema;

    private $dbs = [];
    /**
     * @var Db
     */
    private $db;

    private const DB = 'test';

    public function setUp() {
        parent::setUp();
        $db = $this->newDbConnection();
        $this->schema = new Schema($db);
        $this->schema->deleteAllTables();
        $this->db = $db;
        $this->dbs = [];
    }

    public function tearDown() {
        parent::tearDown();
        foreach ($this->dbs as $dbName) {
            $this->db->eval("DROP DATABASE IF EXISTS " . $dbName);
        }
    }

    public function testDatabaseOperations() {
        $dbSuffix = md5(__FUNCTION__);
        $dbName = 't' . $dbSuffix;
        $this->assertFalse($this->schema->databaseExists($dbName));
        $this->callCreateDatabase($dbName, Schema::CHARSET, Schema::COLLATION);
        $this->assertTrue($this->schema->databaseExists($dbName));
    }

    public function testTableDefinitionForNonExistingTable() {
        $this->expectException('\RuntimeException', "The table 'foo' does not exist");
        $this->schema->tableDefinition('foo');
    }

    public function testCreateTablesWithFksOnOneColumn() {
        $this->schema->createTables([
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

    public function testCreateTableSqlFromDefinition() {
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
        $this->schema->createTable('file', $tableDefinition);

        $this->assertEquals(['file'], $this->schema->tableNames());

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
            $this->schema->createTableSql('file')
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
            $this->schema->columnDefinitionToSql($columnName, $columnDefinition)
        );

        $columnDefinition = [
            'type' => 'tinyint(1)',
            'nullable' => true,
        ];
        $this->assertEquals(<<<OUT
`$columnName` tinyint(1)
OUT
            ,
            $this->schema->columnDefinitionToSql($columnName, $columnDefinition)
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
        list($sql, $args) = $this->schema->tableDefinitionToSql($tableName, $tableDefinition);
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
        $actualTableNames = $this->schema->tableNames();
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
            $this->schema->createTableSql('product')
        );
        $this->assertEquals(<<<OUT
CREATE TABLE `order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
OUT
            ,
            $this->schema->createTableSql('order')
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
            $this->schema->createTableSql('productOrder')
        );
    }
    
    // ------------------------------------------------------------------------

    public function testSizeOfDatabases() {
        $this->markTestIncomplete();
    }

    public function testSizeOfDatabase() {
        $size = $this->schema->sizeOfDatabase('mysql');
        $this->assertGreaterThan(0, $size);
        $sum = 0;
        foreach ($this->db->eval("SELECT DATA_LENGTH, INDEX_LENGTH FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'mysql'") as $row) {
            $sum += $row['DATA_LENGTH'] + $row['INDEX_LENGTH'];
        }
        $this->assertEquals($sum, $size);
    }

    public function testSizeOfTables() {
        $size = $this->schema->sizeOfTables('mysql');
        $this->assertInternalType('array', $size);
        $this->assertNotEmpty($size);
        $expectedKeys = ['tableName', 'tableType', 'sizeInBytes'];
        foreach ($size as $row) {
            $this->assertArrayHasOnlyItemsWithKeys($expectedKeys, $row);
        }
    }

    public function testSizeOfTable() {
        $this->markTestIncomplete();
    }
    
    // ------------------------------------------------------------------------
    
    public function testAvailableCharsetsWithDefaultCollation() {
        $expected = [
            [
                'Charset' => 'latin1',
                'Description' => 'cp1252 West European',
                'Default collation' => 'latin1_swedish_ci',
                'Maxlen' => 1,
            ],
            [
                'Charset' => 'utf8',
                'Description' => 'UTF-8 Unicode',
                'Default collation' => 'utf8_general_ci',
                'Maxlen' => 3,
            ],
        ];
        $this->assertEquals(
            $expected,
            $this->schema->availableCharsetsWithDefaultCollation(['utf8', 'latin1'])
        );
        $rows = $this->schema->availableCharsetsWithDefaultCollation();
        $this->assertTrue(count($rows) > count($expected));
        $expectedKeys = array_keys($expected[0]);
        foreach ($rows as $row) {
            $this->assertArrayHasOnlyItemsWithKeys($expectedKeys, $row);
        }
    }

    public function testAvailableCollationsForCharset() {
        $charset = 'utf8';
        $rows = $this->schema->availableCollationsForCharset($charset);
        $this->assertNotEmpty($rows);
        $expectedKeys = [
            'Collation',
            'Charset',
            'Id',
            'Default',
            'Compiled',
            'Sortlen',
        ];
        foreach ($rows as $row) {
            $this->assertArrayHasOnlyItemsWithKeys($expectedKeys, $row);
            $this->assertEquals($charset, $row['Charset']);
            $this->assertStringStartsWith($charset, $row['Collation']);
        }
    }
    
    public function testCharsetAndCollationVars() {
        $vars = $this->schema->charsetAndCollationVars();
        $expectedKeys = [
            'character_set_client',
            'character_set_connection',
            'character_set_database',
            'character_set_filesystem',
            'character_set_results',
            'character_set_server',
            'character_set_system',
            'character_sets_dir',
            'collation_connection',
            'collation_database',
            'collation_server',
        ];
        $this->assertArrayHasOnlyItemsWithKeys($expectedKeys, $vars);
    }

    public function testCharsetAndCollationOfDatabase() {
        $charset = 'gb2312';
        $collation = $charset . '_bin';
        $dbName = $this->callCreateDatabase('t' . md5(__FUNCTION__), $charset, $collation);
        $this->assertEquals(['charset' => $charset, 'collation' => $collation], $this->schema->charsetAndCollationOfDatabase($dbName));
    }
    
    public function testCharsetAndCollationOfTables() {
        $this->db->eval("CREATE TABLE cherry (id int) CHARACTER SET gb2312 COLLATE gb2312_bin");
        $this->db->eval("CREATE TABLE kiwi (id int) CHARACTER SET cp1250 COLLATE cp1250_croatian_ci");
        $rows = $this->schema->charsetAndCollationOfTables(self::DB);
        $this->assertNotEmpty($rows);
        foreach ($rows as $row) {
            $this->assertCount(5, $row);
            $this->assertEquals(self::DB, $row['dbName']);
            $this->assertEquals('BASE TABLE', $row['tableType']);
            switch ($row['tableName']) {
                case'cherry':
                    $this->assertEquals('gb2312', $row['charset']);
                    $this->assertEquals('gb2312_bin', $row['collation']);
                    break;
                case 'kiwi';
                    $this->assertEquals('cp1250', $row['charset']);
                    $this->assertEquals('cp1250_croatian_ci', $row['collation']);
                    break;
                default:
                    $this->fail();
            }
        }
    }
    
    public function testCharsetAndCollationOfColumns() {
        $this->markTestIncomplete();
    }

    public function testCreateTableOptions() {
        $this->assertSame('ENGINE=InnoDB DEFAULT CHARSET=utf8', $this->schema->createTableOptions());
    }

    private function assertArrayHasOnlyItemsWithKeys(array $expectedKeys, array $arr) {
        $this->assertTrue(
            ArrayTool::setsEqual($expectedKeys, array_keys($arr)),
            print_r($expectedKeys, true) . print_r(array_keys($arr), true)
        );
    }

    private function callCreateDatabase($dbName, $charset, $collation): string {
        $this->dbs[] = $dbName;
        $this->db->eval("CREATE DATABASE $dbName CHARACTER SET $charset COLLATE $collation");
        return $dbName;
    }
}