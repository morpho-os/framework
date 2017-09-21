<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Core\ModuleFs as BaseModuleFs;
use Morpho\Di\IServiceManager;
use Morpho\Di\IServiceManagerAware;

class ModuleFs extends BaseModuleFs implements IServiceManagerAware {
    protected $serviceManager;

    public function cacheDirPath(): string {
        return $this->serviceManager->get('site')->cacheDirPath();
    }

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }
}