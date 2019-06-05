<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

use Morpho\Ioc\IServiceManager;
use Morpho\Fs\Dir;
use Morpho\Fs\File;

class ModuleIterator implements \IteratorAggregate {
    /**
     * @var string
     */
    private $baseDirPath;

    public function __construct(IServiceManager $serviceManager) {
        $this->init($serviceManager);
    }

    public function getIterator() {
        foreach ($this->dirIter() as $moduleDirPath) {
            $metaFilePath = $moduleDirPath . '/' . META_FILE_NAME;
            if (!\is_file($metaFilePath)) {
                continue;
            }
            $module = File::readJson($metaFilePath);
            $module['path'] = [
                'dirPath' => $moduleDirPath,
            ];
            if (!$this->filter($module)) {
                continue;
            }
            yield $this->map($module);
        }
    }

    protected function dirIter(): iterable {
        return Dir::dirPaths($this->baseDirPath . '/' . MODULE_DIR_NAME, null, ['recursive' => false]);
    }

    protected function filter(array $module): bool {
        return isset($module['name']);
    }

    protected function map(array $module): array {
        $namespaces = [];
        foreach ($module['autoload']['psr-4'] ?? [] as $key => $value) {
            $namespaces[\trim($key, '\\/')] = \trim($value, '\\/');
        }

        $moduleName = $module['name'];
        return [
            'name' => $moduleName,
            'path' => $module['path'],
            'namespace' => $namespaces,
        ];
    }

    protected function init(IServiceManager $serviceManager): void {
        $this->baseDirPath = $serviceManager['app']->config()['baseDirPath'];
    }
}
