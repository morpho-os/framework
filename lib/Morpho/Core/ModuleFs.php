<?php
namespace Morpho\Core;

//use function Morpho\Base\classify;
//use function Morpho\Base\dasherize;
//use function Morpho\Base\head;
use function Morpho\Base\requireFile;
use Morpho\Code\ClassTypeDiscoverer;
use Morpho\Fs\Directory;
use Morpho\Fs\File;
use Morpho\Fs\Path;
use const Morpho\Web\DOMAIN_DIR_NAME;

abstract class ModuleFs {
    protected $baseModuleDirPath;

    //protected $useCache;

    protected $autoloader;

    protected $registeredModules = [];

    protected $moduleCache;

    const CACHE_FILE_NAME = 'module-fs.php';

    public function __construct(string $baseModuleDirPath, $autoloader) {
        $this->baseModuleDirPath = $baseModuleDirPath;
        $this->autoloader = $autoloader;
/*
$this->cacheDirPath = $cacheDirPath;
$this->useCache = $useCache;
*/
    }
    
    public function clearCache()/*: void */ {
        $this->moduleCache = null;
        File::deleteIfExists($this->baseCacheDirPath() . '/' . self::CACHE_FILE_NAME);
    }
    
    abstract public function baseCacheDirPath(): string;

    public function moduleNamespace(string $moduleName): string {
        $this->initModuleCache();
        return $this->moduleCache[$moduleName]['namespace'];
    }

    public function moduleCacheDirPath(string $moduleName): string {
        return $this->baseCacheDirPath() . '/' . $moduleName;
    }
    
    public function moduleControllerDirPath(string $moduleName): string {
        return $this->moduleDirPath($moduleName) . '/' . CONTROLLER_DIR_NAME;
    }
    
    public function baseModuleDirPath(): string {
        return $this->baseModuleDirPath;
    }

    public function moduleNames() {
        $this->initModuleCache();
        return array_keys($this->moduleCache);
    }

    public function doesModuleExist(string $moduleName): bool {
        $this->initModuleCache();
        return isset($this->moduleCache[$moduleName]);
    }

    /**
     * @return string|null Returns null when module does not have the class, return string otherwise.
     */
    public function moduleClass(string $moduleName) {
        $this->initModuleCache();
        return $this->moduleCache[$moduleName]['class'];
    }

    /**
     * @param string $moduleName
     */
    public function moduleDirPath(string $moduleName): string {
        $this->initModuleCache();
        return $this->baseModuleDirPath . '/' . $this->moduleCache[$moduleName]['dirPath'];
    }

    public function moduleControllerFilePaths(string $moduleName): array {
        $dirPath = $this->moduleControllerDirPath($moduleName);
        if (!is_dir($dirPath)) {
            return [];
        }
        return iterator_to_array(
            Directory::filePaths($dirPath, '~.Controller\.php$~s', ['recursive' => true]),
            false
        );
    }

    /**
     * Registers the module, so that its classes will be automatically loaded.
     */
    public function registerModuleAutoloader(string $moduleName) {
        if (isset($this->registeredModules[$moduleName])) {
            return false;
        }
        
        $moduleDirPath = $this->moduleDirPath($moduleName);
        //$autoloadFilePath = $moduleDirPath . '/' . VENDOR_DIR_NAME . '/' . AUTOLOAD_FILE_NAME;
        $composerFilesDirPath = $moduleDirPath . '/' . VENDOR_DIR_NAME . '/composer';
        $autoloader = $this->autoloader;
        
        // @TODO: Optimize the autoloading process, use one file for the all 3 composer's autoload_* files.

        $filePath = $composerFilesDirPath . '/autoload_namespaces.php';
        if (is_file($filePath)) {
            $map = requireFile($filePath);
            foreach ($map as $namespace => $path) {
                $autoloader->set($namespace, $path);
            }
        }

        $filePath = $composerFilesDirPath . '/autoload_psr4.php';
        if (is_file($filePath)) {
            $map = requireFile($filePath);
            foreach ($map as $namespace => $path) {
                $autoloader->setPsr4($namespace, $path);
            }

        }
        $moduleNs = $this->moduleNamespace($moduleName);
        foreach ([CONTROLLER_NS => CONTROLLER_DIR_NAME, DOMAIN_NS => DOMAIN_DIR_NAME] as $ns => $dirName) {
            $autoloader->setPsr4($moduleNs . '\\' . $ns . '\\', $moduleDirPath . '/' . $dirName);
        }

        $filePath = $composerFilesDirPath . '/autoload_classmap.php';
        if (is_file($filePath)) {
            $classMap = requireFile($filePath);
            if ($classMap) {
                $autoloader->addClassMap($classMap);
            }
        }

        $this->registeredModules[$moduleName] = true;
        
        return true;
    }

    protected function initModuleCache() {
        if (null === $this->moduleCache) {
            $cacheFilePath = $this->baseCacheDirPath() . '/' . self::CACHE_FILE_NAME;
            if (is_file($cacheFilePath)) {
                $this->moduleCache = requireFile($cacheFilePath);
            } else {
                $moduleCache = [];
                $filter = function ($path, $isDir) {
                    return !$isDir || ($isDir && basename($path) !== VENDOR_DIR_NAME);
                };
                $classTypeDiscoverer = new ClassTypeDiscoverer();
                foreach (Directory::dirPaths($this->baseModuleDirPath(), $filter, ['recursive' => false]) as $moduleDirPath) {
                    $composerFilePath = $moduleDirPath . '/' . MODULE_META_FILE_NAME;
                    if (is_file($composerFilePath)) {
                        $meta = File::readJson($composerFilePath);
                        $moduleName = $meta['name'] ?? false;
                        if ($moduleName) {
                            $moduleCache[$moduleName] = ['dirPath' => Path::toRelative($this->baseModuleDirPath, $moduleDirPath)];
                            $moduleFilePath = $moduleDirPath . '/' . MODULE_CLASS_FILE_NAME;
                            $class = null;
                            if (is_file($moduleFilePath)) {
                                $classTypes = $classTypeDiscoverer->definedClassTypesInFile($moduleFilePath);
                                if (count($classTypes)) {
                                    $class = key($classTypes);
                                }
                            }
                            $moduleCache[$moduleName]['class'] = $class;
                            $moduleCache[$moduleName]['namespace'] = isset($meta['autoload']['psr-4']) ? rtrim(key($meta['autoload']['psr-4']), '\\') : null;
                        }
                    }
                }
                File::writePhpVar($cacheFilePath, $moduleCache);
                $this->moduleCache = $moduleCache;
            }
        }
    }
    /*
    public function testFilePaths(string $moduleName): array {
        $dirPath = $this->moduleDirPath($moduleName) . '/' . TEST_DIR_NAME;
        if (!is_dir($dirPath)) {
            return [];
        }
        return iterator_to_array(
            Directory::filePaths($dirPath, '~.(Test|TestSuite)\.php$~s', ['recursive' => true]),
            false
        );
    }

    public function classTypeMap(string $moduleName): array {
        $moduleDirPath = $this->moduleDirPath($moduleName);
        $classTypeDiscoverer = new ClassTypeDiscoverer();
        $moduleClassFilePath = $moduleDirPath . '/' . MODULE_CLASS_FILE_NAME;
        $map = [];
        if (is_file($moduleClassFilePath)) {
            $map = array_merge($map, $classTypeDiscoverer->definedClassTypesInFile($moduleClassFilePath));
        }
        $map = array_merge($map, $classTypeDiscoverer->definedClassTypesInDir(
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
    */
}