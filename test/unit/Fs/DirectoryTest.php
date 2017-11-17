<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Fs;

use LogicException;
use Morpho\Base\InvalidOptionsException;
use Morpho\Fs\Directory;
use Morpho\Fs\Stat;
use Morpho\Test\TestCase;
use Morpho\Fs\Exception as FsException;

class DirectoryTest extends TestCase {
    public function testPhpFilesRe() {
        $this->assertEquals(1, preg_match(Directory::PHP_FILES_RE, __FILE__));
        $this->assertEquals(1, preg_match(Directory::PHP_FILES_RE, basename(__FILE__)));
        $this->assertEquals(1, preg_match(Directory::PHP_FILES_RE, 'foo/.php'));
        $this->assertEquals(1, preg_match(Directory::PHP_FILES_RE, '.php'));

        $this->assertEquals(0, preg_match(Directory::PHP_FILES_RE, __FILE__ . '.ts'));
        $this->assertEquals(0, preg_match(Directory::PHP_FILES_RE, basename(__FILE__) . '.ts'));
        $this->assertEquals(0, preg_match(Directory::PHP_FILES_RE, 'foo/.ts'));
        $this->assertEquals(0, preg_match(Directory::PHP_FILES_RE, '.ts'));
    }

    public function testDoIn() {
        $curDirPath = getcwd();
        $otherDirPath = $this->createTmpDir();
        $fn = function ($otherDirPathArg) use ($otherDirPath, &$called) {
            $this->assertEquals($otherDirPath, getcwd());
            $this->assertEquals($otherDirPath, $otherDirPathArg);
            $called = true;
            return 'res from fn';
        };
        $res = Directory::doIn($otherDirPath, $fn);
        $this->assertEquals('res from fn', $res);
        $this->assertTrue($called);
        $this->assertEquals($curDirPath, getcwd());
    }

    public function testDelete_WhenParentNotWritable_BoolPredicate() {
        $tmpDirPath = $this->createTmpDir();
        $parentDirPath = $tmpDirPath . '/foo';
        $childDirPath = $parentDirPath . '/bar';
        $testFilePath = $childDirPath . '/test';

        mkdir($childDirPath, 0700, true);
        touch($testFilePath);
        chmod($parentDirPath, 0500);

        $this->assertTrue(is_file($testFilePath));

        Directory::delete($childDirPath, false);

        $this->assertFalse(is_file($testFilePath));
        $this->assertTrue(is_dir($parentDirPath));
    }

    public function testDelete_WhenParentNotWritable_ClosurePredicate() {
        $this->markTestIncomplete();
    }

    public function testDelete_KeepSomeDirectories() {
        $tmpDirPath = $this->createTmpDir();
        mkdir($tmpDirPath . '/foo');
        mkdir($tmpDirPath . '/fox');
        touch($tmpDirPath . '/foo/test.txt');
        mkdir($tmpDirPath . '/foo/bar');
        mkdir($tmpDirPath . '/foo/bar/baz');
        touch($tmpDirPath . '/foo/bar/bird.txt');
        $delete = function ($path, $isDir) use (&$called, $tmpDirPath) {
            $called++;
            switch ($path) {
                case $tmpDirPath:
                    return false; // keep
                case $tmpDirPath . '/foo':
                    $this->assertTrue($isDir);
                    return false; // keep
                case $tmpDirPath . '/fox':
                    $this->assertTrue($isDir);
                    return false; // keep
                case $tmpDirPath . '/foo/test.txt':
                    $this->assertFalse($isDir);
                    return false; // keep
                case $tmpDirPath . '/foo/bar':
                    $this->assertTrue($isDir);
                    return true; // delete this directory and all its content.
                case $tmpDirPath . '/foo/bar/baz':
                    $this->fail('must not be called as the parent directory will be deleted');
                    break;
                case $tmpDirPath . '/foo/bar/bird.txt':
                    $this->fail('must not be called as the parent directory will be deleted');
                    break;
                default:
                    $this->fail('Unknown path: ' . $path);
            }
            $this->assertEquals(2, func_num_args());
        };
        Directory::delete($tmpDirPath, $delete);
        $this->assertTrue($called > 0);
    }

    public function testDelete_Predicate_Depth1() {
        $tmpDirPath = $this->createTmpDir();
        touch($tmpDirPath . '/foo');
        touch($tmpDirPath . '/bar');
        mkdir($tmpDirPath . '/baz');
        Directory::delete($tmpDirPath, function ($filePath) {
            return basename($filePath) === 'bar';
        });
        $this->assertSame(['baz', 'foo'], $this->pathsInDir($tmpDirPath));
    }

