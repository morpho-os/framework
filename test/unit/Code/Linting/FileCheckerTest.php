<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Qa\Test\Unit\Code\Linting;

use Morpho\Code\Linting\FileChecker;
use Morpho\Code\Linting\SourceFile;
use Morpho\Test\TestCase;
use Morpho\Test\Vfs;

class FileCheckerTest extends TestCase {
    /**
     * @var FileChecker
     */
    private $checker;

    public function setUp() {
        parent::setUp();
        Vfs::register();
        $this->checker = new FileChecker();
    }

    public function tearDown() {
        parent::tearDown();
        Vfs::unregister();
    }

    public function testCheckMetaFile_MetaFileNotExists() {
        $moduleDirPath = $this->getTestDirPath() . '/non-existing';
        $sourceFile = new SourceFile($moduleDirPath . '/Foo/Bar.php');
        $sourceFile->setModuleDirPath($moduleDirPath);
        $result = $this->checker->checkMetaFile($sourceFile);
        $this->assertSame([FileChecker::META_FILE_NOT_FOUND], $result);
    }

    public function testCheckMetaFile_InvalidMetaFileFormat_NonJson() {
        $moduleDirUri = Vfs::prefixUri($this->getTestDirPath());
        $classFileUri = $moduleDirUri . '/Foo/Bar.php';
        $parentDirUri = Vfs::parentDirUri($classFileUri);
        mkdir($parentDirUri, 0755, true);
        file_put_contents($moduleDirUri . '/composer.json', 'test');
        file_put_contents($classFileUri, '<?php ');
        $sourceFile = new SourceFile($classFileUri);
        $sourceFile->setModuleDirPath($moduleDirUri);
        $result = $this->checker->checkMetaFile($sourceFile);
        $this->assertSame([FileChecker::INVALID_META_FILE_FORMAT], $result);
    }

    public function testCheckMetFile_InvalidMetaFileFormat_InvalidJson() {
        $this->markTestIncomplete();
    }
}