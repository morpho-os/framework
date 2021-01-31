<?php
return [
    /*
    'file' => [
        'columns' => [
            'id' => [
                'type' => 'primaryKey',
            ],
            'path' => [
                'type' => 'varchar',
            ],
            'type' => [
                'type' => 'varchar',
                'length' => 10,
            ],
        ],
        'indexes' => [
            'path',
            'type',
        ],
    ],
    */
    'module' => [
        'columns' => [
            'id' => [
                'type' => 'primaryKey'
            ],
            'name' => [
                'type' => 'varchar',
            ],
            'status' => [
                'type' => 'int',
            ],
            'weight' => [
                'type' => 'int',
            ],
        ],
        'uniqueKeys' => ['name'],
    ],
    //'controller' =>
    'event' => [
        'columns' => [
            'name' => [
                'type' => 'varchar',
            ],
            'priority' => [
                'type' => 'integer',
            ],
            'method' => [
                'type' => 'varchar',
            ],
            'moduleId' => [
                'type' => 'integer',
                'unsigned' => true,
            ],
        ],
        'primaryKey' => [
            'columns' => [
                'name',
                'moduleId',
            ],
        ],
        'foreignKeys' => [
            [
                'childColumn' => 'moduleId',
                'parentTable' => 'module',
                'parentColumn' => 'id',
            ],
        ],
    ],
    'setting' => [
        'columns' => [
            'name' => [
                'type' => 'varchar',
            ],
            'value' => [
                'type' => 'text',
            ],
            'moduleId' => [
                'type' => 'int',
                'unsigned' => 'true',
            ],
        ],
        'primaryKey' => [
            'columns' => [
                'name',
                'moduleId',
            ],
        ],
        'foreignKeys' => [
            [
                'childColumn' => 'moduleId',
                'parentTable' => 'module',
                'parentColumn' => 'id',
            ]
        ],
    ],
];


