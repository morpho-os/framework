<?php
use Morpho\Fs\Directory;

class TestSuite extends \Morpho\Test\TestSuite {
    public function listTestFiles() {
        return array_merge(
            Directory::listFiles(__DIR__ . '/MorphoTest', $this->testFileRegexp),
            $this->listTestFilesOfModules()
        );
    }

    protected function listTestFilesOfModules() {
        $paths = [];
        foreach (glob(MODULE_DIR_PATH . '/*') as $path) {
            $testDirPath = $path . '/' . TEST_DIR_NAME;
            if (is_dir($testDirPath)) {
                $paths = array_merge(
                    $paths,
                    Directory::listFiles($testDirPath, $this->testFileRegexp)
                );
            }
        }
        return $paths;
    }
}
