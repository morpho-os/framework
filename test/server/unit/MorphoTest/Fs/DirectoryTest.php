<?php
namespace MorphoTest\Fs;

use LogicException;
use Morpho\Base\InvalidOptionsException;
use Morpho\Fs\Directory;
use Morpho\Test\TestCase;

class DirectoryTest extends TestCase {
    public function testDelete_EmptyDir() {
        $this->markTestIncomplete();

        // @TODO: Test both: iterable and string first argument
    }

    public function testDelete_NotEmptyDir() {
        $this->markTestIncomplete();

        // @TODO: Test both: iterable and string first argument
    }

    public function testMustExist_ReturnsArg() {
        $dirPath = __DIR__;
        $this->assertEquals($dirPath, Directory::mustExist($dirPath));
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

        $this->assertEquals($targetDirPath, Directory::move($sourceDirPath, $targetDirPath));

        $this->assertFalse(is_dir($sourceDirPath . '/bar'));
        $this->assertFalse(is_file($sourceDirPath . '/bar/1.txt'));
        $this->assertTrue(is_dir($targetDirPath));
        $this->assertTrue(is_dir($targetDirPath . '/bar'));
        $this->assertTrue(is_file($targetDirPath . '/bar/1.txt'));
    }

    public function testTmpPath() {
        $tmpDirPath = Directory::tmpPath();
        $this->assertNotEmpty($tmpDirPath && (false === strpos($tmpDirPath, '\\')));
    }

