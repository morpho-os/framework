<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Fs;

use Morpho\Test\TestCase;
use Morpho\Fs\Path;
use Morpho\Fs\Exception as FsException;

class PathTest extends TestCase {
    public function dataForIsAbs() {
        return [
            [
                '',
                false,
            ],
            [
                'ab',
                false,
            ],
            [
                '\\',  // UNC (Universal Naming Convention)
                true,
            ],
            [
                '/',
                true,
            ],
            [
                'C:/',
                true,
            ],
            [
                'ab/cd',
                false,
            ],
            [
                'ab/cd/',
                false,
            ],
            [
                'ab/cd\\',
                false,
            ],
            [
                'ab\\cd',
                false,
            ],
            [
                'C:\\',
                true,
            ],
            [
                __FILE__,
                true,
            ],
        ];
    }

    /**
     * @dataProvider dataForIsAbs
     */
    public function testIsAbs($path, $isAbs) {
        $isAbs ? $this->assertTrue(Path::isAbs($path)) : $this->assertFalse(Path::isAbs($path));
    }

    public function dataForAssertSafe() {
        return [
            ['..'],
            ['C:/foo/../bar'],
            ['C:\foo\..\bar'],
            ["some/file.php\x00"],
            ["foo/../bar"],
            ["foo/.."],
        ];
    }

    /**
     * @dataProvider dataForAssertSafe
     */
    public function testAssertSafeThrowsExceptionForNotSafePath($path) {
        $this->expectException('\Morpho\Base\SecurityException', 'Invalid file path was detected.');
        Path::assertSafe($path);
    }

    public function dataForAssertSafeDoesNotThrowExceptionForSafePath() {
        return [
            [
                'C:/foo/bar',
                'C:\foo\bar',
                'foo/bar',
                '/foo/bar/index.php',
            ],
        ];
    }

    /**
     * @dataProvider dataForAssertSafeDoesNotThrowExceptionForSafePath
     */
    public function testAssertSafe_DoesNotThrowExceptionForSafePath($path) {
        Path::assertSafe($path);
        $this->markTestAsNotRisky();
    }

    public function dataForIsNormalizedInvalid() {
        $dataSet = [
            ['\foo\bar\baz\\'],
            ['\foo\bar\baz/'],

            ['\foo\..\baz\\'],
            ['\foo\..\baz/'],

            ['\foo\..\baz'],
            ['/foo/../baz'],
            ['/foo/../baz\\'],
            ['/foo/../baz/'],

            ['C:\foo\bar\baz\\'],
            ['C:\foo\bar\baz/'],

            ['C:\foo\..\baz'],
            ['C:\foo\..\baz\\'],
            ['C:\foo\..\baz/'],

            ['C:/foo/../baz'],
            ['C:/foo/../baz\\'],
            ['C:/foo/../baz/'],
        ];
        $isWin = $this->isWindows();
        if ($isWin) {
            $dataSet = array_merge($dataSet, [
                ['\foo\bar\baz'],
                ['C:\foo\bar\baz'],
            ]);
        }
        return $dataSet;
    }

    /**
     * @dataProvider dataForIsNormalizedInvalid
     */
    public function testIsNormalizedInvalid($invalid) {
        $this->assertFalse(Path::isNormalized($invalid));
    }

    public function testIsNormalizedValid() {
        $this->assertTrue(Path::isNormalized('/foo/bar/baz'));
        $this->assertTrue(Path::isNormalized('foo/bar/baz'));
        $this->assertTrue(Path::isNormalized('C:/foo/bar/baz'));
    }

    public function testToAbsShouldThrowExceptionForInvalidPath() {
        $invalidPath = __DIR__ . '/ttttt';
        $this->expectException(FsException::class, "Unable to detect absolute path for the '$invalidPath' path.");
        Path::toAbs($invalidPath);
    }

    public function dataForToAbs() {
        return [
            [
                '/', '/',
            ],
            [__DIR__, __DIR__],
            [__FILE__, __FILE__],
            [getcwd(), ''],
            [__DIR__, __DIR__ . '/_files/..'],
            [__FILE__, __DIR__ . '/_files/../' . basename(__FILE__)],
        ];
    }

