<?php
namespace Morpho\Core;

use Morpho\Fs\Directory;
use Morpho\Fs\File;
use Morpho\Fs\Path;

abstract class ModuleFs {
    protected $baseModuleDirPath;

    // @TODO
    //protected $useCache;

    private $registeredModules = [];

    private $moduleCache;

    private const CACHE_FILE_NAME = 'module-fs.php';

    public function __construct(string $baseModuleDirPath) {
        $this->baseModuleDirPath = $baseModuleDirPath;
        // @TODO: $this->useCache = $useCache;
    }

    public function baseModuleDirPath(): string {
        return $this->baseModuleDirPath;
    }

    public function clearCache(): void {
        $this->moduleCache = null;
        File::deleteIfExists($this->cacheDirPath() . '/' . self::CACHE_FILE_NAME);
    }

    public function moduleNames(): iterable {
        $this->initModuleCache();
        return array_keys($this->moduleCache);
    }

    abstract public function cacheDirPath(): string;

    /**
     * @return string|false
     */
    public function moduleClass(string $moduleName) {
        $this->initModuleCache();
        return $this->moduleCache[$moduleName]['class'];
    }

    /**
     * @return string|false
     */
    public function moduleNamespace(string $moduleName) {
        $this->initModuleCache();
        return $this->moduleCache[$moduleName]['namespace'];
    }

    public function moduleExists(string $moduleName): bool {
        $this->initModuleCache();
        return isset($this->moduleCache[$moduleName]);
    }

    public function moduleDirPath(string $moduleName): string {
        $this->initModuleCache();
        return $this->baseModuleDirPath . '/' . $this->moduleCache[$moduleName]['relDirPath'];
    }

    public function registerModuleAutoloader(string $moduleName): void {
        if (!isset($this->registeredModules[$moduleName])) {
            // @TODO: Register simple autoloader, which must try to load the class using simple scheme, then
            // call Composer's autoloader in case of fail.
            require $this->moduleDirPath($moduleName) . '/' . VENDOR_DIR_NAME . '/' . AUTOLOAD_FILE_NAME;
            $this->registeredModules[$moduleName] = true;
        }
    }

    private function initModuleCache(): void {
        if (null === $this->moduleCache) {
            $cacheFilePath = $this->cacheDirPath() . '/' . self::CACHE_FILE_NAME;
            if (is_file($cacheFilePath)) {
                $this->moduleCache = require $cacheFilePath;
            } else {
                $moduleCache = [];
                $filter = function ($path, $isDir) {
                    return $isDir && basename($path) !== VENDOR_DIR_NAME;
                };
                foreach (Directory::dirPaths($this->baseModuleDirPath(), $filter, ['recursive' => false]) as $moduleDirPath) {
                    $moduleMetaFilePath = $moduleDirPath . '/' . MODULE_META_FILE_NAME;
                    if (is_file($moduleMetaFilePath)) {
                        $meta = File::readJson($moduleMetaFilePath);
                        $moduleName = $meta['name'] ?? false;
                        if ($moduleName) {
                            $namespace = isset($meta['autoload']['psr-4']) ? rtrim(key($meta['autoload']['psr-4']), '\\') : false;
                            $class = false;
                            if ($namespace) {
                                $autoloadFilePath = $moduleDirPath . '/' . VENDOR_DIR_NAME . '/' . AUTOLOAD_FILE_NAME;
                                require $autoloadFilePath;
                                $class1 = $namespace . '\\' . basename(MODULE_CLASS_FILE_NAME, '.php');
                                if (class_exists($class1)) {
                                    $class = $class1;
                                } else {
                                    $class = Module::class;
                                }
                            }
                            $moduleCache[$moduleName] = [
                                'relDirPath' => Path::toRelative($this->baseModuleDirPath, $moduleDirPath),
                                'namespace' => $namespace,
                                'class' => $class,
                            ];
                        }
                    }
                }
                File::writePhpVar($cacheFilePath, $moduleCache);
                $this->moduleCache = $moduleCache;
            }
        }
    }
}