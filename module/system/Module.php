<?php
namespace System;

use Morpho\Core\Module as BaseModule;
use Morpho\Db\Db;
use Morpho\Web\AccessDeniedException;
use Morpho\Web\NotFoundException;

class Module extends BaseModule {
    const NAME = 'system';

    public function install(Db $db) {

    }

    /**
     * @Listen dispatchError 100
     */
    public function dispatchError(array $event) {
        $exception = $event[1]['exception'];
        if ($exception instanceof AccessDeniedException) {
            $mca = $this->serviceManager->get('settingManager')
                ->get('accessDeniedMCA', 'system');
            if (false !== $mca) {
                $request = $event[1]['request'];
                $request->setModuleName($mca['module'])
                    ->setControllerName($mca['controller'])
                    ->setActionName($mca['action'])
                    ->isDispatched(false);
                $request->getResponse()
                    ->setStatusCode(403);
            }
        } elseif ($exception instanceof NotFoundException) {
            $mca = $this->serviceManager->get('settingManager')
                ->get('notFoundMCA', 'system');
            if (false !== $mca) {
                $request = $event[1]['request'];
                $request->setModuleName($mca['module'])
                    ->setControllerName($mca['controller'])
                    ->setActionName($mca['action'])
                    ->isDispatched(false);
                $request->getResponse()
                    ->setStatusCode(404);
            }
        } else {
            // @TODO: Handle other errors.
            throw $exception;
        }
    }

    public static function getTableDefinitions(): array {
        return [
            /*
            'file' => [
                'columns' => [
                    'id' => [
                        'type' => 'pk',
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
                        'type' => 'pk'
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
                'indexes' => [
                    'name',
                ],
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
                'fks' => [
                    [
                        'childColumn' => 'moduleId',
                        'parentTable' => 'module',
                        'parentColumn' => 'id',
                    ],
                ],
            ],
            'setting' => [
                'columns' => [
                    'id' => [
                        'type' => 'pk',
                    ],
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
                'fks' => [
                    [
                        'childColumn' => 'moduleId',
                        'parentTable' => 'module',
                        'parentColumn' => 'id',
                    ]
                ],
            ],
        ];
    }
}
