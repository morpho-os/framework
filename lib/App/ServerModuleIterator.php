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

class ServerModuleIterator implements \IteratorAggregate {
    private string $baseModuleDirPath;

    public function __construct(IServiceManager $serviceManager) {
        $this->baseModuleDirPath = $serviceManager['app']->config()['baseServerModuleDirPath'];
    }

    public function getIterator() {
        foreach ($this->dirIt() as $moduleDirPath) {
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

    protected function dirIt(): iterable {
        return Dir::dirPaths($this->baseModuleDirPath, null, ['recursive' => false]);
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
}
