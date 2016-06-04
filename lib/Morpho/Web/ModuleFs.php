<?php
namespace Morpho\Web;

use Morpho\Code\ClassTypeDiscoverer;
use Morpho\Core\ModuleFs as BaseModuleFs;
use Morpho\Di\IServiceManager;
use Morpho\Di\IServiceManagerAware;
use Morpho\Fs\File;

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

    public function registerModuleAutoloader(string $moduleName) {
        if (isset($this->registeredModules[$moduleName])) {
            return;
        }

        parent::registerModuleAutoloader($moduleName);

        $dirPath = $this->getModuleViewDirPath($moduleName);
        if (is_dir($dirPath)) {
            $cacheFilePath = $this->getModuleCacheDirPath($moduleName) . '/autoload_classmap.php';
            if (is_file($cacheFilePath)) {
                $classTypes = require $cacheFilePath;
                if ($classTypes) {
                    $this->autoloader->addClassMap($classTypes);
                }
            } else {
                $classTypeDiscoverer = new ClassTypeDiscoverer();
                $classTypes = $classTypeDiscoverer->definedClassTypesInDir($dirPath);
                if ($classTypes) {
                    $this->autoloader->addClassMap($classTypes);
                }
                File::writePhpVar($cacheFilePath, $classTypes);
            }
        }
    }
}