    /**
     * @dataProvider dataForToAbs
     */
    public function testToAbs($expected, $path) {
        $actual = Path::toAbs($path);
        $this->assertSame(str_replace('\\', '/', $expected), $actual);
    }

    public function testCombine_UnixPaths() {
        if ($this->isWindows()) {
            $this->assertEquals('foo/bar/baz', Path::combine('foo\\bar', 'baz'));
            $this->assertEquals('foo/bar/baz', Path::combine('foo\\bar', '/baz'));
            $this->assertEquals('/foo/bar/baz', Path::combine('/foo\\bar', 'baz'));
            $this->assertEquals('/foo/bar/baz', Path::combine('/foo\\bar', '/baz'));
            $this->assertEquals('foo/bar/baz', Path::combine('foo\\bar/', '/baz'));
            $this->assertEquals('/foo/bar/baz', Path::combine('/foo\\bar/', '/baz'));
        } else {
            $this->assertEquals('foo\\bar/baz', Path::combine('foo\\bar', 'baz'));
            $this->assertEquals('foo\\bar/baz', Path::combine('foo\\bar', '/baz'));
            $this->assertEquals('/foo\\bar/baz', Path::combine('/foo\\bar', 'baz'));
            $this->assertEquals('/foo\\bar/baz', Path::combine('/foo\\bar', '/baz'));
            $this->assertEquals('foo\\bar/baz', Path::combine('foo\\bar/', '/baz'));
            $this->assertEquals('/foo\\bar/baz', Path::combine('/foo\\bar/', '/baz'));
        }
    }

    public function testCombine_WinPaths() {
        if ($this->isWindows()) {
            $this->assertEquals('foo/bar/baz', Path::combine('foo\\bar', 'baz'));
            $this->assertEquals('foo/bar/baz', Path::combine('foo\\bar', '/baz'));
            $this->assertEquals('C:/foo/bar/baz', Path::combine('C:/foo\\bar', 'baz'));
            $this->assertEquals('C:/foo/bar/baz', Path::combine('C:/foo\\bar', '/baz'));
            $this->assertEquals('foo/bar/baz', Path::combine('foo\\bar/', '/baz'));
            $this->assertEquals('C:/foo/bar/baz', Path::combine('C:/foo\\bar/', '/baz'));
        } else {
            $this->assertEquals('foo\\bar/baz', Path::combine('foo\\bar', 'baz'));
            $this->assertEquals('foo\\bar/baz', Path::combine('foo\\bar', '/baz'));
            $this->assertEquals('C:/foo\\bar/baz', Path::combine('C:/foo\\bar', 'baz'));
            $this->assertEquals('C:/foo\\bar/baz', Path::combine('C:/foo\\bar', '/baz'));
            $this->assertEquals('foo\\bar/baz', Path::combine('foo\\bar/', '/baz'));
            $this->assertEquals('C:/foo\\bar/baz', Path::combine('C:/foo\\bar/', '/baz'));
        }
    }

    public function testCombine_WithRootSlash() {
        $this->assertEquals('/foo/bar', Path::combine('/', '/foo', '/bar'));
        $this->assertEquals('/foo/bar', Path::combine('/', 'foo', 'bar'));
        $this->assertEquals('/', Path::combine('/'));
        $this->assertEquals('/', Path::combine('/', '', null));
        $this->assertEquals('', Path::combine(null, '', null));
        $this->assertEquals('/', Path::combine('', '/'));
        $this->assertEquals('/foo/bar', Path::combine('', '/foo/bar'));
    }

    public function testCombine_ArraySyntax() {
        $this->assertEquals('foo/bar/baz', Path::combine(['foo', 'bar', null, 'baz']));
    }

    public function testCombineEmpty() {
        $this->assertSame('', Path::combine(['', '', '']));
    }

    public function dataForCombine_AbsUri() {
        return [
            [
                'http://foo/bar',
                'http://foo/',
                '/',
                '/bar',
            ],
            [
                'http://localhost/foo',
                'http://localhost',
                '/foo',
            ],
            [
                'http://localhost/foo/bar',
                'http://localhost/foo/',
                '/bar/',
            ],
            [
                'http://localhost/foo',
                'http://localhost',
                'foo',
            ],
            [
                'https://localhost/foo/bar/baz',
                'https://localhost',
                'foo',
                '/bar/baz',
            ],
        ];
    }

