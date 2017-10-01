<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Base\NotImplementedException;
use Morpho\Fs\Directory;
use Morpho\Fs\File;
use Morpho\Fs\Path;

class Fs {
    /**
     * @var string
     */
    protected $baseDirPath;

    /**
     * @var ?string
     */
    protected $baseModuleDirPath;

    /**
     * @var ?string
     */
    private $configDirPath;

    /**
     * @var ?string
     */
    private $vendorDirPath;

    /**
     * @var ?string
     */
    protected $configFilePath;

    // @TODO
    //protected $useCache;

    /**
     * @var ?array
     */
    private $moduleCache;

    /**
     * @var array
     */
    private $registeredModules = [];

    private const CACHE_FILE_NAME = 'module-fs.php';

    public function __construct(string $baseDirPath) {
        $this->baseDirPath = $baseDirPath;
        // @TODO: $this->useCache = $useCache;
    }

    /**
     * @return false|string
     */
    public static function detectBaseDirPath(string $dirPath, bool $throwEx = true) {
        if (null === $dirPath) {
            $dirPath = __DIR__;
        }
        $baseDirPath = null;
        do {
            $path = $dirPath . '/vendor/composer/ClassLoader.php';
            if (is_file($path)) {
                $baseDirPath = $dirPath;
                break;
            } else {
                $chunks = explode(DIRECTORY_SEPARATOR, $dirPath, -1);
                $dirPath = implode(DIRECTORY_SEPARATOR, $chunks);
            }
        } while ($chunks);
        if (null === $baseDirPath) {
            if ($throwEx) {
                throw new \RuntimeException("Unable to find a path of the root directory");
            }
            return null;
        }
        return Path::normalize($baseDirPath);
    }

    public function setBaseDirPath(string $baseDirPath): void {
        $this->baseDirPath = $baseDirPath;
    }

    public function baseDirPath(): string {
        return $this->baseDirPath;
    }

    public function setBaseModuleDirPath(string $baseModuleDirPath): void {
        $this->baseModuleDirPath = $baseModuleDirPath;
    }

    public function baseModuleDirPath(): string {
        if (null === $this->baseModuleDirPath) {
            $this->baseModuleDirPath = $this->baseDirPath() . '/' . MODULE_DIR_NAME;
        }
        return $this->baseModuleDirPath;
    }

    public function setVendorDirPath(string $vendorDirPath): void {
        $this->vendorDirPath = $vendorDirPath;
    }

    public function vendorDirPath(): string {
        if (null === $this->vendorDirPath) {
            $this->vendorDirPath = $this->baseDirPath() . '/' . VENDOR_DIR_NAME;
        }
        return $this->vendorDirPath;
    }

    public function setConfigDirPath(string $configDirPath): void {
        $this->configDirPath = $configDirPath;
    }

    public function configDirPath(): string {
        if (null === $this->configDirPath) {
            $this->configDirPath = $this->baseDirPath() . '/' . CONFIG_DIR_NAME;
        }
        return $this->configDirPath;
    }

    public function loadConfigFile(): array {
        return require $this->configFilePath();
    }

    public function setConfigFilePath(string $configFilePath): void {
        $this->configFilePath = $configFilePath;
    }

    public function configFilePath(): string {
        if (null === $this->configFilePath) {
            $this->configFilePath = $this->configDirPath() . '/' . CONFIG_FILE_NAME;
        }
        return $this->configFilePath;
    }

    public function clearCache(): void {
        $this->moduleCache = null;
        File::deleteIfExists($this->cacheDirPath() . '/' . self::CACHE_FILE_NAME);
    }

    public function moduleNames(): iterable {
        $this->initModuleCache();
        return array_keys($this->moduleCache);
    }

    public function cacheDirPath(): string {
        throw new NotImplementedException();
    }

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
        return $this->baseModuleDirPath() . '/' . $this->moduleCache[$moduleName]['relDirPath'];
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
                    $moduleMetaFilePath = $moduleDirPath . '/' . META_FILE_NAME;
                    if (is_file($moduleMetaFilePath)) {
                        $autoloadFilePath = $moduleDirPath . '/' . VENDOR_DIR_NAME . '/' . AUTOLOAD_FILE_NAME;
                        $meta = File::readJson($moduleMetaFilePath);
                        $moduleName = $meta['name'] ?? false;
                        if ($moduleName) {
                            $namespace = isset($meta['autoload']['psr-4']) ? rtrim(key($meta['autoload']['psr-4']), '\\') : false;
                            $class = false;
                            if ($namespace) {
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