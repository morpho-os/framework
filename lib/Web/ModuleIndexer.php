<?php //declare(strict_types=1);

namespace Morpho\Web;

use Morpho\Core\IModuleIndexer;
use Morpho\Fs\Directory;
use Morpho\Fs\File;
use Morpho\Fs\Path;
use Zend\Stdlib\ArrayUtils;
use const Morpho\Core\AUTOLOAD_FILE_NAME;
use const Morpho\Core\META_FILE_NAME;
use const Morpho\Core\MODULE_CLASS_FILE_NAME;
use const Morpho\Core\VENDOR_DIR_NAME;

class ModuleIndexer implements IModuleIndexer {
    private $indexFilePath;
    private $baseModuleDirPath;
    private $activeModules;
    private $modulesConfig;

    public function __construct(string $baseModuleDirPath, string $indexFilePath, array $modulesConfig, array $activeModules) {
        $this->baseModuleDirPath = $baseModuleDirPath;
        $this->indexFilePath = $indexFilePath;
        $this->modulesConfig = $modulesConfig;
        $this->activeModules = array_flip($activeModules);
    }

    public function build() {
        $baseModuleDirPath = $this->baseModuleDirPath;
        $indexFilePath = $this->indexFilePath;
        if (is_file($indexFilePath)) {
            return require $indexFilePath;
        } else {
            $index = [];
            foreach ($this->moduleDirPaths() as $moduleDirPath) {
                $moduleMetaFilePath = $moduleDirPath . '/' . META_FILE_NAME;
                if (is_file($moduleMetaFilePath)) {
                    $autoloadFilePath = $moduleDirPath . '/' . VENDOR_DIR_NAME . '/' . AUTOLOAD_FILE_NAME;
                    $meta = File::readJson($moduleMetaFilePath);
                    $moduleName = $meta['name'] ?? false;
                    if ($moduleName && isset($this->activeModules[$moduleName])) {
                        $namespace = isset($meta['autoload']['psr-4']) ? rtrim(key($meta['autoload']['psr-4']), '\\') : false;
                        if (!$namespace) {
                            continue;
                        }
                        require $autoloadFilePath;
                        $class1 = $namespace . '\\Web\\' . basename(MODULE_CLASS_FILE_NAME, '.php');
                        if ($class1 && class_exists($class1)) {
                            $class = $class1;
                        } else {
                            $class = Module::class;
                        }
                        $paths = [
                            'baseDirPath' => $baseModuleDirPath,
                            'relDirPath'  => Path::toRelative($baseModuleDirPath, $moduleDirPath),
                        ];
                        $paths['viewDirPath'] = $paths['baseDirPath'] . '/' . $paths['relDirPath'] . '/' . VIEW_DIR_NAME;
                        $index[$moduleName] = [
                            'paths'     => $paths,
                            'namespace' => $namespace,
                            'class'     => $class,
                        ];
                    }
                }
            }

            foreach ($this->modulesConfig as $moduleName => $config) {
                if (isset($config['modules'])) {
                    foreach ($config['modules'] as $module => $conf) {
                        $index[$module] = ArrayUtils::merge($index[$module], $conf);
                    }
                }
                unset($config['modules']);
                $index[$moduleName] = ArrayUtils::merge($index[$moduleName], $config);
            }

            uksort($index, function ($a, $b) {
                // use $this->activeModules
                return $this->activeModules[$a] - $this->activeModules[$b];
            });

            File::writePhpVar($indexFilePath, $index);
            return $index;
        }
    }

    public function clear() {
        File::deleteIfExists($this->indexFilePath);
    }

    private function moduleDirPaths(): iterable {
        $filter = function ($path, $isDir) {
            return $isDir && basename($path) !== VENDOR_DIR_NAME;
        };
        return Directory::dirPaths($this->baseModuleDirPath, $filter, ['recursive' => false]);
    }
}