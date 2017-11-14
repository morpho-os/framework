<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Db\Sql\MySql;

use Morpho\Db\Sql\MySql\Tree;
use Morpho\Test\DbTestCase;

class TreeTest extends DbTestCase {
    private $db;
    private $tree;

    public function setUp() {
        $this->db = $this->newDbConnection();
        $this->db->schema()->deleteAllTables();
        $this->tree = new Tree($this->db);
        $dataTableName = 'tree_data_fs';
        $this->tree->setDataColumns($dataTableName, ['id', 'filePath']);
        $dataTableDefinition = [
            'columns' => [
                'id' => [
                    'type' => 'primaryKey',
                ],
                'filePath' => [
                    'type' => 'varchar',
                    'length' => '255',
                ],
            ],
        ];
        $this->db->schema()->createTable($dataTableName, $dataTableDefinition);
        $this->tree->createDbTable();
    }

    public function testChildNodes() {
        $this->assertEquals([], $this->tree->childNodes());
        $data = ['filePath' => '/etc/nginx/conf.d/default.conf'];
        $nodeId = $this->tree->addChildNode(null, $data);
        $this->assertIntString($nodeId);
        $this->assertEquals(
            [
                [
                    'childId' => $nodeId,
                    'parentId' => null,
                    'depth' => '0',
                    'data' => array_merge($data, ['id' => $nodeId]),
                ],
            ],
            $this->tree->childNodes()
        );
    }
}