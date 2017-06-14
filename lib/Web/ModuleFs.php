<?php
namespace Morpho\Web;

use function Morpho\Base\requireFile;
use Morpho\Code\ClassTypeDiscoverer;
use Morpho\Core\ModuleFs as BaseModuleFs;
use Morpho\Di\IServiceManager;
use Morpho\Di\IServiceManagerAware;
use Morpho\Fs\File;

class ModuleFs extends BaseModuleFs implements IServiceManagerAware {
    protected $serviceManager;

    public function cacheDirPath(): string {
        return $this->serviceManager->get('site')->cacheDirPath();
    }

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }
}