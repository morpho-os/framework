<?php
namespace Morpho\Db\Sql;

class Tree {
    protected $db;

    const TABLE_NAME = 'tree';

    protected $tableName = self::TABLE_NAME;
    
    protected $dataColumns = [
        'table' => null,
        'column' => 'id'
    ];

    public function __construct(Db $db) {
        $this->db = $db;
    }

    public function addChildNode($parentNodeId, array $data = null): string {
        return $this->db->transaction(function ($db) use ($parentNodeId, $data) {
            $db->insertRow($this->dataColumns['table'], $data);
            $pk = $db->lastInsertId();
            // insert select level where childId = parentNodeId if $parentNodeId !== null.
            $db->insertRow($this->tableName, ['childId' => $pk, 'parentId' => $parentNodeId]);
            return $pk;
        });
    }

    public function childNodes($parentNodeId = null): array {
        $args = [];
        if ($parentNodeId === null) {
            $whereClause = $this->db->query()->whereClause('parentId IS NULL');
        } else {
            $whereClause = $this->db->query()->whereClause('parentId = ?');
            $args[] = $parentNodeId;
        }
        $columnPrefix = 'data';
        $rows = $this->db->select(
            't.childId, t.parentId, t.depth, ' . $this->addTableAliasAndColumnPrefix('d', $columnPrefix, $this->dataColumns['columns'])
            . ' FROM ' . $this->db->query()->identifier($this->tableName) . " AS t
            INNER JOIN {$this->dataColumns['table']} as d
                ON t.childId = d.{$this->dataColumns['columns'][0]}"
            . "\n$whereClause",
            $args
        )->rows();
        $nodes = [];
        $dataColumns = function (array $row) use ($columnPrefix) {
            $res = [];
            $prefixLength = strlen($columnPrefix);
            foreach ($row as $name => $value) {
                if (substr($name, 0, $prefixLength) === $columnPrefix) {
                    $res[substr($name, $prefixLength)] = $value;
                }
            }
            return $res;
        };
        foreach ($rows as $row) {
            $node = [
                'childId' => $row['childId'],
                'parentId' => $row['parentId'],
                'depth' => $row['depth'],
            ];
            $node['data'] = $dataColumns($row);
            $nodes[] = $node;
        }
        return $nodes;
    }

    private function addTableAliasAndColumnPrefix(string $tableAlias, string $columnPrefix, array $columns): string {
        $newColumns = [];
        foreach ($columns as $column) {
            $newColumns[] = $tableAlias . '.' . $column . ' AS ' . $columnPrefix . $column;
        }
        return implode(', ', $newColumns);
    }

    public function descendantNodes(): array {

    }
    
    public function descendantOrSelfNodes(): array {
        
    }
    
    public function precedingSiblingNodes(): array {
        
    }
    
    public function followingSiblingNodes(): array {
        
    }
    
    public function parentNode() {
        
    }

    public function ancestorNodes(): array {

    }

    public function ancestorOrSelfNodes(): array {

    }

    public function createDbTable()/*: void */ {
        $tableDefinition = [
            'columns' => [
                'childId' => [
                    'type' => 'int',
                    'unsigned' => true,
                ],
                'parentId' => [
                    'type' => 'int',
                    'unsigned' => true,
                    'nullable' => true,
                ],
                'depth' => [
                    'type' => 'int',
                ],
            ],
            'foreignKeys' => [
                [
                    'childColumn' => 'parentId',
                    'parentTable' => $this->tableName,
                    'parentColumn' => 'childId',
                ],
                [
                    'childColumn' => 'childId',
                    'parentTable' => $this->dataColumns['table'],
                    'parentColumn' => $this->dataColumns['columns'][0],
                ]
            ],
            'description' => 'Stores hierarchy as tree',
        ];
        $this->db->schemaManager()->createTable($this->tableName, $tableDefinition);
    }

    public function setDataColumns(string $table, array $columns)/*: void */ {
        $this->dataColumns = [
            'table' => $table,
            'columns' => $columns,
        ];
    }

    public function dataColumns(): array {
        return $this->dataColumns;
    }
}