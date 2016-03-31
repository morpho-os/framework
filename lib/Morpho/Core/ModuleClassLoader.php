<?php
namespace Morpho\Core;

use function Morpho\Base\dasherize;
use Morpho\Base\Autoloader;
use Morpho\Base\EmptyPropertyException;
use function Morpho\Base\head;
use Morpho\Code\ClassDiscoverer;
use Morpho\Fs\File;

class ModuleClassLoader extends Autoloader {
    protected $registered = false;
    
    protected $modulePathManager;

    protected $cacheDirPath;

    protected $useCache;

    protected $map;

    public function __construct(ModulePathManager $modulePathManager, string $cacheDirPath = null, bool $useCache) {
        $this->modulePathManager = $modulePathManager;
        $this->cacheDirPath = $cacheDirPath;
        $this->useCache = $useCache;
    }

    public function clearCache() {
        $this->map = null;
    }
    
    public function registerModule(string $moduleName) {
        $moduleDirPath = $this->modulePathManager->getModuleDirPath($moduleName);
        $composerAutoloadFilePath = $moduleDirPath . '/' . COMPOSER_AUTOLOAD_FILE_PATH;
        if (is_file($composerAutoloadFilePath)) {
            require $composerAutoloadFilePath;
        }
        if ($this->useCache) {
            if (empty($this->cacheDirPath)) {
                throw new EmptyPropertyException($this, 'cacheDirPath');
            }
            $cacheFilePath = $this->cacheDirPath . '/' . dasherize($moduleName) . '-class-map.php';
            if (is_file($cacheFilePath)) {
                $map = require $cacheFilePath;
            } else {
                $map = $this->getClassMap($moduleName);
                File::write($cacheFilePath, '<?php return ' . var_export($map, true) . ';');
            }
        } else {
            $map = $this->getClassMap($moduleName);
        }
        $this->map[$moduleName] = $map;
    }
    
    protected function getClassMap(string $moduleName): array {
        $classDiscoverer = new ClassDiscoverer();
        $moduleDirPath = $this->modulePathManager->getModuleDirPath($moduleName);
        $moduleClassFilePath = $moduleDirPath . '/' . MODULE_CLASS_FILE_NAME;
        $map = [];
        if (is_file($moduleClassFilePath)) {
            $map = array_merge($map, $classDiscoverer->getClassMapForFile($moduleClassFilePath));
        }
        $map = array_merge($map, $classDiscoverer->getClassMapForDir(
            array_filter(
                array_map(
                    function ($dirName) use ($moduleDirPath) {
                        $dirPath = $moduleDirPath . '/' . $dirName;
                        return is_dir($dirPath) ? $dirPath : null;
                    },
                    [CONTROLLER_DIR_NAME, DOMAIN_DIR_NAME, VIEW_DIR_NAME, TEST_DIR_NAME]
                )
            )
        ));
        return $map;
    }

    public function findFilePath(string $class) {
        $moduleName = head($class, '\\');
        return $this->map[$moduleName][$class] ?? false;
    }
}