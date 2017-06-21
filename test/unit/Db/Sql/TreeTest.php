<?php declare(strict_types=1);
namespace MorphoTest\Unit\Db\Sql;

use Morpho\Db\Sql\Tree;
use Morpho\Test\DbTestCase;

class TreeTest extends DbTestCase {
    public function setUp() {
        $this->db = $this->db();
        $this->db->schemaManager()->deleteAllTables();
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
        $this->db->schemaManager()->createTable($dataTableName, $dataTableDefinition);
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