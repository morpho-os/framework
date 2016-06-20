<?php
namespace MorphoTest\Fs;

use Morpho\Fs\Directory;
use Morpho\Test\TestCase;

class DirectoryTest extends TestCase {
    public function testDeleteEmptyDirs() {
        $this->markTestIncomplete();
    }
    
    public function testIsEmptyDir() {
        $this->assertFalse(Directory::isEmpty($this->getTestDirPath()));
        $this->assertTrue(Directory::isEmpty($this->createTmpDir()));
    }

    public function testMove_WhenTargetNotExist() {
        $sourceDirPath = $this->createTmpDir('source');
        mkdir($sourceDirPath . '/bar');
        touch($sourceDirPath . '/bar/1.txt');
        $this->assertTrue(is_dir($sourceDirPath . '/bar'));
        $this->assertTrue(is_file($sourceDirPath . '/bar/1.txt'));

        $targetDirPath = $this->createTmpDir('some') . '/target';

        $this->assertFalse(is_dir($targetDirPath . '/bar'));
        $this->assertFalse(is_dir($targetDirPath . '/bar'));
        $this->assertFalse(is_file($targetDirPath . '/bar/1.txt'));

        Directory::move($sourceDirPath, $targetDirPath);

        $this->assertFalse(is_dir($sourceDirPath . '/bar'));
        $this->assertFalse(is_file($sourceDirPath . '/bar/1.txt'));
        $this->assertTrue(is_dir($targetDirPath));
        $this->assertTrue(is_dir($targetDirPath . '/bar'));
        $this->assertTrue(is_file($targetDirPath . '/bar/1.txt'));
    }

    public function testTmpDirPath() {
        $tmpDirPath = Directory::tmpDirPath();
        $this->assertNotEmpty($tmpDirPath && (false === strpos($tmpDirPath, '\\')));
    }
    
    public function testDelete() {
        $this->markTestIncomplete();
    }

    public function testCreate_CantCreateEmptyDir() {
        $this->setExpectedException('\Morpho\Fs\Exception', "The directory path is empty.");
        Directory::create('');
    }

    public function testCreate_DoesNotCreateIfDirExists() {
        $this->assertEquals(__DIR__, Directory::create(__DIR__));
    }

    public function testUniquePath() {
        $this->assertEquals(__DIR__ . '/something', Directory::uniquePath(__DIR__ . '/something'));
        $this->assertEquals(__DIR__ . '-0', Directory::uniquePath(__DIR__));
    }

    public function testUniquePath_ThrowsExceptionWhenNumberOfAttemptsReached() {
        $dirPath = __DIR__;
        $expectedMessage = "Unable to generate an unique path for the directory '$dirPath' (tried 0 times).";
        $this->setExpectedException('\\Morpho\\Fs\\Exception', $expectedMessage);
        Directory::uniquePath($dirPath, 0);
    }

