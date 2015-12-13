<?php
namespace User;

use Morpho\Core\Module as BaseModule;

class Module extends BaseModule {
/**
     * @Listen beforeDispatch 100

    public function beforeDispatch(array $event) {
        $userManager = $this->serviceManager->get('userManager');
        d($userManager->isGuestUser());
        /*
        $event[1]['request']->setUser(
            new User(['id' => $this->getUserId()])
        );
    }
*/
    public static function getTableDefinitions(): array {
        return [
            'user' => [
                'columns' => [
                    'id' => [
                        'type' => 'pk',
                    ],
                    'login' => [
                        'type' => 'varchar',
                    ],
                    'passwordHash' => [
                        'type' => 'varchar',
                    ],
                ],
            ],
            'permission' => [
                'columns' => [
                    'id' => [
                        'type' => 'pk',
                    ],
                    'name' => [
                        'type' => 'varchar',
                    ],
                ],
            ],
            'role' => [
                'columns' => [
                    'id' => [
                        'type' => 'pk',
                    ],
                    'name' => [
                        'type' => 'varchar',
                    ],
                ],
            ],
            'resource' => [
                'columns' => [
                    'id' => [
                        'type' => 'pk',
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
                'fks' => [
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
                'fks' => [
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
            ],
        ];
    }
}
