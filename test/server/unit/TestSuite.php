<?php
use Morpho\Core\ModuleListProvider;
use Morpho\Core\ModulePathManager;
use Morpho\Fs\Directory;
use Morpho\Web\ModuleManager;

class TestSuite extends \Morpho\Test\TestSuite {
    public function listTestFiles() {
        return array_merge(
            iterator_to_array(Directory::listFiles(__DIR__ . '/MorphoTest', $this->testFileRegexp), false),
            $this->listTestFilesOfModules()
        );
    }

    protected function listTestFilesOfModules() {
        $filePaths = [];
        $modulePathManager = new ModulePathManager(MODULE_DIR_PATH);
        $moduleManager = new ModuleManager(null, new ModuleListProvider($modulePathManager));
        foreach ($moduleManager->listAllModules() as $moduleName) {
            $filePaths = array_merge($filePaths, $modulePathManager->getTestFilePaths($moduleName));
        }
        return $filePaths;
    }
}
