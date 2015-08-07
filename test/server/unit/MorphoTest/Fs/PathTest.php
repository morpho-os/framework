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
        return array(
            array('..'),
            array('C:/foo/../bar'),
            array('C:\foo\..\bar'),
            array("some/file.php\x00"),
            array("foo/../bar"),
            array("foo/.."),
        );
    }

    /**
     * @dataProvider dataForAssertSafe
     */
    public function testAssertSafeThrowsExceptionForNotSafePath($path) {
        $this->setExpectedException('\Morpho\Base\SecurityException', 'Invalid file path was detected.');
        Path::assertSafe($path);
    }

    public function dataForAssertSafeDoesNotThrowExceptionForSafePath() {
        return array(
            array(
                'C:/foo/bar',
                'C:\foo\bar',
                'foo/bar',
                '/foo/bar/index.php',
            ),
        );
    }

    /**
     * @dataProvider dataForAssertSafeDoesNotThrowExceptionForSafePath
     */
    public function testAssertSafeDoesNotThrowExceptionForSafePath($path) {
        Path::assertSafe($path);
    }

    public function dataForIsNormalizedInvalid() {
        return array(
            array('\foo\bar\baz'),
            array('\foo\bar\baz\\'),
            array('\foo\bar\baz/'),

            array('\foo\..\baz'),
            array('\foo\..\baz\\'),
            array('\foo\..\baz/'),

            array('/foo/../baz'),
            array('/foo/../baz\\'),
            array('/foo/../baz/'),

            array('C:\foo\bar\baz'),
            array('C:\foo\bar\baz\\'),
            array('C:\foo\bar\baz/'),

            array('C:\foo\..\baz'),
            array('C:\foo\..\baz\\'),
            array('C:\foo\..\baz/'),

            array('C:/foo/../baz'),
            array('C:/foo/../baz\\'),
            array('C:/foo/../baz/'),
        );
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
            array(__DIR__, __DIR__),
            array(__FILE__, __FILE__),
            array(getcwd(), ''),
            array(__DIR__, __DIR__ . '/_files/..'),
            array(__FILE__, __DIR__ . '/_files/../' . basename(__FILE__)),
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
        $this->assertEquals('foo/bar/baz', Path::combine(array('foo', 'bar', null, 'baz')));
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

    public function dataForBase64EncodeDecode() {
        return [
            [
                "abc 123",
            ],
            [
                "�J�sӑ釘/",
            ],
            [
                "\x00\r\n123'`",
            ],
        ];
    }

    /**
     * @dataProvider dataForBase64EncodeDecode
     */
    public function testBase64EncodeDecode($uri) {
        $encoded = Path::base64Encode($uri);
        $this->assertRegExp('~^' . Path::BASE64_URI_REGEXP . '+$~s', $encoded);
        $this->assertSame($uri, Path::base64Decode($encoded));
    }

    public function testGetExtension() {
        $this->markTestIncomplete();
    }
}
