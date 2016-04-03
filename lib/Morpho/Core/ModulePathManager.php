<?php
namespace Morpho\Core;

use Morpho\Code\ClassTypeDiscoverer;
use Morpho\Fs\Directory;

class ModulePathManager {
    protected $allModuleDirPath;

    public function __construct($allModuleDirPath) {
        $this->allModuleDirPath = $allModuleDirPath;
    }
    
    public function getAllModuleDirPath(): string {
        return $this->allModuleDirPath;
    }

    public function getModuleDirPath(string $moduleName): string {
        return $this->getAllModuleDirPath() . '/' . \Morpho\Base\dasherize($moduleName);
    }
    
    public function getTestFilePaths(string $moduleName): array {
        $dirPath = $this->getModuleDirPath($moduleName) . '/' . TEST_DIR_NAME;
        if (!is_dir($dirPath)) {
            return [];
        }
        return iterator_to_array(
            Directory::listFiles($dirPath, '~(Test|TestSuite)\.php$~s'),
            false
        );
    }

    public function getControllerFilePaths(string $moduleName): array {
        $dirPath = $this->getModuleDirPath($moduleName) . '/' . CONTROLLER_DIR_NAME;
        if (!is_dir($dirPath)) {
            return [];
        }
        return iterator_to_array(
            Directory::listFiles($dirPath, '~\.php$~s'),
            false
        );
    }
}