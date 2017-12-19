<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web\Uri;

use Morpho\Test\TestCase;
use Morpho\Web\Uri\Authority;
use Morpho\Web\Uri\Path;
use Morpho\Web\Uri\Query;
use Morpho\Web\Uri\Uri;

class UriTest extends TestCase {
    use TUriParserDataProvider;

    public function testSchemeAccessors() {
        $this->checkAccessors([new Uri(), 'scheme'], '', 'http');
        $this->checkAccessors([new Uri(), 'scheme'], '', 'http');
    }

    public function testAuthorityAccessors() {
        $uri = new Uri();

        $authority = $uri->authority();
        $this->assertEquals(new Authority(), $authority);
        $this->assertTrue($authority->isNull());

        $newAuthority = new Authority('example.com');
        $this->assertNull($uri->setAuthority($newAuthority));
        $this->assertSame($newAuthority, $uri->authority());
    }

    public function testPathAccessors() {
        $uri = new Uri();

        $this->assertEquals(new Path(''), $uri->path());

        $path = '/foo/bar';
        $this->assertNull($uri->setPath($path));
        $this->assertEquals(new Path($path), $uri->path());

        $path = new Path($path);
        $this->assertNull($uri->setPath($path));
        $this->assertSame($path, $uri->path());
    }

    public function testQueryAccessors() {
        $uri = new Uri();

        $query = $uri->query();
        $this->assertEquals(new Query(), $query);
        $this->assertTrue($query->isNull());

        $newQuery = new Query('foo=bar&test=123');
        $this->assertNull($uri->setQuery($newQuery));
        $this->assertSame($newQuery, $uri->query());
    }

    public function testFragmentAccessors() {
        $this->checkAccessors([new Uri(), 'fragment'], null, 'toc');
        $this->checkAccessors([new Uri(), 'fragment'], null, null);
    }
    
    public function dataForToStr() {
        foreach ($this->dataForParse() as $sample) {
            yield [$sample[0]];
        }
    }

    /**
     * @dataProvider dataForToStr
     */
    public function testToStr(string $uriStr) {
        $uri = new Uri($uriStr);
        $this->assertSame($uriStr, $uri->toStr(false));
    }
    
    public function dataForResolveRelUri_NormalExamples() {
        yield ['g:h', 'g:h'];
        yield ['g', 'http://a/b/c/g'];
        yield ['./g', 'http://a/b/c/g'];
        yield ['g/', 'http://a/b/c/g/'];
        yield ['/g', 'http://a/g'];
        yield ['//g', 'http://g'];
        yield ['?y', 'http://a/b/c/d;p?y'];
        yield ['g?y', 'http://a/b/c/g?y'];
        yield ['#s', 'http://a/b/c/d;p?q#s'];
        yield ['g#s', 'http://a/b/c/g#s'];
        yield ['g?y#s', 'http://a/b/c/g?y#s'];
        yield [';x', 'http://a/b/c/;x'];
        yield ['g;x', 'http://a/b/c/g;x'];
        yield ['g;x?y#s', 'http://a/b/c/g;x?y#s'];
        yield ['', 'http://a/b/c/d;p?q'];
        yield ['.', 'http://a/b/c/'];
        yield ['./', 'http://a/b/c/'];
        yield ['..', 'http://a/b/'];
        yield ['../', 'http://a/b/'];
        yield ['../g', 'http://a/b/g'];
        yield ['../..', 'http://a/'];
        yield ['../../', 'http://a/'];
        yield ['../../g', 'http://a/g'];

        yield ['http://foo/bar', 'http://foo/bar'];
    }

    /**
     * @dataProvider dataForResolveRelUri_NormalExamples
     */
    public function testResolveRelUri_NormalExamples($relUri, $expected) {
        $uri = Uri::resolveRelUri('http://a/b/c/d;p?q', $relUri);
        $this->assertSame($expected, $uri->toStr(false));
    }
    
    public function dataForResolveRelUri_AbnormalExamples() {
        yield ['../../../g', 'http://a/g'];
        yield ['../../../../g', 'http://a/g'];
        yield ['/./g', 'http://a/g'];
        yield ['/../g', 'http://a/g'];
        yield ['g.', 'http://a/b/c/g.'];
        yield ['.g', 'http://a/b/c/.g'];
        yield ['g..', 'http://a/b/c/g..'];
        yield ['..g', 'http://a/b/c/..g'];
        yield ['./../g', 'http://a/b/g'];
        yield ['./g/.', 'http://a/b/c/g/'];
        yield ['g/./h', 'http://a/b/c/g/h'];
        yield ['g/../h', 'http://a/b/c/h'];
        yield ['g;x, 1/./y', 'http://a/b/c/g;x, 1/y'];
        yield ['g;x, 1/../y', 'http://a/b/c/y'];
        yield ['g?y/./x', 'http://a/b/c/g?y/./x'];
        yield ['g?y/../x', 'http://a/b/c/g?y/../x'];
        yield ['g#s/./x', 'http://a/b/c/g#s/./x'];
        yield ['g#s/../x', 'http://a/b/c/g#s/../x'];
        yield ['http:g', 'http:g'];
    }

    /**
     * @dataProvider dataForResolveRelUri_AbnormalExamples
     */
    public function testResolveRelUri_AbnormalExamples($relUri, $expected) {
        $uri = Uri::resolveRelUri('http://a/b/c/d;p?q', $relUri);
        $this->assertSame($expected, $uri->toStr(false));
    }
}