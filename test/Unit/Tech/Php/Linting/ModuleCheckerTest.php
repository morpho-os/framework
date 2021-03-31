<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Tech\Php\Linting;

use Morpho\Tech\Php\Linting\FileChecker;
use Morpho\Tech\Php\Linting\ModuleChecker;
use Morpho\Testing\TestCase;
use Morpho\Testing\Vfs;

class ModuleCheckerTest extends TestCase {
    public function testCheckMetaFile_MetaFileNotExists() {
        $moduleDirPath = $this->getTestDirPath() . '/non-existing';

        $metaFilePath = $moduleDirPath . '/composer.json';
        $errors = ModuleChecker::checkMetaFile($metaFilePath);

        $this->assertSame([FileChecker::META_FILE_NOT_FOUND], $errors);
    }

    public function dataCheckMetaFile_InvalidMetaFileFormat() {
        yield ['test'];
        yield [\json_encode(['foo' => 'bar'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)];
    }

    /**
     * @dataProvider dataCheckMetaFile_InvalidMetaFileFormat
     */
    public function testCheckMetaFile_InvalidMetaFileFormat(string $metaFileContents) {
        $moduleDirUri = Vfs::prefixUri($this->getTestDirPath());

        $metaFileUri = $moduleDirUri . '/composer.json';
        $this->createTestMetaFile($metaFileUri, $metaFileContents);

        $errors = ModuleChecker::checkMetaFile($metaFileUri);

        $this->assertSame([FileChecker::INVALID_META_FILE_FORMAT], $errors);
    }
    
    public function testCheckMetaFile_ValidFormat() {
        $ns = 'Morpho\\';
        $relLibDirPath = 'lib/';
        $metaFileContents = <<<OUT
{
    "name": "foo/bar",
    "autoload": {
        "psr-4": {
            "$ns\\": "$relLibDirPath"
        }
    }
}
OUT;
        $moduleDirUri = Vfs::prefixUri($this->getTestDirPath());

        $metaFileUri = $moduleDirUri . '/composer.json';
        $this->createTestMetaFile($metaFileUri, $metaFileContents);

        $errors = ModuleChecker::checkMetaFile($metaFileUri);

        $this->assertSame([], $errors);
    }

    private function createTestMetaFile(string $metaFileUri, string $metaFileContents): void {
        \mkdir(Vfs::parentDirUri($metaFileUri), 0755, true);
        \file_put_contents($metaFileUri, $metaFileContents);
    }
}
