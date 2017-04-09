<?php
namespace MorphoTest;

use function Morpho\Base\filter;
use const Morpho\Core\LIB_DIR_NAME;
use const Morpho\Core\MODULE_DIR_PATH;
use const Morpho\Core\TEST_DIR_NAME;
use const Morpho\Core\VENDOR_DIR_NAME;
use Morpho\Fs\Directory;

class TestSuite extends \Morpho\Test\TestSuite {
    public function testFilePaths() {
        $filePaths = Directory::filePaths(__DIR__, $this->testFileRegexp, ['recursive' => true]);
        return array_merge(
            filter(
                function ($path) {
                    return $path !== str_replace('\\', '/', __FILE__);
                },
                iterator_to_array($filePaths, false)
            ),
            $this->testFilePathsOfModules()
        );
    }

    protected function testFilePathsOfModules() {
        $filter = function ($path, $isDir) {
            if ($isDir) {
                $baseName = basename($path);
                return $baseName !== VENDOR_DIR_NAME && $baseName !== LIB_DIR_NAME;
            }
            return (bool) preg_match('~/' . TEST_DIR_NAME . '/.+Test\.php$~s', $path);
        };
        return iterator_to_array(Directory::filePaths(MODULE_DIR_PATH, $filter, ['recursive' => true]), false);
    }
}
