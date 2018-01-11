<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Qa\Test\Unit\Web;

use Morpho\Test\TestCase;
use function Morpho\Web\prependBasePath;

class FunctionsTest extends TestCase {
    public function dataForPrependBasePath() {
        yield [
            '',
            '',
            '',
        ];
        yield [
            '',
            '/',
            '/',
        ];
        yield [
            '/base/path',
            '',
            '',
        ];
        yield [
            '/base/path',
            '/',
            '/base/path',
        ];
        yield [
            '',
            '/foo/bar?test=123#toc',
            '/foo/bar?test=123#toc',
        ];
        yield [
            '/base/path',
            '/foo/bar?test=123#toc',
            '/base/path/foo/bar?test=123#toc',
        ];
        yield [
            '/base/path',
            '/foo/bar?redirect=' . rawurlencode('http://localhost/some/base/path/abc/def?three=qux&four=pizza'),
            '/base/path/foo/bar?redirect=' . rawurlencode('http://localhost/some/base/path/abc/def?three=qux&four=pizza')
        ];
    }

    /**
     * @dataProvider dataForPrependBasePath
     */
    public function testPrependBasePath(string $basePath, string $uriStr, string $expected) {
        $uri = prependBasePath(function () use ($basePath) {
            return $basePath;
        }, $uriStr);
        $this->assertSame($expected, $uri->toStr(false));
    }
}