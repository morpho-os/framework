<?php
namespace MorphoTest\Web;

use Morpho\Test\TestCase;
use Morpho\Web\Uri;

class UriTest extends TestCase {
    public function testPrependWithBasePath() {
        $uri = (new Uri())->setBasePath('/base/path');

        $uriString = 'http://example.com/system?bar=baz#some';
        $this->assertEquals($uriString, $uri->prependWithBasePath($uriString));

        $this->assertEquals('/base/path/system?bar=baz#some', $uri->prependWithBasePath('/system?bar=baz#some'));
        $this->assertEquals('/base/path/system?bar=baz#some', $uri->prependWithBasePath('system?bar=baz#some'));
        $this->assertEquals('/base/path?bar=baz#some', $uri->prependWithBasePath('?bar=baz#some'));
    }

    public function testAppendQueryArgs() {
        $uri = new Uri('http://example.com/system?bar=baz#some');
        $this->assertEquals('http://example.com/system?bar=baz&show=top#some', $uri->appendQueryArgs(['show' => 'top'])->__toString());
    }

    public function testQueryArgsToString() {
        $this->assertEquals('foo=bar&empty=0', Uri::queryArgsToString(['foo' => 'bar', 'empty' => 0]));
    }

    public function testStringToQueryArgs() {
        $this->assertEquals(['foo' => 'bar', 'empty' => '0'], Uri::stringToQueryArgs('foo=bar&empty=0'));
        $this->assertEquals([], Uri::stringToQueryArgs(''));
    }

    public function testRemoveQueryArg() {
        $uri = '/system/module/rebuild-routes?redirect=/system/module/rebuild-routes&ok=test';
        $this->assertEquals('/system/module/rebuild-routes?ok=test', (new Uri($uri))->removeQueryArg('redirect')->__toString());

        $uri = '/system/foo';
        $this->assertEquals('/system/foo', (new Uri($uri))->removeQueryArg('redirect'));
    }

    public function dataForHasAuthority() {
        return [
            [
                false,
                '//',
            ],
            [
                false,
                '/system/foo',
            ],
            [
                false,
                'system',
            ],
            [
                false,
                '',
            ],
            [
                false,
                'example.com',
            ],
            [
                true,
                'http://example.com',
            ],
            [
                true,
                '//example.com',
            ],
            [
                true,
                '//example.com/',
            ],
        ];
    }

    /**
     * @dataProvider dataForHasAuthority
     */
    public function testHasAuthority($expected, $uri) {
        $this->assertSame($expected, Uri::hasAuthority($uri));
    }

    public function testRelativeRef() {
        $uri = new Uri('http://example.com/system?bar=baz#some');
        $this->assertEquals('/system?bar=baz#some', $uri->relativeRef());
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
        $encoded = Uri::base64Encode($uri);
        $this->assertRegExp('~^' . Uri::BASE64_URI_REGEXP . '+$~s', $encoded);
        $this->assertSame($uri, Uri::base64Decode($encoded));
    }
}