    /**
     * @dataProvider dataForCombine_AbsUri
     */
    public function testCombine_AbsUri($expected, $uri, $path1, $path2 = null) {
        $this->assertEquals($expected, Path::combine($uri, $path1, $path2));
    }

    public function testNormalize() {
        if ($this->isWindows()) {
            $this->assertEquals('foo/bar/baz', Path::normalize('foo\\bar\\baz/'));
            $this->assertEquals('/foo/bar/baz', Path::normalize('/foo\\bar\\baz/'));
            $this->assertEquals('/foo/bar/baz', Path::normalize('/foo\\bar\\baz/'));
            $this->assertEquals('C:/foo/bar/baz', Path::normalize('C:/foo\\bar\\baz/'));
            $this->assertEquals('C:/foo/bar/baz', Path::normalize('C:/foo\\bar\\baz/'));
            $this->assertEquals('C:/foo/bar/baz', Path::normalize('C:/foo\\bar\\baz\\'));
        } else {
            // In Linux the `\` character is allowed for file name.
            $this->assertEquals('foo\\bar\\baz', Path::normalize('foo\\bar\\baz/'));
            $this->assertEquals('/foo\\bar\\baz', Path::normalize('/foo\\bar\\baz/'));
            $this->assertEquals('/foo\\bar\\baz', Path::normalize('/foo\\bar\\baz/'));
            $this->assertEquals('C:/foo\\bar\\baz', Path::normalize('C:/foo\\bar\\baz/'));
            $this->assertEquals('C:/foo\\bar\\baz', Path::normalize('C:/foo\\bar\\baz/'));
            $this->assertEquals('C:/foo\\bar\\baz', Path::normalize('C:/foo\\bar\\baz\\'));
        }
        $this->assertEquals('/', Path::normalize('/'));
        $this->assertEquals('', Path::normalize(''));
    }

    public function testNormalize_RelBetween() {
        $this->assertEquals('/foo/bar/setosa/versicolor', Path::normalize('/foo/bar/baz/../setosa/versicolor'));
    }

    public function testToRel() {
        $baseDirPath = __DIR__ . '/../../..';
        $this->assertEquals(Path::toRel($baseDirPath . '/module/foo/bar', $baseDirPath), 'module/foo/bar');
        $this->assertSame(Path::toRel($baseDirPath, $baseDirPath), '');
        $this->assertSame(Path::toRel($baseDirPath . '/', $baseDirPath), '');
        $this->assertSame(Path::toRel($baseDirPath . '/index.php', $baseDirPath), 'index.php');
    }

    public function testToRel_ThrowsExceptionWhenBasePathNotContainedWithinPath() {
        $baseDirPath = '/foo/bar/baz/';
        $path = __DIR__;
        $this->expectException(
            FsException::class,
            "The path '" . str_replace('\\', '/', $path) . "' does not contain the base path '/foo/bar/baz'"
        );
        Path::toRel($path, $baseDirPath);
    }

    public function testNameWithoutExt() {
        $this->assertEquals('', Path::nameWithoutExt(''));
        $this->assertEquals('', Path::nameWithoutExt('.jpg'));
        $this->assertEquals('foo', Path::nameWithoutExt('foo.jpg'));
    }

    public function testExt() {
        $this->assertEquals('', Path::ext(''));
        $this->assertEquals('jpg', Path::ext('.jpg'));
        $this->assertEquals('txt', Path::ext('config.txt'));
        $this->assertEquals('txt', Path::ext('.config.txt'));

        $this->assertEquals('txt', Path::ext('dir/.txt'));
        $this->assertEquals('txt', Path::ext('dir/config.txt'));
        $this->assertEquals('php', Path::ext(__FILE__));
        $this->assertEquals('ts', Path::ext(__DIR__ . '/test.d.ts'));

        $this->assertEquals('', Path::ext('term.'));
    }

