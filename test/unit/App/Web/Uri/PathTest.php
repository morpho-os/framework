<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\Uri;

use Morpho\Testing\TestCase;
use Morpho\App\Web\Uri\IUriComponent;
use Morpho\App\Web\Uri\Path;

class PathTest extends TestCase {
    public function testInitialState() {
        $path = new Path('');
        $this->assertSame('', $path->toStr(false));
        $this->assertNull($path->basePath());
        $this->assertNull($path->relPath());
    }

    public function testInterface() {
        $this->assertInstanceOf(IUriComponent::class, new Path('test'));
    }

    public function testToStr_Encode() {
        $pathComp1 = 'это';
        $pathComp2 = 'тест';
        $path = new Path($pathComp1 . '/' . $pathComp2);
        $this->assertSame(
            \rawurlencode($pathComp1) . '/' . \rawurlencode($pathComp2),
            $path->toStr(true)
        );
    }

    public function dataForBasePathAccessors() {
        yield [
            '/base/path/foo/bar',
            '/base/path',
            'foo/bar',
        ];
        yield [
            '',
            '',
            '',
        ];
    }

    /**
     * @dataProvider dataForBasePathAccessors
     */
    public function testBasePathAccessors(string $uri, string $basePathStr, string $relPathStr) {
        $path = new Path($uri);
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($path->setBasePath($basePathStr));
        $this->assertSame($basePathStr, $path->basePath());
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertSame($relPathStr, $path->relPath());
    }

    public function testThrowsExIfPathDoesNotStartWithBasePath() {
        $path = new Path('/foo/bar/baz');
        $this->expectException(\RuntimeException::class, 'The base path is not begging of the path');
        $path->setBasePath('/base/path');
    }

    public function dataForIsRel() {
        yield ['', true];
        yield ['/', false];
        yield ['//', false];
        yield ['foo/bar', true];
        yield ['/foo/bar', false];
        yield ['//foo/bar', false];
        yield ['./foo/bar', true];
        yield ['../foo/bar', true];
        yield ['/../foo/bar', false];
        yield ['.', true];
    }

    /**
     * @dataProvider dataForIsRel
     */
    public function testIsRel(string $pathStr, bool $isRel) {
        $path = new Path($pathStr);
        $isRel ? $this->assertTrue($path->isRel()) : $this->assertFalse($path->isRel());
    }
}