    public function testDelete_Predicate_Depth2() {
        $tmpDirPath = $this->createTmpDir();
        touch($tmpDirPath . '/foo');
        mkdir($tmpDirPath . '/bar');
        touch($tmpDirPath . '/bar/cow');
        touch($tmpDirPath . '/bar/wolf');
        touch($tmpDirPath . '/baz');
        Directory::delete($tmpDirPath, function ($filePath) {
            return basename($filePath) === 'wolf';
        });
        $this->assertSame(['bar' , 'bar/cow', 'baz', 'foo'], $this->pathsInDir($tmpDirPath));
    }

    public function testDelete_InvalidArg() {
        $tmpDirPath = $this->createTmpDir();
        $this->expectException(\InvalidArgumentException::class, 'The second argument must be either bool or callable');
        Directory::delete($tmpDirPath, '123');
    }

    public function testDelete_DeleteSelf() {
        $tmpDirPath = $this->createTmpDir();
        touch($tmpDirPath . '/orange.dat');
        mkdir($tmpDirPath . '/foo');
        touch($tmpDirPath . '/foo/test.txt');
        mkdir($tmpDirPath . '/foo/bar');
        touch($tmpDirPath . '/foo/bar/bird.txt');

        Directory::delete($tmpDirPath, false);
        $this->assertTrue(is_dir($tmpDirPath));
        $this->assertSame([], $this->pathsInDir($tmpDirPath));

        Directory::delete($tmpDirPath, true);
        $this->assertFalse(is_dir($tmpDirPath));
    }

    public function testDeleteIfExist_DeleteSelf() {
        $tmpDirPath = $this->createTmpDir();
        touch($tmpDirPath . '/orange.dat');
        mkdir($tmpDirPath . '/foo');
        touch($tmpDirPath . '/foo/test.txt');
        mkdir($tmpDirPath . '/foo/bar');
        touch($tmpDirPath . '/foo/bar/bird.txt');

        Directory::deleteIfExists($tmpDirPath, false);
        $this->assertTrue(is_dir($tmpDirPath));
        $this->assertSame([], $this->pathsInDir($tmpDirPath));

        Directory::deleteIfExists($tmpDirPath, true);
        $this->assertFalse(is_dir($tmpDirPath));
    }

    public function testBrokenLinkPaths() {
        $tmpDirPath = $this->createTmpDir();
        symlink($tmpDirPath . '/foo', $tmpDirPath . '/bar');
        touch($tmpDirPath . '/dest');
        symlink($tmpDirPath . '/dest', $tmpDirPath . '/src');
        $paths = Directory::brokenLinkPaths($tmpDirPath);
        $this->assertEquals([$tmpDirPath . '/bar'], iterator_to_array($paths, false));
    }

    public function testEmptyDirPaths() {
        $tmpDirPath = $this->createTmpDir();
        mkdir($tmpDirPath . '/foo/bar/baz', 0777, true);
        mkdir($tmpDirPath . '/foo/test');
        touch($tmpDirPath . '/foo/pig.txt');
        $emptyDirPaths = iterator_to_array(Directory::emptyDirPaths($tmpDirPath), false);
        sort($emptyDirPaths);
        $this->assertEquals([$tmpDirPath . '/foo/bar/baz', $tmpDirPath . '/foo/test'], $emptyDirPaths);
    }

    public function testDeleteEmptyDirs() {
        $tmpDirPath = $this->createTmpDir();
        mkdir($tmpDirPath . '/foo/bar/baz', 0777, true);
        mkdir($tmpDirPath . '/foo/test');
        touch($tmpDirPath . '/foo/pig.txt');
        Directory::deleteEmptyDirs($tmpDirPath);
        $this->assertEquals(['foo', 'foo/pig.txt'], $this->pathsInDir($tmpDirPath));
    }

    public function testMustExist_RelativePath_0AsArg() {
        $tmpDirPath = $this->createTmpDir();
        $dirPath = $tmpDirPath . '/0';
        mkdir($dirPath);
        chdir($tmpDirPath);
        $this->assertEquals('0', Directory::mustExist('0'));
    }

