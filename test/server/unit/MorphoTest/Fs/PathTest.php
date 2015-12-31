<?php
namespace MorphoTest\Fs;

use Morpho\Test\TestCase;
use Morpho\Fs\Path;

class PathTest extends TestCase {
    public function dataForIsAbsolute() {
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
     * @dataProvider dataForIsAbsolute
     */
    public function testIsAbsolute($path, $isAbsolute) {
        $isAbsolute ? $this->assertTrue(Path::isAbsolute($path)) : $this->assertFalse(Path::isAbsolute($path));
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
        $this->setExpectedException('\Morpho\Base\SecurityException', 'Invalid file path was detected.');
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
    public function testAssertSafeDoesNotThrowExceptionForSafePath($path) {
        Path::assertSafe($path);
    }

    public function dataForIsNormalizedInvalid() {
        return [
            ['\foo\bar\baz'],
            ['\foo\bar\baz\\'],
            ['\foo\bar\baz/'],

            ['\foo\..\baz'],
            ['\foo\..\baz\\'],
            ['\foo\..\baz/'],

            ['/foo/../baz'],
            ['/foo/../baz\\'],
            ['/foo/../baz/'],

            ['C:\foo\bar\baz'],
            ['C:\foo\bar\baz\\'],
            ['C:\foo\bar\baz/'],

            ['C:\foo\..\baz'],
            ['C:\foo\..\baz\\'],
            ['C:\foo\..\baz/'],

            ['C:/foo/../baz'],
            ['C:/foo/../baz\\'],
            ['C:/foo/../baz/'],
        ];
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

    public function testToAbsoluteShouldThrowExceptionForInvalidPath() {
        $invalidPath = __DIR__ . '/ttttt';
        $this->setExpectedException('\Morpho\Fs\IoException', "Unable to detect absolute path for the '$invalidPath' path.");
        Path::toAbsolute($invalidPath);
    }

    public function dataForToAbsolute() {
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
     * @dataProvider dataForToAbsolute
     */
    public function testToAbsolute($expected, $path) {
        $actual = Path::toAbsolute($path);
        $this->assertSame(str_replace('\\', '/', $expected), $actual);
    }

    public function testCombine_UnixPaths() {
        $this->assertEquals('foo/bar/baz', Path::combine('foo\\bar', 'baz'));
        $this->assertEquals('foo/bar/baz', Path::combine('foo\\bar', '/baz'));
        $this->assertEquals('/foo/bar/baz', Path::combine('/foo\\bar', 'baz'));
        $this->assertEquals('/foo/bar/baz', Path::combine('/foo\\bar', '/baz'));
        $this->assertEquals('foo/bar/baz', Path::combine('foo\\bar/', '/baz'));
        $this->assertEquals('/foo/bar/baz', Path::combine('/foo\\bar/', '/baz'));
    }

    public function testCombine_WinPaths() {
        $this->assertEquals('foo/bar/baz', Path::combine('foo\\bar', 'baz'));
        $this->assertEquals('foo/bar/baz', Path::combine('foo\\bar', '/baz'));
        $this->assertEquals('C:/foo/bar/baz', Path::combine('C:/foo\\bar', 'baz'));
        $this->assertEquals('C:/foo/bar/baz', Path::combine('C:/foo\\bar', '/baz'));

        $this->assertEquals('foo/bar/baz', Path::combine('foo\\bar/', '/baz'));
        $this->assertEquals('C:/foo/bar/baz', Path::combine('C:/foo\\bar/', '/baz'));
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

    public function dataForCombine_AbsoluteUri() {
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
     * @dataProvider dataForCombine_AbsoluteUri
     */
    public function testCombine_AbsoluteUri($expected, $uri, $path1, $path2 = null) {
        $this->assertEquals($expected, Path::combine($uri, $path1, $path2));
    }

    public function testNormalize() {
        $this->assertEquals('foo/bar/baz', Path::normalize('foo\bar\baz/'));
        $this->assertEquals('/foo/bar/baz', Path::normalize('/foo\bar\baz/'));
        $this->assertEquals('/foo/bar/baz', Path::normalize('/foo\bar\baz/'));
        $this->assertEquals('/', Path::normalize('/'));

        $this->assertEquals('C:/foo/bar/baz', Path::normalize('C:/foo\bar\baz/'));
        $this->assertEquals('C:/foo/bar/baz', Path::normalize('C:/foo\bar\baz/'));
    }

    public function testToRelative() {
        $baseDirPath = __DIR__ . '/../../..';
        $this->assertEquals('module/foo/bar', Path::toRelative($baseDirPath, $baseDirPath . '/module/foo/bar'));
        $this->assertSame('', Path::toRelative($baseDirPath, $baseDirPath));
        $this->assertSame('', Path::toRelative($baseDirPath, $baseDirPath . '/'));
        $this->assertSame('index.php', Path::toRelative($baseDirPath, $baseDirPath . '/index.php'));
    }

    public function testToRelativeThrowsExceptionWhenBasePathNotContainedWithinPath() {
        $baseDirPath = '/foo/bar/baz/';
        $path = __DIR__;
        $this->setExpectedException(
            '\Morpho\Fs\IoException',
            "The path '" . str_replace('\\', '/', $path) . "' does not contain the base path '/foo/bar/baz'."
        );
        Path::toRelative($baseDirPath, $path);
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

    public function testNewExt() {
        $this->assertEquals('term.txt', Path::newExt('term.jpg', 'txt'));
        $this->assertEquals('term.txt', Path::newExt('term.jpg', '.txt'));

        $this->assertEquals('term.txt', Path::newExt('term.txt', 'txt'));
        $this->assertEquals('term.txt', Path::newExt('term.txt', '.txt'));

        $this->assertEquals('term.txt', Path::newExt('term', 'txt'));
        $this->assertEquals('term.txt', Path::newExt('term', '.txt'));

        $this->assertEquals('/foo/bar/term.txt', Path::newExt('/foo/bar/term.jpg', 'txt'));
        $this->assertEquals('/foo/bar/term.txt', Path::newExt('/foo/bar/term.jpg', '.txt'));
        $this->assertEquals('/foo/bar/term.txt', Path::newExt('/foo/bar/term.', 'txt'));

        $this->assertEquals('dir/foo.d.ts', Path::newExt('dir/foo.d.ts', 'd.ts'));
    }

    public function testNewExt_EmptyPathOrExt() {
        $this->assertEquals('term', Path::newExt('term', ''));
        $this->assertEquals('term', Path::newExt('term.', ''));
        $this->assertEquals('/foo/bar/term', Path::newExt('/foo/bar/term', ''));
        $this->assertEquals('/foo/bar/term', Path::newExt('/foo/bar/term.txt', ''));
        $this->assertEquals('/foo/bar/term', Path::newExt('/foo/bar/term.', ''));

        $this->assertEquals('.jpg', Path::newExt('', '.jpg'));
        $this->assertEquals('.jpg', Path::newExt('', 'jpg'));
    }
}