    public function testPaths_WithoutProcessorAndWithDefaultOptions() {
        $testDirPath = $this->getTestDirPath();
        $expected = [
            $testDirPath . '/1.txt',
            $testDirPath . '/2',
            $testDirPath . '/2/3.php',
            $testDirPath . '/4',
            $testDirPath . '/4/5',
            $testDirPath . '/4/5/6.php',
        ];
        $actual = iterator_to_array(Directory::paths($testDirPath), false);
        sort($expected);
        sort($actual);
        $this->assertEquals($expected, $actual);

        $actual = iterator_to_array(Directory::paths($testDirPath), false);
        sort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function testPaths_WithoutProcessorAndWithDirOption() {
        $testDirPath = $this->getTestDirPath();
        $expected = [
            $testDirPath . '/2',
            $testDirPath . '/4',
            $testDirPath . '/4/5',
        ];
        $actual = iterator_to_array(Directory::paths($testDirPath, null, ['type' => Directory::DIR]), false);
        sort($expected);
        sort($actual);
        $this->assertEquals($expected, $actual);

        $actual = iterator_to_array(Directory::dirPaths($testDirPath), false);
        sort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function testPaths_WithoutProcessorAndWithDirOrFileOption() {
        $testDirPath = $this->getTestDirPath();
        $expected = [
            $testDirPath . '/1.txt',
            $testDirPath . '/2',
            $testDirPath . '/2/3.php',
            $testDirPath . '/4',
            $testDirPath . '/4/5',
            $testDirPath . '/4/5/6.php',
        ];
        $actual = iterator_to_array(Directory::paths($testDirPath, null, ['type' => Directory::DIR | Directory::FILE]), false);
        sort($expected);
        sort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function testPaths_WithoutProcessorAndWithoutBothFileAndDirOptions() {
        $this->assertEquals([], iterator_to_array(Directory::paths($this->getTestDirPath(), null, ['type' => 0]), false));
    }

    public function testPaths_WithClosureProcessorAndWithDefaultOptions() {
        $testDirPath = $this->getTestDirPath();
        $expected = [
            $testDirPath . '/2',
            $testDirPath . '/2/3.php',
            $testDirPath . '/4',
            $testDirPath . '/4/5',
            $testDirPath . '/4/5/6.php',
        ];
        $actual = iterator_to_array(
            Directory::paths(
                $testDirPath,
                function ($path, $isDir) {
                    return $isDir || basename($path) != '1.txt';
                }
            ),
            false
        );
        sort($expected);
        sort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function testPaths_WithRegExpProcessorAndWithDefaultOptions() {
        $testDirPath = $this->getTestDirPath();
        $expected = [
            $testDirPath . '/2',
            $testDirPath . '/2/3.php',
            $testDirPath . '/4',
            $testDirPath . '/4/5',
            $testDirPath . '/4/5/6.php',
        ];
        $actual = iterator_to_array(Directory::paths($testDirPath, '~\.php$~si'), false);
        sort($expected);
        sort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function testPaths_WithRegExpProcessorAndWithDirOption() {
        $testDirPath = $this->getTestDirPath();
        $expected = [
            $testDirPath . '/2',
            $testDirPath . '/4',
            $testDirPath . '/4/5',
        ];
        $actual = iterator_to_array(Directory::paths($testDirPath, '~\.php$~si', ['type' => Directory::DIR]), false);
        sort($expected);
        sort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function testPaths_WithRegExpProcessorAndWithBothFileAndDirOptions() {
        $testDirPath = $this->getTestDirPath();
        $expected = [
            $testDirPath . '/2',
            $testDirPath . '/2/3.php',
            $testDirPath . '/4',
            $testDirPath . '/4/5',
            $testDirPath . '/4/5/6.php',
        ];
        $actual = iterator_to_array(Directory::paths($testDirPath, '~\.php$~si', ['type' => Directory::DIR | Directory::FILE]), false);
        sort($expected);
        sort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function testPaths_WithRegExpProcessorThatDoesNotMatchAnyPathAndWithFileOption() {
        $this->assertEquals(
            [],
            iterator_to_array(
                Directory::paths(
                    $this->getTestDirPath(),
                    '~\.some$~si',
                    ['type' => Directory::FILE]
                ),
                false
            )
        );
    }

    public function testPaths_WithRegExpProcessorAndWithBothBothFileAndDirOptionsWithoutRecursiveOption() {
        $testDirPath = $this->getTestDirPath();
        $expected = [
            $testDirPath . '/1.txt',
            $testDirPath . '/2',
            $testDirPath . '/4',
        ];
        $actual = Directory::paths(
            $testDirPath,
            '~\.txt$~si',
            [
                'type'      => Directory::DIR | Directory::FILE,
                'recursive' => false,
            ]
        );
        $actual = iterator_to_array($actual, false);
        sort($expected);
        sort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function testPaths_ThrowsExceptionOnInvalidOption() {
        $this->setExpectedException('\RuntimeException', 'Not allowed items are present');
        iterator_to_array(Directory::paths($this->getTestDirPath(), null, ['invalid' => 'foo']), false);
    }

    public function testPaths_WithNotRecursiveOption() {
        $testDirPath = $this->getTestDirPath();
        $actual = iterator_to_array(Directory::paths($testDirPath, null, ['recursive' => false]), false);
        $expected = [
            $testDirPath . '/1.txt',
            $testDirPath . '/2',
            $testDirPath . '/4',
        ];
        sort($actual);
        sort($expected);
        $this->assertEquals($expected, $actual);
    }

    public function testDirPaths_WithRegExpAndWithNotRecursiveOption() {
        $testDirPath = $this->getTestDirPath();
        $actual = iterator_to_array(Directory::dirPaths($testDirPath, "~.*/[^4]$~si", ['recursive' => false]), false);
        $expected = [
            $testDirPath . '/2',
        ];
        sort($actual);
        sort($expected);
        $this->assertEquals($expected, $actual);
    }

    public function testCopy_WhenTargetDirExists() {
        $sourceDirPath = $this->createTmpDir();
        touch($sourceDirPath . '/file1.txt');
        mkdir($sourceDirPath . '/dir1');
        touch($sourceDirPath . '/dir1/file2.txt');

        $targetDirPath = $this->createTmpDir();
        $this->assertNotEquals($sourceDirPath, $targetDirPath);
        touch($targetDirPath . '/file1.txt');
        mkdir($targetDirPath . '/dir1');
        touch($targetDirPath . '/dir1/file2.txt');

        Directory::copy($sourceDirPath, $targetDirPath);

        $this->assertDirContentsEqual($sourceDirPath, $targetDirPath . '/' . basename($sourceDirPath));
    }

    public function testCopy_WhenTargetDirNotExists() {
        $sourceDirPath = $this->createTmpDir();
        touch($sourceDirPath . '/file1.txt');
        mkdir($sourceDirPath . '/dir1');
        touch($sourceDirPath . '/dir1/file2.txt');

        $targetDirPath = $this->createTmpDir() . '/target';
        $this->assertFalse(is_dir($targetDirPath));

        Directory::copy($sourceDirPath, $targetDirPath);

        $this->assertDirContentsEqual($sourceDirPath, $targetDirPath);
    }

    public function testCopy_WhenTargetPathEqualsSourcePathsThrowsException() {
        $sourceDirPath = $this->createTmpDir();
        touch($sourceDirPath . '/file1.txt');
        mkdir($sourceDirPath . '/dir1');
        touch($sourceDirPath . '/dir1/file2.txt');

        $targetDirPath = $sourceDirPath;

        $this->setExpectedException('\Morpho\Fs\Exception', "Cannot copy a directory '$sourceDirPath' into itself.");
        Directory::copy($sourceDirPath, $targetDirPath);
    }

    protected function assertDirContentsEqual($sourceDirPath, $targetDirPath) {
        $expected = iterator_to_array(Directory::paths($sourceDirPath), false);
        $actual = iterator_to_array(Directory::paths($targetDirPath), false);

        $sourceDirPath = str_replace('\\', '/', $sourceDirPath);
        $targetDirPath = str_replace('\\', '/', $targetDirPath);

        $this->assertTrue(count($actual) > 0);
        $this->assertEquals(count($expected), count($actual));

        sort($expected);
        sort($actual);
        foreach ($expected as &$filePath) {
            $filePath = preg_replace('{^' . preg_quote($sourceDirPath) . '}si', '', $filePath);
        }
        unset($filePath);

        foreach ($actual as &$filePath) {
            $filePath = preg_replace('{^' . preg_quote($targetDirPath) . '}si', '', $filePath);
        }
        unset($filePath);

        $this->assertEquals($expected, $actual);
    }
}
