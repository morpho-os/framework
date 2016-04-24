<?php
namespace Morpho\Web;

use Morpho\Core\ModuleFs as BaseModuleFs;
use Morpho\Di\IServiceManager;
use Morpho\Di\IServiceManagerAware;

class ModuleFs extends BaseModuleFs implements IServiceManagerAware {
    protected $serviceManager;

    public function getBaseCacheDirPath(): string {
        return $this->serviceManager->get('siteManager')->getCurrentSite()->getCacheDirPath();
    }

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }
}