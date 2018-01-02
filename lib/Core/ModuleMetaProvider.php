<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Ioc\IServiceManager;
use Morpho\Fs\Directory;
use Morpho\Fs\File;

class ModuleMetaProvider implements \IteratorAggregate {
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
            if (!is_file($metaFilePath)) {
                continue;
            }
            $moduleMeta = File::readJson($metaFilePath);
            $moduleMeta['paths'] = [
                'dirPath' => $moduleDirPath,
            ];
            if (!$this->filter($moduleMeta)) {
                continue;
            }
            yield $this->map($moduleMeta);
        }
    }

    protected function dirIter(): iterable {
        return Directory::dirPaths($this->baseDirPath . '/' . MODULE_DIR_NAME, null, ['recursive' => false]);
    }

    protected function filter(array $moduleMeta): bool {
        if (!isset($moduleMeta['name']) || !isset($moduleMeta['autoload']['psr-4'])) {
            return false;
        }
        $autoloadFilePath = $moduleMeta['paths']['dirPath'] . '/' . VENDOR_DIR_NAME . '/' . AUTOLOAD_FILE_NAME;
        return is_file($autoloadFilePath);
    }

    protected function map(array $moduleMeta): array {
        $namespaces = [];
        foreach ($moduleMeta['autoload']['psr-4'] as $key => $value) {
            $namespaces[trim($key, '\\/')] = trim($value, '\\/');
        }

        $moduleName = $moduleMeta['name'];
        $moduleMeta = [
            'name' => $moduleName,
            'paths' => $moduleMeta['paths'],
            'namespaces' => $namespaces,
        ];
        return $moduleMeta;
    }

    protected function init(IServiceManager $serviceManager): void {
        $this->baseDirPath = $serviceManager->get('app')->config()['baseDirPath'];
    }
}