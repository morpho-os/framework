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
    public function setUp() {
        parent::setUp();
        Vfs::register();
    }

    public function tearDown() {
        parent::tearDown();
        Vfs::unregister();
    }

    public function testCheckMetaFile_MetaFileNotExists() {
        $moduleDirPath = $this->getTestDirPath() . '/non-existing';

        $metaFilePath = $moduleDirPath . '/composer.json';
        $errors = FileChecker::checkMetaFile($metaFilePath);

        $this->assertSame([FileChecker::META_FILE_NOT_FOUND], $errors);
    }

    public function dataForCheckMetaFile_InvalidMetaFileFormat() {
        yield ['test'];
        yield [json_encode(['foo' => 'bar'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)];
    }

    /**
     * @dataProvider dataForCheckMetaFile_InvalidMetaFileFormat
     */
    public function testCheckMetaFile_InvalidMetaFileFormat(string $metaFileContents) {
        $moduleDirUri = Vfs::prefixUri($this->getTestDirPath());

        $metaFileUri = $moduleDirUri . '/composer.json';
        $this->createTestMetaFile($metaFileUri, $metaFileContents);

        $errors = FileChecker::checkMetaFile($metaFileUri);

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

        $errors = FileChecker::checkMetaFile($metaFileUri);

        $this->assertSame([], $errors);
    }

    public function testCheckNamespaces_NsNotFound() {
        $moduleDirUri = Vfs::prefixUri($this->getTestDirPath());
        $classFileUri = $moduleDirUri . '/test/bar';
        $sourceFile = $this->createTestSourceFile($classFileUri, '<?php ');
        $sourceFile->setNsToLibDirPathMap([
            __NAMESPACE__ . '\\Foo\\Bar' => $moduleDirUri . '/shelf/book/',
        ]);

        $errors = FileChecker::checkNamespaces($sourceFile);

        $this->assertSame([FileChecker::NS_NOT_FOUND], $errors);
    }

    public function testCheckNamespaces_FileDoesNotMatchNs() {
        $moduleDirUri = Vfs::prefixUri($this->getTestDirPath());
        $libDirPath = $moduleDirUri . '/shelf/book';
        $nsPrefix = __NAMESPACE__;
        $sourceFileContents = <<<OUT
<?php
namespace $nsPrefix\Some {}
OUT;

        $classFileUri = $libDirPath . '/Red/Green/Test.php';

        $sourceFile = $this->createTestSourceFile($classFileUri, $sourceFileContents);

        $sourceFile->setNsToLibDirPathMap([
            $nsPrefix => $libDirPath,
        ]);

        $errors = FileChecker::checkNamespaces($sourceFile);

        $this->assertSame(['invalidNs' => $nsPrefix . '\\Some'], $errors);
    }

    public function testCheckNamespaces_MultipleValidNss() {
        $moduleDirUri = Vfs::prefixUri($this->getTestDirPath());
        $libDirPath = $moduleDirUri . '/shelf/book';
        $nsPrefix = __NAMESPACE__;
        $sourceFileContents = <<<OUT
<?php
namespace $nsPrefix\Red\Green {}

namespace $nsPrefix\Red\Green\Blue {}
OUT;

        $classFileUri = $libDirPath . '/Red/Green/Test.php';

        $sourceFile = $this->createTestSourceFile($classFileUri, $sourceFileContents);

        $sourceFile->setNsToLibDirPathMap([
            $nsPrefix . '\\' => $libDirPath,
        ]);

        $errors = FileChecker::checkNamespaces($sourceFile);

        $this->assertSame([], $errors);
    }

    public function testCheckClassTypes_InvalidClass() {
        $moduleDirUri = Vfs::prefixUri($this->getTestDirPath());
        $libDirPath = $moduleDirUri . '/shelf/book';
        $nsPrefix = __NAMESPACE__;
        $sourceFileContents = <<<OUT
<?php
namespace $nsPrefix\Red\Green;

class Blue {
}
OUT;

        $classFileUri = $libDirPath . '/Red/Green/Test.php';

        $sourceFile = $this->createTestSourceFile($classFileUri, $sourceFileContents);

        $sourceFile->setNsToLibDirPathMap([
            $nsPrefix . '\\' => $libDirPath,
        ]);

        $errors = FileChecker::checkClassTypes($sourceFile);

        $this->assertSame($nsPrefix . '\\Red\\Green\\Blue', $errors[FileChecker::INVALID_CLASS]);
    }

    public function testCheckClassTypes_ValidClass() {
        $moduleDirUri = Vfs::prefixUri($this->getTestDirPath());
        $libDirPath = $moduleDirUri . '/shelf/book';
        $nsPrefix = __NAMESPACE__;
        $sourceFileContents = <<<OUT
<?php
namespace $nsPrefix\Red\Green;

class Test {
}
OUT;

        $classFileUri = $libDirPath . '/Red/Green/Test.php';

        $sourceFile = $this->createTestSourceFile($classFileUri, $sourceFileContents);

        $sourceFile->setNsToLibDirPathMap([
            $nsPrefix . '\\' => $libDirPath,
        ]);

        $errors = FileChecker::checkClassTypes($sourceFile);

        $this->assertSame([], $errors);
    }
    
    private function createTestSourceFile(string $classFileUri, string $sourceFileContents): SourceFile {
        mkdir(Vfs::parentDirUri($classFileUri), 0755, true);
        file_put_contents($classFileUri, $sourceFileContents);
        $sourceFile = new SourceFile($classFileUri);
        return $sourceFile;
    }

    private function createTestMetaFile(string $metaFileUri, string $metaFileContents): void {
        mkdir(Vfs::parentDirUri($metaFileUri), 0755, true);
        file_put_contents($metaFileUri, $metaFileContents);
    }
}