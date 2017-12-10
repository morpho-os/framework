<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use Morpho\Test\TestCase;
use Morpho\Web\Uri;

class UriTest extends TestCase {
    public function testSchemeAccessors() {
        $this->checkAccessors([new Uri(), 'scheme'], null, 'http');
    }

    public function testAuthorityAccessors() {
        $this->checkAccessors([new Uri(), 'authority'], null, 'example.com');
    }

    public function testSetAuthority_ChangesHostAndPort() {
        $uri = new Uri();
        $host = 'example.com';
        $port = 80;
        $uri->setAuthority("$host:$port");
        $this->assertSame($host, $uri->host());
        $this->assertSame($port, $uri->port());
    }

    public function testSetAuthorityParts_ChangesAuthority() {
        $uri = new Uri();
        $uri->setUserInfo('foo:bar');
        $uri->setHost('example.com');
        $uri->setPort(80);
        $this->assertSame('foo:bar@example.com:80', $uri->authority());
    }

    public function testUserInfoAccessors() {
        $this->checkAccessors([new Uri(), 'userInfo'], null, 'name:1234');
    }

    public function testHostAccessors() {
        $this->checkAccessors([new Uri(), 'host'], null, 'example.com');
    }

    public function testPortAccessors() {
        $this->checkAccessors([new Uri(), 'port'], null, 123);
    }

    public function testPathAccessors() {
        $this->checkAccessors([new Uri(), 'path'], null, '/foo/bar/baz');
    }

    public function testQueryAccessors() {
        $this->checkAccessors([new Uri(), 'query'], null, 'foo=bar&test=123');
    }

    public function testFragmentAccessors() {
        $this->checkAccessors([new Uri(), 'fragment'], null, 'toc');
    }
    
    public function dataForToString() {
        // Cases for authority
        yield [
            'http://localhost/', // ends with '/'
        ];
        yield [
            'http://localhost?', // ends with '?'
        ];
        yield [
            'http://localhost#', // ends with '#'
        ];
        yield [
            'http://localhost',  // ends with end of URI
        ];
        // Cases for path
        yield [
            'foo://info.example.com?fred',
        ];
        yield [
            'foo://info.example.com/system/module#test',
        ];
        // Modified samples from RFC 3986
        yield [
            'http://www.ics.uci.edu/pub/ietf/uri/?foo=bar&baz=quak#Related',
        ];
        // Samples from RFC 3986
        yield [
            'ftp://ftp.is.co.za/rfc/rfc1808.txt',
        ];
        yield [
            'http://www.ietf.org/rfc/rfc2396.txt',
        ];
        yield [
            'ldap://[2001:db8::7]/c=GB?objectClass?one',
        ];
        yield [
            'mailto:John.Doe@example.com',
        ];
        yield [
            'news:comp.infosystems.www.servers.unix',
        ];
        yield [
            'tel:+1-816-555-1212',
        ];
        yield [
            'telnet://192.0.2.16:80/',
        ];
        yield [
            'urn:oasis:names:specification:docbook:dtd:xml:4.1.2',
        ];
        // Other cases
        yield [
            'foo://example.com:8042/over/there?name=ferret#nose',
        ];
        yield [
            'http://привет.мир/базовый/путь?первый=123&второй=quak#таблица-1',
        ];
        yield [
            'file:///home/user/.vimrc',
        ];
        yield [
            '//example.com',
        ];
        yield [
            'file:/path/to/file',
        ];
        yield [
            'file://host.example.com/path/to/file',
        ];
    }

    /**
     * @dataProvider dataForToString
     */
    public function testToString(string $uriStr) {
        $uri = new Uri($uriStr);
        $this->assertSame($uriStr, (string)$uri);
    }
}