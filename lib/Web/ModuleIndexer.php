<?php //declare(strict_types=1);

namespace Morpho\Web;

use Morpho\Core\IModuleIndexer;
use Morpho\Fs\File;
use Zend\Stdlib\ArrayUtils;
use const Morpho\Core\AUTOLOAD_FILE_NAME;
use const Morpho\Core\META_FILE_NAME;
use const Morpho\Core\MODULE_CLASS_FILE_NAME;
use const Morpho\Core\VENDOR_DIR_NAME;

class ModuleIndexer implements IModuleIndexer {
    private $indexFilePath;
    private $moduleDirsIterator;
    private $activeModules;
    private $modulesConfig;

    public function __construct(iterable $moduleDirsIterator, string $indexFilePath, array $modulesConfig, array $activeModules) {
        $this->moduleDirsIterator = $moduleDirsIterator;
        $this->indexFilePath = $indexFilePath;
        $this->modulesConfig = $modulesConfig;
        $this->activeModules = array_flip($activeModules);
    }

    public function build() {
        $indexFilePath = $this->indexFilePath;
        if (is_file($indexFilePath)) {
            return require $indexFilePath;
        } else {
            $index = $this->indexModules();
            $index = $this->mergeModulesConfig($index, $this->modulesConfig);
            $index = $this->sortIndex($index);
            File::writePhpVar($indexFilePath, $index);
            return $index;
        }
    }

    public function clear() {
        File::deleteIfExists($this->indexFilePath);
    }

    private function indexModules() {
        $index = [];
        foreach ($this->moduleDirsIterator as $moduleDirPath) {
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
                        'dirPath' => $moduleDirPath,
                        'viewDirPath' => $moduleDirPath . '/' . VIEW_DIR_NAME,
                    ];
                    $index[$moduleName] = [
                        'paths'     => $paths,
                        'namespace' => $namespace,
                        'class'     => $class,
                    ];
                }
            }
        }
        return $index;
    }

    private function mergeModulesConfig($index, $modulesConfig) {
        foreach ($modulesConfig as $moduleName => $config) {
            if (isset($config['modules'])) {
                foreach ($config['modules'] as $module => $conf) {
                    $index[$module] = ArrayUtils::merge($index[$module], $conf);
                }
            }
            unset($config['modules']);
            $index[$moduleName] = ArrayUtils::merge($index[$moduleName], $config);
        }
        return $index;
    }

    private function sortIndex($index) {
        uksort($index, function ($a, $b) {
            // use $this->activeModules
            return $this->activeModules[$a] - $this->activeModules[$b];
        });
        return $index;
    }
}