    public function testMustExist_AbsPath() {
        $this->assertEquals(__DIR__, Directory::mustExist(__DIR__));
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

    public function testCreate_CantCreateEmptyDir() {
        $this->expectException(FsException::class, "The directory path is empty");
        Directory::create('');
    }

    public function testCreate_DoesNotCreateIfDirExists() {
        $this->assertEquals(__DIR__, Directory::create(__DIR__));
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
        $it = Directory::paths($testDirPath, null, ['type' => Stat::DIR, 'recursive' => true]);
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
        $actual = iterator_to_array(Directory::paths($testDirPath, null, ['type' => Stat::DIR | Stat::FILE, 'recursive' => true]), false);
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
        $it = Directory::paths($testDirPath, '~\.php$~si', ['type' => Stat::DIR, 'recursive' => true]);
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
        $it = Directory::paths($testDirPath, '~\.php$~si', ['type' => Stat::DIR | Stat::FILE, 'recursive' => true]);
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
                    ['type' => Stat::FILE, 'recursive' => true]
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
                'type'      => Stat::DIR | Stat::FILE,
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
        $this->expectException(FsException::class, "Cannot copy the directory '$sourceDirPath' into itself");
        Directory::copy($sourceDirPath, $targetDirPath);
    }

    public function testCopy_IntoItself_TargetDirContainsTheSameDirName_ThrowsException() {
        $tmpDirPath = $this->createTmpDir();
        $sourceDirPath = $tmpDirPath . '/foo';
        mkdir($sourceDirPath);
        $targetDirPath = $tmpDirPath;
        $this->expectException(FsException::class, "The '$tmpDirPath' directory already contains the 'foo'");
        Directory::copy($sourceDirPath, $targetDirPath);
    }

    public function testCopy_TargetDirContainsTheSameSubdir() {
        $sourceDirPath = $this->createTmpDir();
        mkdir($sourceDirPath . '/test1/foo', Stat::DIR_MODE, true);

        $targetDirPath = $this->createTmpDir();
        mkdir($targetDirPath . '/test1/foo', Stat::DIR_MODE, true);

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
        mkdir($targetDirPath . '/foo', Stat::DIR_MODE, true);

        $this->assertEquals(
            $targetDirPath . '/' . basename($sourceDirPath),
            Directory::copy($sourceDirPath, $targetDirPath)
        );

        $this->assertEquals(['foo'], $this->pathsInDir($targetDirPath));
    }

    public function testCopy_TargetDirExists_NestedDirExists() {
        $sourceDirPath = $this->createTmpDir();
        mkdir($sourceDirPath . '/public/module/system', Stat::DIR_MODE, true);
        touch($sourceDirPath . '/public/module/system/composer.json');

        $targetDirPath = $this->createTmpDir();
        mkdir($targetDirPath . '/public/module/bootstrap', Stat::DIR_MODE, true);

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

    public function testCopyContents() {
        $sourceDirPath = $this->createTmpDir();
        touch($sourceDirPath . '/file1.txt');
        mkdir($sourceDirPath . '/dir1');
        touch($sourceDirPath . '/dir1/file2.txt');
        mkdir($sourceDirPath . '/.git');
        touch($sourceDirPath . '/.git/config');

        $targetDirPath = $this->createTmpDir() . '/target';

        $this->assertSame($targetDirPath, Directory::copyContents($sourceDirPath, $targetDirPath));
        $this->assertTrue(is_file($targetDirPath . '/file1.txt'));
        $this->assertTrue(is_file($targetDirPath . '/dir1/file2.txt'));
        $this->assertTrue(is_file($targetDirPath . '/.git/config'));

        $count = 0;
        foreach (new \DirectoryIterator($targetDirPath) as $item) {
            if ($item->isDot()) {
                continue;
            }
            $count++;
        }
        $this->assertSame(3, $count);
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

    public function testFileNames_NotRecursiveWithoutProcessor() {
        $this->assertEquals(
            ['1.txt'],
            iterator_to_array(
                Directory::fileNames($this->getTestDirPath()),
                false
            )
        );
    }

    public function testFileNames_RecursiveWithoutProcessor() {
        $fileNames = Directory::fileNames($this->getTestDirPath(), null, ['recursive' => true]);
        $fileNames = iterator_to_array($fileNames, false);
        sort($fileNames);
        $this->assertEquals(
            [
                '1.txt',
                '3.php',
                '6.php',
            ],
            $fileNames
        );
    }

    public function testFileNames_RecursiveWithProcessor() {
        $processor = function (...$args) use (&$calledTimes) {
            $this->assertNotContains('/', $args[0]);
            $this->assertContains('/', $args[1]);
            $this->assertCount(2, $args);
            $calledTimes++;
            return $args[0] !== '6.php';
        };
        $fileNames = Directory::fileNames($this->getTestDirPath(), $processor, ['recursive' => true]);
        $fileNames = iterator_to_array($fileNames, false);
        sort($fileNames);
        $this->assertEquals(3, $calledTimes);
        $this->assertEquals(
            [
                '1.txt',
                '3.php',
            ],
            $fileNames
        );
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

    private function assertDirContentsEqual(string $dirPathExpectedContent, string $dirPathActualContent) {
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
