<?php
namespace User;

use Morpho\Core\Module as BaseModule;
use Morpho\Identity\UserManager;

class Module extends BaseModule {
    private $initialized = false;

    /**
     * @Listen beforeDispatch 100
     */
    public function beforeDispatch(array $event) {
        if (!$this->initialized) {
            $this->serviceManager->set('userManager', new UserManager($this->getRepo('User'), $this->serviceManager->get('session')));
            $this->initialized = true;
        }
    }

    public static function getTableDefinitions(): array {
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
            'role' => [
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
            'userRole' => [
                'columns' => [
                    'userId' => [
                        'type' => 'int',
                        'unsigned' => true,
                    ],
                    'roleId' => [
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
                        'childColumn' => 'roleId',
                        'parentTable' => 'role',
                        'parentColumn' => 'id',
                    ]
                ],
                'description' => 'Stores relations between users (the `user` table) and roles (the `role` table)',
            ],
            'userPermission' => [
                'columns' => [
                    'roleId' => [
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
                        'childColumn' => 'roleId',
                        'parentTable' => 'role',
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
    }
}
