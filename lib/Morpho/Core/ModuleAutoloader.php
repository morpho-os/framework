<?php
namespace Morpho\Core;

use function Morpho\Base\dasherize;
use Morpho\Base\Autoloader;
use Morpho\Base\EmptyPropertyException;
use function Morpho\Base\head;
use Morpho\Code\ClassTypeDiscoverer;
use Morpho\Fs\File;

class ModuleAutoloader extends Autoloader {
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
                $map = $this->getClassTypeMap($moduleName);
                File::write($cacheFilePath, '<?php return ' . var_export($map, true) . ';');
            }
        } else {
            $map = $this->getClassTypeMap($moduleName);
        }
        $this->map[$moduleName] = $map;
    }
    
    protected function getClassTypeMap(string $moduleName): array {
        $classTypeDiscoverer = new ClassTypeDiscoverer();
        $moduleDirPath = $this->modulePathManager->getModuleDirPath($moduleName);
        $moduleClassFilePath = $moduleDirPath . '/' . MODULE_CLASS_FILE_NAME;
        $map = [];
        if (is_file($moduleClassFilePath)) {
            $map = array_merge($map, $classTypeDiscoverer->classTypesDefinedInFile($moduleClassFilePath));
        }
        $map = array_merge($map, $classTypeDiscoverer->classTypesDefinedInDir(
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