<?php declare(strict_types=1);
namespace MorphoTest\Fs;

use Morpho\Fs\Exception as FsException;
use Morpho\Fs\File;
use Morpho\Fs\FileNotFoundException;
use Morpho\Test\TestCase;

class FileTest extends TestCase {
    public function testMustExist_EmptyFilePath() {
        $this->expectException(FsException::class, "The file path is empty");
        File::mustExist('');
    }

    public function testMustExist_NonExistentFile() {
        $this->expectException(FsException::class, "The file does not exist");
        File::mustExist(__FILE__ . '123');
    }

    public function testIsEmpty() {
        $tmpFilePath = $this->createTmpDir() . '/123';
        touch($tmpFilePath);
        $this->assertTrue(File::isEmpty($tmpFilePath));
        file_put_contents($tmpFilePath, 'ok');
        $this->assertFalse(File::isEmpty($tmpFilePath));
        file_put_contents($tmpFilePath, '');
        $this->assertTrue(File::isEmpty($tmpFilePath));
    }

    public function testReadJson() {
        $tmpDirPath = $this->createTmpDir();
        $targetFilePath = $this->copyFile($this->getTestDirPath() . '/composer.json', $tmpDirPath);

        $this->assertEquals([
            'require'     => [
                'php'      => '7.0.*',
                'ext-curl' => '*',
            ],
            'require-dev' => [
                'phpunit/phpunit' => 'dev-master',
            ],
        ], File::readJson($targetFilePath));
    }

    public function testWriteJson() {
        $targetFilePath = $this->createTmpDir() . '/composer.json';
        $this->assertTrue(!is_file($targetFilePath));
        $dataToWrite = ['ping' => 'pong'];
        $this->assertEquals($targetFilePath, File::writeJson($targetFilePath, $dataToWrite));
        $this->assertEquals($dataToWrite, File::readJson($targetFilePath));
    }

    public function testDelete() {
        $targetFilePath = $this->tmpDirPath() . '/' . basename(__FILE__);
        File::copy(__FILE__, $targetFilePath);
        $this->assertFileExists($targetFilePath);

        File::delete($targetFilePath);

        $this->assertFileNotExists($targetFilePath);
    }

    public function testDeleteNonExistentFileThrowsException() {
        $this->expectException(FileNotFoundException::class);
        File::delete($this->tmpDirPath() . '/' . md5(uniqid()) . '.php');
    }

    public function testTruncate() {
        $filePath = $this->createTmpDir() . '/' . basename(md5(__METHOD__));
        $this->assertFileNotExists($filePath);
        $someString = '123';
        file_put_contents($filePath, $someString);
        $this->assertEquals($someString, file_get_contents($filePath));
        File::truncate($filePath);
        $this->assertEquals('', file_get_contents($filePath));
        $this->assertEquals(0, filesize($filePath));
    }

    public function testMove_ToNotExistentDirAndFile() {
        $sourceFilePath = $this->createTmpDir() . '/' . basename(md5(__METHOD__));
        $this->assertFileNotExists($sourceFilePath);
        copy(__FILE__, $sourceFilePath);
        $this->assertFileExists($sourceFilePath);
        $targetFilePath = $this->createTmpDir() . '/some/new/name.php';
        $this->assertFileNotExists($targetFilePath);

        $this->assertEquals($targetFilePath, File::move($sourceFilePath, $targetFilePath));

        $this->assertFileExists($targetFilePath);
        $this->assertEquals(filesize(__FILE__), filesize($targetFilePath));
    }

    public function testMove_NotExistentSourceFileThrowsException() {
        $sourceFilePath = __FILE__ . 'some';
        $targetFilePath = $this->tmpDirPath() . '/some';
        $this->expectException(FsException::class, "Unable to move the '$sourceFilePath' to the '$targetFilePath'.");
        File::move($sourceFilePath, $targetFilePath);
    }

    public function testMove_ToExistentDirWithTheSameNameAndWithoutFileName() {
        $this->markTestIncomplete();
    }

    public function testMove_ToExistentDirWithDifferentNameAndWithoutFileName() {
        $this->markTestIncomplete();
    }

    public function testCopy() {
        $tmpDirPath = $this->createTmpDir();
        $outFilePath = $tmpDirPath . '/foo/bar/baz/' . basename(__FILE__);
        $this->assertFileNotExists($outFilePath);

        $this->assertEquals($outFilePath, File::copy(__FILE__, $outFilePath));

        $this->assertFileExists($outFilePath);
        $this->assertEquals(filesize(__FILE__), filesize($outFilePath));
    }

    public function testCopy_IfSourceIsDirThrowsException() {
        $sourceFilePath = $this->getTestDirPath();
        $this->expectException(FsException::class, "Unable to copy: the source '$sourceFilePath' is not a file");
        File::copy($sourceFilePath, $this->tmpDirPath());
    }

    public function testWrite() {
        $tmpDirPath = $this->createTmpDir();
        $filePath = $tmpDirPath . '/foo.txt';
        $this->assertFalse(is_file($filePath));
        $this->assertEquals($filePath, File::write($filePath, 'test'));
        $this->assertTrue(is_file($filePath));
        $this->assertEquals('test', file_get_contents($filePath));
    }

    public function testWrite_CantWriteToEmptyFile() {
        $this->expectException(FsException::class, "The file path is empty.");
        File::write('', 'Test');
    }

