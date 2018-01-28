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

        $errors = $this->checker->checkMetaFile($sourceFile);

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
        $classFileUri = $moduleDirUri . '/Foo/Bar.php';
        $sourceFile = $this->createTestSourceFile($moduleDirUri, $classFileUri, $metaFileContents, '<?php');

        $errors = $this->checker->checkMetaFile($sourceFile);

        $this->assertSame([FileChecker::INVALID_META_FILE_FORMAT], $errors);
    }
    
    public function testCheckMetaFile_ValidFormat() {
        $moduleDirUri = Vfs::prefixUri($this->getTestDirPath());
        $ns = 'Morpho\\';
        $libDirPath = 'lib/';
        $metaFileContents = <<<OUT
{
    "name": "foo/bar",
    "autoload": {
        "psr-4": {
            "$ns\\": "$libDirPath"
        }
    }
}
OUT;

        $classFileUri = $moduleDirUri . '/Foo/Bar.php';
        $sourceFile = $this->createTestSourceFile($moduleDirUri, $classFileUri, $metaFileContents, '<?php ');

        $errors = $this->checker->checkMetaFile($sourceFile);

        $this->assertSame([], $errors);
        $this->assertSame([$ns => $libDirPath], $sourceFile['nsToDirPathMap']);
    }

    public function testCheckNamespaces_NsNotFound() {
        $moduleDirUri = Vfs::prefixUri($this->getTestDirPath());
        $classFileUri = $moduleDirUri . '/test/bar';
        $sourceFile = $this->createTestSourceFile($moduleDirUri, $classFileUri, '', '<?php ');
        $sourceFile['nsToDirPathMap'] = [
            __NAMESPACE__ . '\\Foo\\Bar\\' => 'shelf/book/',
        ];

        $errors = $this->checker->checkNamespaces($sourceFile);

        $this->assertSame([FileChecker::NS_NOT_FOUND], $errors);
    }

    public function testCheckNamespaces_FileDoesNotMatchNs() {
        $moduleDirUri = Vfs::prefixUri($this->getTestDirPath());
        $libDirPath = 'shelf/book';
        $nsPrefix = __NAMESPACE__;
        $sourceFileContents = <<<OUT
<?php
namespace $nsPrefix\Some {}
OUT;

        $classFileUri = $moduleDirUri . '/' . $libDirPath . '/Red/Green/Test.php';

        $sourceFile = $this->createTestSourceFile($moduleDirUri, $classFileUri, '', $sourceFileContents);

        $sourceFile['nsToDirPathMap'] = [
            $nsPrefix . '\\' => $libDirPath . '/',
        ];

        $errors = $this->checker->checkNamespaces($sourceFile);

        $this->assertSame(['invalidNs' => $nsPrefix . '\\Some'], $errors);
    }

    public function testCheckNamespaces_MultipleValidNss() {
        $moduleDirUri = Vfs::prefixUri($this->getTestDirPath());
        $libDirPath = 'shelf/book';
        $nsPrefix = __NAMESPACE__;
        $sourceFileContents = <<<OUT
<?php
namespace $nsPrefix\Red\Green {}

namespace $nsPrefix\Red\Green\Blue {}
OUT;

        $classFileUri = $moduleDirUri . '/' . $libDirPath . '/Red/Green/Test.php';

        $sourceFile = $this->createTestSourceFile($moduleDirUri, $classFileUri, '', $sourceFileContents);

        $sourceFile['nsToDirPathMap'] = [
            $nsPrefix . '\\' => $libDirPath . '/',
        ];

        $errors = $this->checker->checkNamespaces($sourceFile);

        $this->assertSame([], $errors);
    }

    public function testCheckClassTypes_InvalidClass() {
        $moduleDirUri = Vfs::prefixUri($this->getTestDirPath());
        $libDirPath = 'shelf/book';
        $nsPrefix = __NAMESPACE__;
        $sourceFileContents = <<<OUT
<?php
namespace $nsPrefix\Red\Green;

class Blue {
}
OUT;

        $classFileUri = $moduleDirUri . '/' . $libDirPath . '/Red/Green/Test.php';

        $sourceFile = $this->createTestSourceFile($moduleDirUri, $classFileUri, '', $sourceFileContents);

        $sourceFile['nsToDirPathMap'] = [
            $nsPrefix . '\\' => $libDirPath . '/',
        ];

        $errors = $this->checker->checkClassTypes($sourceFile);

        $this->assertSame($nsPrefix . '\\Red\\Green\\Blue', $errors[FileChecker::INVALID_CLASS]);
    }

    public function testCheckClassTypes_ValidClass() {
        $moduleDirUri = Vfs::prefixUri($this->getTestDirPath());
        $libDirPath = 'shelf/book';
        $nsPrefix = __NAMESPACE__;
        $sourceFileContents = <<<OUT
<?php
namespace $nsPrefix\Red\Green;

class Test {
}
OUT;

        $classFileUri = $moduleDirUri . '/' . $libDirPath . '/Red/Green/Test.php';

        $sourceFile = $this->createTestSourceFile($moduleDirUri, $classFileUri, '', $sourceFileContents);

        $sourceFile['nsToDirPathMap'] = [
            $nsPrefix . '\\' => $libDirPath . '/',
        ];

        $errors = $this->checker->checkClassTypes($sourceFile);

        $this->assertSame([], $errors);
    }

    private function createTestSourceFile(string $moduleDirUri, string $classFileUri, string $metaFileContents, string $sourceFileContents): SourceFile {
        $parentDirUri = Vfs::parentDirUri($classFileUri);
        mkdir($parentDirUri, 0755, true);
        file_put_contents($moduleDirUri . '/composer.json', $metaFileContents);
        file_put_contents($classFileUri, $sourceFileContents);
        $sourceFile = new SourceFile($classFileUri);
        $sourceFile->setModuleDirPath($moduleDirUri);
        return $sourceFile;
    }
}