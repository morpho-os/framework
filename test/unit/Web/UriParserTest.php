<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use Morpho\Test\TestCase;
use Morpho\Web\Uri;
use Morpho\Web\UriParseException;
use Morpho\Web\UriParser;

/**
 * @TODO: Complement this class with tests from the \ZendTest\Uri\UriTest and the \ZendTest\Uri\HttpTest
 * This class complemented with tests from the \ZendTest\Uri\UriTest class and \ZendTest\Uri\HttpTest classes.
 * @see https://github.com/zendframework/zend-http for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license https://github.com/zendframework/zend-http/blob/master/LICENSE.md New BSD License
 */
class UriParserTest extends TestCase {
    public function testValidateComponentsAccessor() {
        $this->checkBoolAccessor([new UriParser(), 'validateComponents'], false);
    }

    public function dataForParse() {
        return [
            // Modified samples from RFC 3986
            [
                [
                    'uri' => 'http://www.ics.uci.edu/pub/ietf/uri/?foo=bar&baz=quak#Related',
                    'scheme' => 'http',
                    'authority' => 'www.ics.uci.edu',
                    'path' => '/pub/ietf/uri/',
                    'query' => 'foo=bar&baz=quak',
                    'fragment' => 'Related',
                ],
            ],
            // Samples from RFC 3986
            [
                [
                    'uri' => 'ftp://ftp.is.co.za/rfc/rfc1808.txt',
                    'scheme' => 'ftp',
                    'authority' => 'ftp.is.co.za',
                    'path' => '/rfc/rfc1808.txt',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            [
                [
                    'uri' => 'http://www.ietf.org/rfc/rfc2396.txt',
                    'scheme' => 'http',
                    'authority' => 'www.ietf.org',
                    'path' => '/rfc/rfc2396.txt',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            [
                [
                    'uri' => 'ldap://[2001:db8::7]/c=GB?objectClass?one',
                    'scheme' => 'ldap',
                    'authority' => '[2001:db8::7]',
                    'path' => '/c=GB',
                    'query' => 'objectClass?one',
                    'fragment' => null,
                ],
            ],
            [
                [
                    'uri' => 'mailto:John.Doe@example.com',
                    'scheme' => 'mailto',
                    'authority' => '',
                    'path' => 'John.Doe@example.com',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            [
                [
                    'uri' => 'news:comp.infosystems.www.servers.unix',
                    'scheme' => 'news',
                    'authority' => '',
                    'path' => 'comp.infosystems.www.servers.unix',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            [
                [
                    'uri' => 'tel:+1-816-555-1212',
                    'scheme' => 'tel',
                    'authority' => '',
                    'path' => '+1-816-555-1212',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            [
                [
                    'uri' => 'telnet://192.0.2.16:80/',
                    'scheme' => 'telnet',
                    'authority' => '192.0.2.16:80',
                    'path' => '/',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            [
                [
                    'uri' => 'urn:oasis:names:specification:docbook:dtd:xml:4.1.2',
                    'scheme' => 'urn',
                    'authority' => '',
                    'path' => 'oasis:names:specification:docbook:dtd:xml:4.1.2',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            [
                [
                    'uri' => 'foo://example.com:8042/over/there?name=ferret#nose',
                    'scheme' => 'foo',
                    'authority' => 'example.com:8042',
                    'path' => '/over/there',
                    'query' => 'name=ferret',
                    'fragment' => 'nose',
                ]
            ],
            [
                [
                    'uri' => 'http://привет.мир/базовый/путь?первый=123&второй=quak#таблица-1',
                    'scheme' => 'http',
                    'authority' => 'привет.мир',
                    'path' => '/базовый/путь',
                    'query' => 'первый=123&второй=quak',
                    'fragment' => 'таблица-1',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataForParse
     */
    public function testParse(array $uriMeta) {
        $this->checkParse($uriMeta);
    }

    public function testSchemeAccessors() {
        $this->checkAccessors([new Uri(), 'scheme'], null, 'http');
    }

    public function testPathAccessors() {
        $this->checkAccessors([new Uri(), 'path'], null, '/foo/bar/baz');
    }

    public function testHostAccessors() {
        $this->checkAccessors([new Uri(), 'host'], null, 'localhost');
    }

    public function testPortAccessors() {
        $this->checkAccessors([new Uri(), 'port'], null, 123);
    }

    public function dataForParseScheme() {
        return [
            [
                '', false,
            ],
            [
                'http', true,
            ],
            [
                'HTTP', true,
            ],
            [
                'h', true,
            ],
            [
                'H', true,
            ],
            [
                'q^u', false,
            ],
        ];
    }

    /**
     * @dataProvider dataForParseScheme
     */
    public function testParseScheme(string $scheme, bool $isValid) {
        $uriParser = new UriParser();
        $uriParser->validateComponents(true);
        $uriStr = $scheme . '://localhost';
        if (!$isValid) {
            $this->expectException(UriParseException::class, 'Invalid scheme');
            $uriParser->__invoke($uriStr);
        } else {
            $this->assertSame($scheme, $uriParser->__invoke($uriStr)->scheme());
        }
    }

    public function dataForParseAuthority() {
        // The authority component is preceded by a double slash ("//") and is terminated by the next slash ("/"), question mark ("?"), or number sign ("#") character, or by the end of the URI.
        return [
            [
                [
                    'uri'       => 'http://localhost/', // ends with '/'
                    'scheme'    => 'http',
                    'authority' => 'localhost',
                    'path'      => '/',
                    'query'     => null,
                    'fragment'  => null,
                ],
            ],
            [
                [
                    'uri'       => 'http://localhost?', // ends with '?'
                    'scheme'    => 'http',
                    'authority' => 'localhost',
                    'path'      => '',
                    'query'     => '',
                    'fragment'  => null,
                ],
            ],
            [
                [
                    'uri'       => 'http://localhost#', // ends with '#'
                    'scheme'    => 'http',
                    'authority' => 'localhost',
                    'path'      => '',
                    'query'     => '',
                    'fragment'  => '',
                ]
            ],
            [
                [
                    'uri'       => 'http://localhost',  // ends with end of URI
                    'scheme'    => 'http',
                    'authority' => 'localhost',
                    'path'      => '',
                    'query'     => null,
                    'fragment'  => null,
                ]
            ],
        ];
    }

    /**
     * @dataProvider dataForParseAuthority
     */
    public function testParseAuthority(array $uriMeta) {
        $this->checkParse($uriMeta);
    }

    public function testParsePath() {
/*
   <mailto:fred@example.com> has a path of "fred@example.com", whereas
   the URI <foo://info.example.com?fred> has an empty path.
*/
        $this->markTestIncomplete();
    }

    public function testParseTheSameUriTwice() {
        $uriStr = 'foo://example.com:8042/over/there?name=ferret#nose';
        $uriParser = new UriParser();
        for ($i = 0; $i < 2; $i++) {
            $uri = $uriParser->__invoke($uriStr);
            $this->assertSame('foo', $uri->scheme());
            $this->assertSame('example.com:8042', $uri->authority());
            $this->assertSame('/over/there', $uri->path());
            $this->assertSame('name=ferret', $uri->query());
            $this->assertSame('nose', $uri->fragment());
        }
    }

    private function checkParse(array $uriMeta): void {
        $uri = (new UriParser())->__invoke($uriMeta['uri']);
        $this->assertSame($uriMeta['scheme'], $uri->scheme());
        $this->assertSame($uriMeta['authority'], $uri->authority());
        $this->assertSame($uriMeta['path'], $uri->path());
        $this->assertSame($uriMeta['query'], $uri->query());
        $this->assertSame($uriMeta['fragment'], $uri->fragment());
    }
}