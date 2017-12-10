<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use Morpho\Test\TestCase;
use Morpho\Web\UriParseException;
use Morpho\Web\UriParser;

class UriParserTest extends TestCase {
    public function dataForParse() {
        return [
            // Cases for authority
            [
                'http://localhost/', // ends with '/'
                [
                    'scheme'    => 'http',
                    'authority' => 'localhost',
                    'path'      => '/',
                    'query'     => null,
                    'fragment'  => null,
                ],
            ],
            [
                'http://localhost?', // ends with '?'
                [
                    'scheme'    => 'http',
                    'authority' => 'localhost',
                    'path'      => '',
                    'query'     => '',
                    'fragment'  => null,
                ],
            ],
            [
                'http://localhost#', // ends with '#'
                [
                    'scheme'    => 'http',
                    'authority' => 'localhost',
                    'path'      => '',
                    'query'     => null,
                    'fragment'  => '',
                ]
            ],
            [
                'http://localhost',  // ends with end of URI
                [
                    'scheme'    => 'http',
                    'authority' => 'localhost',
                    'path'      => '',
                    'query'     => null,
                    'fragment'  => null,
                ]
            ],
            // Cases for path
            [
                'foo://info.example.com?fred',
                [
                    'scheme'=> 'foo',
                    'authority' => 'info.example.com',
                    'path' => '',
                    'query' => 'fred',
                    'fragment' => null,
                ],
            ],
            [
                'foo://info.example.com/system/module#test',
                [
                    'scheme'=> 'foo',
                    'authority' => 'info.example.com',
                    'path' => '/system/module',
                    'query' => null,
                    'fragment' => 'test',
                ],
            ],
            // Modified samples from RFC 3986
            [
                'http://www.ics.uci.edu/pub/ietf/uri/?foo=bar&baz=quak#Related',
                [
                    'scheme' => 'http',
                    'authority' => 'www.ics.uci.edu',
                    'path' => '/pub/ietf/uri/',
                    'query' => 'foo=bar&baz=quak',
                    'fragment' => 'Related',
                ],
            ],
            // Samples from RFC 3986
            [
                'ftp://ftp.is.co.za/rfc/rfc1808.txt',
                [
                    'scheme' => 'ftp',
                    'authority' => 'ftp.is.co.za',
                    'path' => '/rfc/rfc1808.txt',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            [
                'http://www.ietf.org/rfc/rfc2396.txt',
                [
                    'scheme' => 'http',
                    'authority' => 'www.ietf.org',
                    'path' => '/rfc/rfc2396.txt',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            [
                'ldap://[2001:db8::7]/c=GB?objectClass?one',
                [
                    'scheme' => 'ldap',
                    'authority' => '[2001:db8::7]',
                    'path' => '/c=GB',
                    'query' => 'objectClass?one',
                    'fragment' => null,
                ],
            ],
            [
                'mailto:John.Doe@example.com',
                [
                    'scheme' => 'mailto',
                    'authority' => null,
                    'path' => 'John.Doe@example.com',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            [
                'news:comp.infosystems.www.servers.unix',
                [
                    'scheme' => 'news',
                    'authority' => null,
                    'path' => 'comp.infosystems.www.servers.unix',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            [
                'tel:+1-816-555-1212',
                [
                    'scheme' => 'tel',
                    'authority' => null,
                    'path' => '+1-816-555-1212',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            [
                'telnet://192.0.2.16:80/',
                [
                    'scheme' => 'telnet',
                    'authority' => '192.0.2.16:80',
                    'path' => '/',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            [
                'urn:oasis:names:specification:docbook:dtd:xml:4.1.2',
                [
                    'scheme' => 'urn',
                    'authority' => null,
                    'path' => 'oasis:names:specification:docbook:dtd:xml:4.1.2',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            // Other cases
            [
                'foo://example.com:8042/over/there?name=ferret#nose',
                [
                    'scheme' => 'foo',
                    'authority' => 'example.com:8042',
                    'path' => '/over/there',
                    'query' => 'name=ferret',
                    'fragment' => 'nose',
                ]
            ],
            [
                'http://привет.мир/базовый/путь?первый=123&второй=quak#таблица-1',
                [
                    'scheme' => 'http',
                    'authority' => 'привет.мир',
                    'path' => '/базовый/путь',
                    'query' => 'первый=123&второй=quak',
                    'fragment' => 'таблица-1',
                ],
            ],
            [
                // A traditional file URI for a local file with an empty authority.
                'file:///home/user/.vimrc',
                [
                    'scheme' => 'file',
                    'authority' => '', // empty authority
                    'path' => '/home/user/.vimrc',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            [
                '//example.com',
                [
                    'scheme' => '',
                    'authority' => 'example.com',
                    'path' => '',
                    'query' => null,
                    'fragment' => null,
                ]
            ],
            // Samples from RFC 8089:
            [
                // The minimal representation of a local file with no authority field and an absolute path that begins with a slash "/".
                'file:/path/to/file',
                [
                    'scheme' => 'file',
                    'authority' => null, // no authority
                    'path' => '/path/to/file',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            [
                // The minimal representation of a local file with no authority field and an absolute path that begins with a slash "/".
                'file://host.example.com/path/to/file',
                [
                    'scheme' => 'file',
                    'authority' => 'host.example.com',
                    'path' => '/path/to/file',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataForParse
     */
    public function testParse(string $uriStr, array $expected) {
        $this->checkParse($uriStr, $expected);
    }

    public function dataForParseOnlyAuthority_ValidCases() {
        yield [
            'user:password@[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]:80',
            [
                'userInfo' => 'user:password',
                'host' => '[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]',
                'port' => 80,
            ],
        ];
        yield [
            'user:pass^word@[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]',
            [
                'userInfo' => 'user:pass^word',
                'host' => '[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]',
                'port' => null,
            ],
        ];
        yield [
            '127.0.0.1',
            [
                'userInfo' => null,
                'host' => '127.0.0.1',
                'port' => null,
            ],
        ];
        yield [
            '127.0.0.1:80',
            [
                'userInfo' => null,
                'host' => '127.0.0.1',
                'port' => 80,
            ],
        ];
        yield [
            '@127.0.0.1:80',
            [
                'userInfo' => '',
                'host' => '127.0.0.1',
                'port' => 80,
            ],
        ];

        // IPv6, some cases found in OpenJDK and RFCs.
        yield [
            '[1080:0:0:0:8:800:200C:417A]',
            [
                'userInfo' => null,
                'host' => '[1080:0:0:0:8:800:200C:417A]',
                'port' => null,
            ],
        ];
        yield [
            '[3ffe:2a00:100:7031::1]',
            [
                'userInfo' => null,
                'host' => '[3ffe:2a00:100:7031::1]',
                'port' => null,
            ],
        ];
        yield [
            '[1080::8:800:200C:417A]',
            [
                'userInfo' => null,
                'host' => '[1080::8:800:200C:417A]',
                'port' => null,
            ],
        ];
        yield [
            '[::192.9.5.5]',
            [
                'userInfo' => null,
                'host' => '[::192.9.5.5]',
                'port' => null,
            ],
        ];
        yield [
            '[::FFFF:129.144.52.38]',
            [
                'userInfo' => null,
                'host' => '[::FFFF:129.144.52.38]',
                'port' => null,
            ],
        ];
        yield [
            '[::FFFF:129.144.52.38]:80',
            [
                'userInfo' => null,
                'host' => '[::FFFF:129.144.52.38]',
                'port' => 80,
            ],
        ];
        yield [
            '[2010:836B:4179::836B:4179]',
            [
                'userInfo' => null,
                'host' => '[2010:836B:4179::836B:4179]',
                'port' => null,
            ],
        ];
        yield [
            '[::1]',
            [
                'userInfo' => null,
                'host' => '[::1]',
                'port' => null,
            ],
        ];
    }

    /**
     * @dataProvider dataForParseOnlyAuthority_ValidCases
     */
    public function testParseOnlyAuthority_ValidCases($authority, $expected) {
        $parts = (new UriParser())->parseOnlyAuthority($authority);
        $this->assertSame($expected['userInfo'], $parts['userInfo']);
        $this->assertSame($expected['host'], $parts['host']);
        $this->assertSame($expected['port'], $parts['port']);
        $this->assertCount(3, $parts);
    }

    public function dataForParseOnlyAuthority_InvalidCases() {
        yield [
            '',
        ];
    }

    /**
     * @dataProvider dataForParseOnlyAuthority_InvalidCases
     */
    public function testParseOnlyAuthority_InvalidCases($authority) {
        $parser = new UriParser();
        $this->expectException(UriParseException::class, 'Invalid authority');
        $parser->parseOnlyAuthority($authority);
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

    private function checkParse(string $uriStr, array $expected): void {
        $uri = (new UriParser())->__invoke($uriStr);
        $this->assertSame($expected['scheme'], $uri->scheme());
        $this->assertSame($expected['authority'], $uri->authority());
        $this->assertSame($expected['path'], $uri->path());
        $this->assertSame($expected['query'], $uri->query());
        $this->assertSame($expected['fragment'], $uri->fragment());
    }
}