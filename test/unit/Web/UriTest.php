<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use Morpho\Test\TestCase;
use Morpho\Web\Authority;
use Morpho\Web\Query;
use Morpho\Web\Uri;

class UriTest extends TestCase {
    use TUriParserDataProvider;

    public function testBasePathAccessors() {
        $this->checkAccessors([new Uri(), 'basePath'], null, '/base/uri');
    }

    public function testSchemeAccessors() {
        $this->checkAccessors([new Uri(), 'scheme'], '', 'http');
        $this->checkAccessors([new Uri(), 'scheme'], '', 'http');
    }

    public function testAuthorityAccessors() {
        $this->checkAccessors([new Uri(), 'authority'], null, new Authority('example.com'));
        $this->checkAccessors([new Uri(), 'authority'], null, null);
    }

    public function testPathAccessors() {
        $this->checkAccessors([new Uri(), 'path'], '', '/foo/bar/baz');
    }

    public function testQueryAccessors() {
        $this->checkAccessors([new Uri(), 'query'], null, new Query('foo=bar&test=123'));
        $this->checkAccessors([new Uri(), 'query'], null, null);
    }

    public function testFragmentAccessors() {
        $this->checkAccessors([new Uri(), 'fragment'], null, 'toc');
        $this->checkAccessors([new Uri(), 'fragment'], null, null);
    }
    
    public function dataForToString() {
        foreach ($this->dataForParse() as $sample) {
            yield [$sample[0]];
        }
    }

    /**
     * @dataProvider dataForToString
     */
    public function testToString(string $uriStr) {
        $uri = new Uri($uriStr);
        $this->assertSame($uriStr, $uri->toString(false));
    }
    
    public function dataForAppended_NormalExamples() {
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
     * @dataProvider dataForAppended_NormalExamples
     */
    public function testAppended_NormalExamples($relativeRef, $expected) {
        $uri = new Uri('http://a/b/c/d;p?q');
        $uri1 = clone $uri;
        $targetUri = $uri->appended($relativeRef);
        $this->assertSame($expected, $targetUri->toString(false));
        $this->assertEquals($uri1, $uri); // $uri should not be changed
    }
    
    public function dataForAppended_AbnormalExamples() {
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
     * @dataProvider dataForAppended_AbnormalExamples
     */
    public function testAppend_AbnormalExamples($relativeRef, $expected) {
        $uri = new Uri('http://a/b/c/d;p?q');
        $this->assertSame($expected, $uri->appended($relativeRef)->toString(false));
    }
}