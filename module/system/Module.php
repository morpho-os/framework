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
}