    public function testCreate_CantCreateEmptyDir() {
        $this->expectException('\Morpho\Fs\Exception', "The directory path is empty.");
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
        $this->expectException('\\Morpho\\Fs\\Exception', $expectedMessage);
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
        $actual = iterator_to_array(Directory::paths($testDirPath, null, ['recursive' => true]), false);
        sort($expected);
        sort($actual);
        $this->assertEquals($expected, $actual);

        $actual = iterator_to_array(Directory::paths($testDirPath, null, ['recursive' => true]), false);
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
        $it = Directory::paths($testDirPath, null, ['type' => Directory::DIR, 'recursive' => true]);
        $actual = iterator_to_array($it, false);
        sort($expected);
        sort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function testPaths_WithoutProcessor_WithDirOrFileOption() {
        $testDirPath = $this->getTestDirPath();
        $expected = [
            $testDirPath . '/1.txt',
            $testDirPath . '/2',
            $testDirPath . '/2/3.php',
            $testDirPath . '/4',
            $testDirPath . '/4/5',
            $testDirPath . '/4/5/6.php',
        ];
        $actual = iterator_to_array(Directory::paths($testDirPath, null, ['type' => Directory::DIR | Directory::FILE, 'recursive' => true]), false);
        sort($expected);
        sort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function testPaths_WithoutProcessorAndWithoutBothFileAndDirOptions() {
        $it = Directory::paths($this->getTestDirPath(), null, ['type' => 0, 'recursive' => true]);
        $this->assertEquals([], iterator_to_array($it, false));
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
                },
                ['recursive' => true]
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
        $it = Directory::paths($testDirPath, '~\.php$~si', ['recursive' => true]);
        $actual = iterator_to_array($it, false);
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
        $it = Directory::paths($testDirPath, '~\.php$~si', ['type' => Directory::DIR, 'recursive' => true]);
        $actual = iterator_to_array($it, false);
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
        $it = Directory::paths($testDirPath, '~\.php$~si', ['type' => Directory::DIR | Directory::FILE, 'recursive' => true]);
        $actual = iterator_to_array($it, false);
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
                    ['type' => Directory::FILE, 'recursive' => true]
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
        $this->expectException(InvalidOptionsException::class, 'Invalid options: invalid');
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

    public function testPaths_SavesModifiedPathFromProcessorButUsesNotModifiedPathInTraversing() {
        $testDirPath = $this->getTestDirPath();
        $processor = function (&$path) {
            static $i;
            $path = $path . 'foo' . ++$i;
            return true;
        };
        $paths = iterator_to_array(Directory::paths($testDirPath, $processor, ['recursive' => true]), false);
        sort($paths);
        $expected = [
            $testDirPath . '/1.txt',
            $testDirPath . '/2',
            $testDirPath . '/2/3.php',
            $testDirPath . '/4',
            $testDirPath . '/4/5',
            $testDirPath . '/4/5/6.php',
        ];
        $this->assertCount(count($expected), $paths);
        foreach ($expected as $path) {
            $this->assertCount(1, preg_grep('~^' . preg_quote($path, '~') . 'foo[1-6]$~si', $paths));
        }
    }

    public function testPaths_YieldsReturnedPathsFromProcessor() {
        $testDirPath = $this->getTestDirPath();
        $processor = function ($path) {
            return basename($path);
        };
        $paths = iterator_to_array(Directory::paths($testDirPath, $processor, ['recursive' => true]), false);
        sort($paths);
        $expected = [
            '1.txt',
            '2',
            '3.php',
            '4',
            '5',
            '6.php',
        ];
        $this->assertEquals($expected, $paths);
    }

    public function testCopy_IntoItself_TargetPathEqualsSourcePath_ThrowsException() {
        $sourceDirPath = $this->createTmpDir() . '/foo';
        mkdir($sourceDirPath);
        $targetDirPath = $sourceDirPath;
        $this->expectException(\Morpho\Fs\Exception::class, "Cannot copy the directory '$sourceDirPath' into itself");
        Directory::copy($sourceDirPath, $targetDirPath);
    }

    public function testCopy_IntoItself_TargetDirContainsTheSameDirName_ThrowsException() {
        $tmpDirPath = $this->createTmpDir();
        $sourceDirPath = $tmpDirPath . '/foo';
        mkdir($sourceDirPath);
        $targetDirPath = $tmpDirPath;
        $this->expectException(\Morpho\Fs\Exception::class, "The '$tmpDirPath' directory already contains the 'foo'");
        Directory::copy($sourceDirPath, $targetDirPath);
    }

    public function testCopy_TargetDirContainsTheSameSubdir() {
        $sourceDirPath = $this->createTmpDir();
        mkdir($sourceDirPath . '/test1/foo', Directory::MODE, true);

        $targetDirPath = $this->createTmpDir();
        mkdir($targetDirPath . '/test1/foo', Directory::MODE, true);

        $sourceDirPath = $sourceDirPath . '/test1';

        $this->assertEquals(
            $targetDirPath . '/' . basename($sourceDirPath),
            Directory::copy($sourceDirPath, $targetDirPath)
        );

        $this->assertEquals(['test1', 'test1/foo'], $this->pathsInDir($targetDirPath));
    }

    public function testCopy_TargetDirNotExist_TargetDirNameNotEqualSourceDirName() {
        $sourceDirPath = $this->createTmpDir() . '/foo';
        mkdir($sourceDirPath);
        $targetDirPath = $this->createTmpDir() . '/bar';

        $this->assertEquals(
            $targetDirPath,
            Directory::copy($sourceDirPath, $targetDirPath)
        );

        $this->assertEquals([], $this->pathsInDir($targetDirPath));
    }

    public function testCopy_TargetDirNotExist_TargetDirNameEqualsSourceDirName() {
        $sourceDirPath = $this->createTmpDir() . '/foo';
        mkdir($sourceDirPath);
        $targetDirPath = $this->createTmpDir() . '/bar/foo';

        $this->assertEquals(
            $targetDirPath,
            Directory::copy($sourceDirPath, $targetDirPath)
        );

        $this->assertEquals([], $this->pathsInDir($targetDirPath));
    }

    public function testCopy_TargetDirExists_TargetDirNameNotEqualsSourceDirName() {
        $sourceDirPath = $this->createTmpDir() . '/foo';
        mkdir($sourceDirPath);
        $targetDirPath = $this->createTmpDir() . '/bar';
        mkdir($targetDirPath);

        $this->assertEquals(
            $targetDirPath . '/' . basename($sourceDirPath),
            Directory::copy($sourceDirPath, $targetDirPath)
        );

        $this->assertEquals(['foo'], $this->pathsInDir($targetDirPath));
    }

    public function testCopy_TargetDirExists_TargetDirNameEqualsSourceDirName() {
        $sourceDirPath = $this->createTmpDir() . '/foo';
        mkdir($sourceDirPath);
        $targetDirPath = $this->createTmpDir() . '/bar';
        mkdir($targetDirPath . '/foo', Directory::MODE, true);

        $this->assertEquals(
            $targetDirPath . '/' . basename($sourceDirPath),
            Directory::copy($sourceDirPath, $targetDirPath)
        );

        $this->assertEquals(['foo'], $this->pathsInDir($targetDirPath));
    }

    public function testCopy_TargetDirExists_NestedDirExists() {
        $sourceDirPath = $this->createTmpDir();
        mkdir($sourceDirPath . '/public/module/system', Directory::MODE, true);
        touch($sourceDirPath . '/public/module/system/composer.json');

        $targetDirPath = $this->createTmpDir();
        mkdir($targetDirPath . '/public/module/bootstrap', Directory::MODE, true);

        $sourceDirPath = $sourceDirPath . '/public';

        $this->assertEquals(
            $targetDirPath . '/' . basename($sourceDirPath),
            Directory::copy($sourceDirPath, $targetDirPath)
        );

        $paths = iterator_to_array(Directory::paths($targetDirPath, null, ['recursive' => true]), false);
        sort($paths);
        $this->assertEquals(
            [
                $targetDirPath . '/public',
                $targetDirPath . '/public/module',
                $targetDirPath . '/public/module/bootstrap',
                $targetDirPath . '/public/module/system',
                $targetDirPath . '/public/module/system/composer.json',
            ],
            $paths
        );
    }

    public function testCopy_WithFiles_TargetDirExists() {
        $sourceDirPath = $this->createTmpDir();
        touch($sourceDirPath . '/file1.txt');
        mkdir($sourceDirPath . '/dir1');
        touch($sourceDirPath . '/dir1/file2.txt');

        $targetDirPath = $this->createTmpDir();
        $this->assertNotEquals($sourceDirPath, $targetDirPath);
        touch($targetDirPath . '/file1.txt');
        mkdir($targetDirPath . '/dir1');
        touch($targetDirPath . '/dir1/file2.txt');

        $this->assertEquals(
            $targetDirPath . '/' . basename($sourceDirPath),
            Directory::copy($sourceDirPath, $targetDirPath)
        );

        $this->assertDirContentsEqual($sourceDirPath, $targetDirPath . '/' . basename($sourceDirPath));
    }

    public function testCopy_WithFiles_TargetDirNotExists() {
        $sourceDirPath = $this->createTmpDir();
        touch($sourceDirPath . '/file1.txt');
        mkdir($sourceDirPath . '/dir1');
        touch($sourceDirPath . '/dir1/file2.txt');

        $targetDirPath = $this->createTmpDir() . '/target';
        $this->assertFalse(is_dir($targetDirPath));

        $this->assertEquals(
            $targetDirPath,
            Directory::copy($sourceDirPath, $targetDirPath)
        );

        $this->assertDirContentsEqual($sourceDirPath, $targetDirPath);
    }

    public function testDirPaths_WithoutProcessor_Recursive() {
        $testDirPath = $this->getTestDirPath();
        $expected = [
            $testDirPath . '/2',
            $testDirPath . '/4',
            $testDirPath . '/4/5',
        ];
        $it = Directory::dirPaths($testDirPath, null, ['recursive' => true]);
        $actual = iterator_to_array($it, false);
        sort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function testDirPaths_RegExpProcessor_NotRecursive() {
        $testDirPath = $this->getTestDirPath();
        $it = Directory::dirPaths($testDirPath, "~.*/[^4]$~si", ['recursive' => false]);
        $actual = iterator_to_array($it, false);
        $expected = [
            $testDirPath . '/2',
        ];
        sort($actual);
        sort($expected);
        $this->assertEquals($expected, $actual);
    }

    public function testDirPaths_ClosureProcessor_NotRecursive() {
        $testDirPath = $this->getTestDirPath();
        $processor = function ($path) use (&$calledTimes) {
            $this->assertTrue(is_dir($path));
            $calledTimes++;
            return $path;
        };
        $it = Directory::dirPaths($testDirPath, $processor);
        $dirPaths = iterator_to_array($it, false);
        sort($dirPaths);
        $this->assertEquals(2, $calledTimes);
        $this->assertEquals([$testDirPath . '/2', $testDirPath . '/4'], $dirPaths);
    }

    public function testDirNames_WithoutProcessor_NotRecursive() {
        $testDirPath = $this->getTestDirPath();
        $it = Directory::dirNames($testDirPath);
        $dirNames = iterator_to_array($it, false);
        sort($dirNames);
        $this->assertEquals(['2', '4'], $dirNames);
    }

    public function testDirNames_WithoutProcessor_RecursiveLogicThrowsException() {
        $this->expectException(LogicException::class, "The 'recursive' option must be false");
        Directory::dirNames($this->getTestDirPath(), null, ['recursive' => true]);
    }

    public function testDirNames_RegExpProcessor() {
        $testDirPath = $this->getTestDirPath();
        $it = Directory::dirNames($testDirPath, '~^2$~si');
        $dirNames = iterator_to_array($it, false);
        $this->assertEquals(['2'], $dirNames);
    }

    public function testDirNames_ClosureProcessor() {
        $testDirPath = $this->getTestDirPath();
        $processor = function ($dirName, $path) use (&$calledTimes) {
            $this->assertRegExp('~^.*/.*/(2|4)$~', $path);
            $calledTimes++;
            $map = [
                '2' => 'foo',
                '4' => 'bar',
            ];
            return $map[$dirName];
        };
        $it = Directory::dirNames($testDirPath, $processor);
        $dirNames = iterator_to_array($it, false);
        sort($dirNames);
        $this->assertEquals(2, $calledTimes);
        $this->assertEquals(['bar', 'foo'], $dirNames);
    }
    
    public function testDirNames_ClosureProcessorWhichReturnsBool() {
        $testDirPath = $this->getTestDirPath();
        $processor = function ($dirName, $path) use (&$calledTimes) {
            $this->assertNotContains('/', $dirName);
            $this->assertRegExp('~^.*/.*/(2|4)$~', $path);
            $calledTimes++;
            return true;
        };
        $it = Directory::dirNames($testDirPath, $processor);
        $dirNames = iterator_to_array($it, false);
        sort($dirNames);
        $this->assertEquals(2, $calledTimes);
        $this->assertEquals(['2', '4'], $dirNames);
    }

    public function testFilePaths_RegExpProcessor_Recursive() {
        $testDirPath = $this->getTestDirPath();
        $it = Directory::filePaths($testDirPath, '~\.(txt|php)$~s', ['recursive' => true]);
        $filePaths = iterator_to_array($it, false);
        sort($filePaths);
        $this->assertEquals(
            [
                $testDirPath . '/1.txt',
                $testDirPath . '/2/3.php',
                $testDirPath . '/4/5/6.php',
            ],
            $filePaths
        );
    }

    public function testFilePaths_RegExpProcessor_NotRecursive() {
        $testDirPath = $this->getTestDirPath();
        $it = Directory::filePaths($testDirPath, '~\.(txt|php)$~s');
        $filePaths = iterator_to_array($it, false);
        sort($filePaths);
        $this->assertEquals(
            [
                $testDirPath . '/1.txt',
            ],
            $filePaths
        );
    }

    private function assertDirContentsEqual($dirPathExpectedContent, $dirPathActualContent) {
        $expected = $this->pathsInDir($dirPathExpectedContent);
        $actual = $this->pathsInDir($dirPathActualContent);
        $this->assertTrue(count($actual) > 0);
        $this->assertEquals($expected, $actual);
    }

    private function pathsInDir(string $dirPath): array {
        $paths = iterator_to_array(Directory::paths($dirPath, null, ['recursive' => true]), false);
        $dirPath = str_replace('\\', '/', $dirPath);
        sort($paths);
        foreach ($paths as &$filePath) {
            $filePath = preg_replace('{^' . preg_quote($dirPath) . '/}si', '', $filePath);
        }
        unset($filePath);
        return $paths;
    }
}
