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

    public function baseCacheDirPath(): string {
        return $this->serviceManager->get('siteManager')->currentSite()->cacheDirPath();
    }

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }
    
    public function moduleViewDirPath(string $moduleName): string {
        return $this->moduleDirPath($moduleName) . '/' . VIEW_DIR_NAME;
    }

    public function registerModuleAutoloader(string $moduleName) {
        if (isset($this->registeredModules[$moduleName])) {
            return;
        }

        parent::registerModuleAutoloader($moduleName);

        $dirPath = $this->moduleViewDirPath($moduleName);
        if (is_dir($dirPath)) {
            $cacheFilePath = $this->moduleCacheDirPath($moduleName) . '/autoload_classmap.php';
            if (is_file($cacheFilePath)) {
                $classTypes = requireFile($cacheFilePath);
                if ($classTypes) {
                    $this->autoloader->addClassMap($classTypes);
                }
            } else {
                $classTypeDiscoverer = new ClassTypeDiscoverer();
                $classTypes = $classTypeDiscoverer->classTypesDefinedInDir($dirPath);
                if ($classTypes) {
                    $this->autoloader->addClassMap($classTypes);
                }
                File::writePhpVar($cacheFilePath, $classTypes);
            }
        }
    }
}