<?php
use Morpho\Fs\Directory;

class TestSuite extends \Morpho\Test\TestSuite {
    public function listTestFiles() {
        return d(array_merge(
            iterator_to_array(Directory::listFiles(__DIR__ . '/MorphoTest', $this->testFileRegexp), false),
            $this->listTestFilesOfModules()
        ));
    }

    protected function listTestFilesOfModules() {
        $filter = function ($path, $isDir) {
            if ($isDir) {
                $baseName = basename($path);
                return $baseName !== VENDOR_DIR_NAME && $baseName !== LIB_DIR_NAME;
            }
            return preg_match('~/' . TEST_DIR_NAME . '/.+Test\.php$~si', $path);
        };
        return iterator_to_array(Directory::listFiles(MODULE_DIR_PATH, $filter, ['recursive' => true]), false);
    }
}
