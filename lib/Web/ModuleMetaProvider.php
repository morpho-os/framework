<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use const Morpho\Core\AUTOLOAD_FILE_NAME;
use const Morpho\Core\META_FILE_NAME;
use Morpho\Core\Module;
use const Morpho\Core\MODULE_CLASS_FILE_NAME;
use const Morpho\Core\VENDOR_DIR_NAME;
use const Morpho\Core\VIEW_DIR_NAME;
use Morpho\Fs\Directory;
use Morpho\Fs\File;
use Zend\Stdlib\ArrayUtils;

class ModuleMetaProvider implements \IteratorAggregate {
    /**
     * @var string
     */
    private $baseModuleDirPath;
    /**
     * @var array
     */
    private $activeModules;

    /**
     * @var array
     */
    private $metaPatch;

    public function __construct(string $baseModuleDirPath, array $activeModules, array $metaPatch) {
        $this->baseModuleDirPath = $baseModuleDirPath;
        $this->activeModules = array_flip($activeModules);
        $this->metaPatch = $metaPatch;
    }

    public function getIterator() {
        foreach (Directory::dirPaths($this->baseModuleDirPath, null, ['recursive' => false]) as $moduleDirPath) {
            $metaFilePath = $moduleDirPath . '/' . META_FILE_NAME;
            if (!is_file($metaFilePath)) {
                continue;
            }
            $classLoaderMeta = File::readJson($metaFilePath);
            if (!isset($classLoaderMeta['name'])) {
                continue;
            }
            $moduleName = $classLoaderMeta['name'];
            if (!isset($this->activeModules[$moduleName])) {
                continue;
            }
            $namespace = isset($classLoaderMeta['autoload']['psr-4']) ? rtrim(key($classLoaderMeta['autoload']['psr-4']), '\\') : false;
            if (!$namespace) {
                continue;
            }
            $autoloadFilePath = $moduleDirPath . '/' . VENDOR_DIR_NAME . '/' . AUTOLOAD_FILE_NAME;
            require $autoloadFilePath;
            $class1 = $namespace . '\\' . basename(MODULE_CLASS_FILE_NAME, '.php');
            if ($class1 && class_exists($class1)) {
                $class = $class1;
            } else {
                $class = Module::class;
            }
            $moduleMeta = [
                'name' => $moduleName,
                'paths' => [
                    'dirPath' => $moduleDirPath,
                    'viewDirPath' => $moduleDirPath . '/' . VIEW_DIR_NAME,
                ],
                'namespace' => $namespace,
                'class'     => $class,
                'weight' => $this->activeModules[$moduleName],
            ];
            if (isset($this->metaPatch[$moduleName])) {
                $moduleMeta = ArrayUtils::merge($moduleMeta, $this->metaPatch[$moduleName]);
            }
            yield $moduleMeta;
        }
    }
}