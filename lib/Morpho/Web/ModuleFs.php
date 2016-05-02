<?php
namespace Morpho\Web;

use Morpho\Code\ClassTypeDiscoverer;
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
    
    public function getModuleViewDirPath(string $moduleName): string {
        return $this->getModuleDirPath($moduleName) . '/' . VIEW_DIR_NAME;
    }

    public function registerModuleAutoloader(string $moduleName): bool {
        if (parent::registerModuleAutoloader($moduleName) === false) {
            return false;
        }
        $dirPath = $this->getModuleViewDirPath($moduleName);
        if (is_dir($dirPath)) {
            // @TODO: Add caching
            $classTypeDiscoverer = new ClassTypeDiscoverer();
            $classTypes = $classTypeDiscoverer->definedClassTypesInDir($dirPath);
            if ($classTypes) {
                $this->autoloader->addClassMap($classTypes);
            }
        }
        return true;
    }
}