    public function testFileName() {
        $this->assertEquals('PathTest.php', Path::fileName(__FILE__));
    }

    public function testNormalizeExt() {
        $this->assertEquals('php', Path::normalizeExt('.php'));
    }

    public function testChangeExt() {
        $this->assertEquals('term.txt', Path::changeExt('term.jpg', 'txt'));
        $this->assertEquals('term.txt', Path::changeExt('term.jpg', '.txt'));

        $this->assertEquals('term.txt', Path::changeExt('term.txt', 'txt'));
        $this->assertEquals('term.txt', Path::changeExt('term.txt', '.txt'));

        $this->assertEquals('term.txt', Path::changeExt('term', 'txt'));
        $this->assertEquals('term.txt', Path::changeExt('term', '.txt'));

        $this->assertEquals('/foo/bar/term.txt', Path::changeExt('/foo/bar/term.jpg', 'txt'));
        $this->assertEquals('/foo/bar/term.txt', Path::changeExt('/foo/bar/term.jpg', '.txt'));
        $this->assertEquals('/foo/bar/term.txt', Path::changeExt('/foo/bar/term.', 'txt'));

        $this->assertEquals('dir/foo.d.ts', Path::changeExt('dir/foo.d.ts', 'd.ts'));
    }

    public function testChangeExt_EmptyPathOrExt() {
        $this->assertEquals('term', Path::changeExt('term', ''));
        $this->assertEquals('term', Path::changeExt('term.', ''));
        $this->assertEquals('/foo/bar/term', Path::changeExt('/foo/bar/term', ''));
        $this->assertEquals('/foo/bar/term', Path::changeExt('/foo/bar/term.txt', ''));
        $this->assertEquals('/foo/bar/term', Path::changeExt('/foo/bar/term.', ''));

        $this->assertEquals('.jpg', Path::changeExt('', '.jpg'));
        $this->assertEquals('.jpg', Path::changeExt('', 'jpg'));
    }

    public function testDropExt() {
        if ($this->isWindows()) {
            $this->assertEquals('C:/foo/bar/test', Path::dropExt('C:\\foo\\bar\\test'));
        } else {
            $this->assertEquals('C:\\foo\\bar\\test', Path::dropExt('C:\\foo\\bar\\test'));
        }
        $this->assertEquals('/foo/bar/test', Path::dropExt('/foo/bar/test.php'));
        $this->assertEquals('test', Path::dropExt('test.php'));
    }

    public function testUnique_ThrowsExceptionWhenParentDirDoesNotExist() {
        $this->expectException(FsException::class, "does not exist");
        Path::unique(__FILE__ . '/foo');
    }

    public function testUnique_ParentDirExistUniquePathPassedAsArg() {
        $uniquePath = __DIR__ . '/unique123';
        $this->assertSame($uniquePath, Path::unique($uniquePath));
    }

    public function testUnique_ExistingFileWithExt() {
        $this->assertEquals(__DIR__ . '/' . basename(__FILE__, '.php') . '-0.php', Path::unique(__FILE__));
    }

    public function testUnique_ExistingFileWithoutExt() {
        $tmpDirPath = $this->createTmpDir();
        $tmpFilePath = $tmpDirPath . '/abc';
        touch($tmpFilePath);
        $this->assertEquals($tmpFilePath . '-0', Path::unique($tmpFilePath));
    }

    public function testUnique_ExistingDirectory() {
        $this->assertEquals(__DIR__ . '-0', Path::unique(__DIR__));
    }

    public function testUnique_ThrowsExceptionWhenNumberOfAttemptsReachedForFile() {
        $filePath = __FILE__;
        $expectedMessage = "Unable to generate an unique path for the '$filePath' (tried 0 times)";
        $this->expectException(FsException::class, $expectedMessage);
        Path::unique($filePath, true, 0);
    }

    public function testUnique_ThrowsExceptionWhenNumberOfAttemptsReachedForDirectory() {
        $dirPath = __DIR__;
        $expectedMessage = "Unable to generate an unique path for the '$dirPath' (tried 0 times)";
        $this->expectException(FsException::class, $expectedMessage);
        Path::unique($dirPath, true, 0);
    }
}