    public function testWrite_EmptyString() {
        $tmpDirPath = $this->createTmpDir();
        $filePath = $tmpDirPath . '/' . __FUNCTION__ . '.txt';
        $this->assertEquals($filePath, File::write($filePath, ''));
    }

    public function testCopyWithoutOverwrite() {
        $tmpDirPath = $this->createTmpDir('foo/bar/baz');
        $sourceFilePath = __FILE__;
        $targetFilePath = $tmpDirPath . '/' . basename($sourceFilePath);
        $this->assertFalse(is_file($targetFilePath));
        touch($targetFilePath);
        $this->assertTrue(is_file($targetFilePath));

        try {
            File::copy($sourceFilePath, $targetFilePath, false);
            $this->fail();
        } catch (FsException $e) {
        }

        $this->assertEquals(0, filesize($targetFilePath));
    }

    public function testCopyWithoutOverwriteAndWithSkipIfExists() {
        $tmpDirPath = $this->createTmpDir('foo/bar/baz');
        $sourceFilePath = __FILE__;
        $targetFilePath = $tmpDirPath . '/' . basename($sourceFilePath);
        $this->assertFalse(is_file($targetFilePath));
        touch($targetFilePath);
        $this->assertTrue(is_file($targetFilePath));

        $this->assertEquals($targetFilePath, File::copy($sourceFilePath, $targetFilePath, false, true));
        $this->assertEquals(0, filesize($targetFilePath));
    }

    public function testCopyWithOverwrite() {
        $tmpDirPath = $this->createTmpDir('foo/bar/baz');
        $outFilePath = $tmpDirPath . '/' . basename(__FILE__);
        $this->assertFalse(is_file($outFilePath));
        touch($outFilePath);
        $this->assertTrue(is_file($outFilePath));

        $this->assertEquals($outFilePath, File::copy(__FILE__, $outFilePath, true));

        $this->assertTrue(is_file($outFilePath));
        $this->assertEquals(filesize(__FILE__), filesize($outFilePath));
    }

    public function testCopyToDirWithoutFileName() {
        $tmpDir = $this->createTmpDir();
        $sourceFilePath = __FILE__;
        $copiedFilePath = $tmpDir . '/' . basename($sourceFilePath);
        $this->assertFalse(file_exists($copiedFilePath));
        File::copy($sourceFilePath, $tmpDir);
        $this->assertTrue(file_exists($copiedFilePath));
    }

    public function testUniquePath() {
        $this->assertEquals(__DIR__ . '/something', File::uniquePath(__DIR__ . '/something'));
        $this->assertEquals(__FILE__ . '-0', File::uniquePath(__FILE__));
    }

    public function testUniquePathShouldThrowExceptionWhenNumberOfAttempsReached() {
        $filePath = __FILE__;
        $expectedMessage = "Unable to generate unique path for file '$filePath' (tried 0 times).";
        $this->expectException(FsException::class, $expectedMessage);
        File::uniquePath($filePath, 0);
    }

    public function testReadTextFileWithBom() {
        $options = [
            'binary' => false,
        ];
        $this->assertEquals("123", File::read($this->getTestDirPath() . '/bom.txt', $options));
    }

    public function testReadBinary() {
        $content = File::read($this->getTestDirPath() . '/binary.jpg');
        $this->assertEquals("\xff\xd8\xff\xe0\x00\x10\x4a\x46\x49\x46\x00\x01\x01\x00\x00\x01", substr($content, 0, 16));
    }

    public function testReadFileAsArray_NonExistingFile() {
        // @TODO: Write tests for other read*() methods for non-existence also.
        $this->markTestIncomplete();
    }

    public function testWithFile_DefaultTmpDir() {
        $this->assertSame('ok', File::withTmp(function ($filePath) use (&$usedFilePath) {
            $this->assertSame(0, filesize($filePath));
            $usedFilePath = $filePath;
            return 'ok';
        }));
        $this->assertNotEmpty($usedFilePath);
        $this->assertFileNotExists($usedFilePath);
    }

    public function testWithTmp_NonDefaultTmpDir() {
        $tmpDirPath = $this->createTmpDir(__FUNCTION__);
        $this->assertSame('ok', File::withTmp(function ($filePath) use (&$usedFilePath) {
            $this->assertSame(0, filesize($filePath));
            $usedFilePath = $filePath;
            return 'ok';
        }, $tmpDirPath));
        $this->assertContains(__FUNCTION__, $usedFilePath);
        $this->assertFileNotExists($usedFilePath);
    }

    public function testWriteLines_Generator() {
        $tmpFilePath = $this->createTmpFile();
        $gen = function () {
            yield 'First';
            yield 'Second';
            yield 'Third';
        };
        File::writeLines($tmpFilePath, $gen());
        $this->assertEquals(['First', 'Second', 'Third'], file($tmpFilePath, FILE_IGNORE_NEW_LINES));
    }

    public function testWriteLines_Array() {
        $tmpFilePath = $this->createTmpFile();
        $lines = [
            'First',
            'Second',
            'Third',
        ];
        File::writeLines($tmpFilePath, $lines);
        $this->assertEquals($lines, file($tmpFilePath, FILE_IGNORE_NEW_LINES));
    }

    public function testWriteLines_Iterator() {
        $tmpFilePath = $this->createTmpFile();
        $lines = [
            'First',
            'Second',
            'Third',
        ];
        File::writeLines($tmpFilePath, new \ArrayIterator($lines));
        $this->assertEquals($lines, file($tmpFilePath, FILE_IGNORE_NEW_LINES));
    }
}