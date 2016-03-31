<?php
namespace Morpho\Core;

use function Morpho\Base\classify;
use Morpho\Fs\Directory;

class ModuleListProvider implements \IteratorAggregate {
    protected $modulePathManager;
    
    public function __construct(ModulePathManager $modulePathManager) {
        $this->modulePathManager = $modulePathManager;
    }
    
    public function getIterator(): \Generator {
        foreach (Directory::listDirs($this->modulePathManager->getAllModuleDirPath(), null, ['recursive' => false]) as $moduleDirPath) {
            $composeFilePath = $moduleDirPath . '/' . COMPOSER_FILE_NAME;
            $moduleClassFilePath = $moduleDirPath . '/' . MODULE_CLASS_FILE_NAME;
            $moduleName = null;
            if (is_file($composeFilePath)) {
                // @TODO
                //$moduleName = $this->getModuleNameFromComposerFile($composeFilePath);
                if ($moduleName) {
                    yield $moduleName;
                    continue;
                }
            }
            if (is_file($moduleClassFilePath)) {
                yield classify(basename($moduleDirPath));
            }
        }
    }
}