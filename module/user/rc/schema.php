<?php
//declare(strict_types=1);
return [
    'user' => [
        'columns' => [
            'id' => [
                'type' => 'primaryKey',
            ],
            'login' => [
                'type' => 'varchar',
            ],
            'passwordHash' => [
                'type' => 'varchar',
                'nullable' => true,
            ],
        ],
        'uniqueKeys' => ['login'],
    ],
    'permission' => [
        'columns' => [
            'id' => [
                'type' => 'primaryKey',
            ],
            'name' => [
                'type' => 'varchar',
            ],
        ],
    ],
    'group' => [
        'columns' => [
            'id' => [
                'type' => 'primaryKey',
            ],
            'name' => [
                'type' => 'varchar',
            ],
        ],
    ],
    'resource' => [
        'columns' => [
            'id' => [
                'type' => 'primaryKey',
            ],
            'name' => [
                'type' => 'varchar',
            ],
        ],
    ],
    'userGroup' => [
        'columns' => [
            'userId' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'groupId' => [
                'type' => 'int',
                'unsigned' => true,
            ],
        ],
        'foreignKeys' => [
            [
                'childColumn' => 'userId',
                'parentTable' => 'user',
                'parentColumn' => 'id',
            ],
            [
                'childColumn' => 'groupId',
                'parentTable' => 'group',
                'parentColumn' => 'id',
            ]
        ],
        'description' => 'Stores relations between users (the `user` table) and groups (the `group` table)',
    ],
    'userPermission' => [
        'columns' => [
            'groupId' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'permissionId' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'resourceId' => [
                'type' => 'int',
                'unsigned' => true,
            ],
        ],
        'foreignKeys' => [
            [
                'childColumn' => 'groupId',
                'parentTable' => 'group',
                'parentColumn' => 'id',
            ],
            [
                'childColumn' => 'permissionId',
                'parentTable' => 'permission',
                'parentColumn' => 'id',
            ],
            [
                'childColumn' => 'resourceId',
                'parentTable' => 'resource',
                'parentColumn' => 'id',
            ]
        ],
        'description' => 'Stores relations between users (the `user` table) and permissions (the `permission` table)',
    ],
];
