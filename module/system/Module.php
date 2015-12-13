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
     * @Listen afterDispatch 100
     */
    public function afterDispatch(array $event) {
        /*
        $headers = $event[1]['request']->getResponse()->getHeaders();
        $headers->addHeaderLine('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT')
            ->addHeaderLine('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
            ->addHeaderLine('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->addHeaderLine('Cache-Control', 'post-check=0, pre-check=0')
            ->addHeaderLine('Pragma', 'no-cache');
        */
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
