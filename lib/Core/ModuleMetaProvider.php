<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Ioc\IServiceManager;
use Morpho\Fs\Directory;
use Morpho\Fs\File;
use Zend\Stdlib\ArrayUtils;

class ModuleMetaProvider implements \IteratorAggregate {
    /**
     * @var string
     */
    protected $baseDirPath;
    /**
     * @var array
     */
    protected $enabledModules;
    /**
     * @var array
     */
    protected $metaPatch;

    public function __construct(IServiceManager $serviceManager) {
        $this->init($serviceManager);
    }

    public function getIterator() {
        foreach ($this->dirIter() as $moduleDirPath) {
            $metaFilePath = $moduleDirPath . '/' . META_FILE_NAME;
            if (!is_file($metaFilePath)) {
                continue;
            }
            $classLoaderMeta = File::readJson($metaFilePath);
            if (!isset($classLoaderMeta['name'])) {
                continue;
            }
            $moduleName = $classLoaderMeta['name'];
            if (!$this->filter($moduleName)) {
                continue;
            }
            $namespace = isset($classLoaderMeta['autoload']['psr-4']) ? rtrim(key($classLoaderMeta['autoload']['psr-4']), '\\') : false;
            if (!$namespace) {
                continue;
            }
            $autoloadFilePath = $moduleDirPath . '/' . VENDOR_DIR_NAME . '/' . AUTOLOAD_FILE_NAME;
            if (!is_file($autoloadFilePath)) {
                continue;
            }
            require_once $autoloadFilePath;
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
            ];
            yield $this->map($moduleMeta);
        }
    }

    protected function dirIter(): iterable {
        return Directory::dirPaths($this->baseDirPath . '/' . MODULE_DIR_NAME, null, ['recursive' => false]);
    }

    protected function filter(string $moduleName): bool {
        return isset($this->enabledModules[$moduleName]);
    }

    protected function map(array $moduleMeta): array {
        $moduleName = $moduleMeta['name'];
        $moduleMeta['weight'] = $this->enabledModules[$moduleName];
        if (isset($this->metaPatch[$moduleName])) {
            $moduleMeta = ArrayUtils::merge($moduleMeta, $this->metaPatch[$moduleName]);
        }
        return $moduleMeta;
    }

    protected function init(IServiceManager $serviceManager): void {
        $this->baseDirPath = $serviceManager->get('app')->config()['baseDirPath'];
    }
}