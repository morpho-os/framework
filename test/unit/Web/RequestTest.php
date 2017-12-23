<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use Morpho\Base\IFn;
use Morpho\Test\TestCase;
use Morpho\Web\BadRequestException;
use Morpho\Web\Request;

class RequestTest extends TestCase {
    /**
     * @var Request
     */
    private $request;

    public function setUp() {
        $this->request = $this->newRequest();
    }

    public function testResponse_ReturnsTheSameInstance() {
        $response = $this->request->response();
        $this->assertSame($response, $this->request->response());
    }

    public function testIsAjax_BoolAccessor() {
        $this->checkBoolAccessor([$this->request, 'isAjax'], false);
    }

    public function testIsAjax_ByDefaultReturnsValueFromHeaders() {
        $this->request->headers()['X-Requested-With'] = 'XMLHttpRequest';
        $this->assertTrue($this->request->isAjax());
        $this->request->headers()->exchangeArray([]);
        $this->assertFalse($this->request->isAjax());
    }

    public function dataForSettingHeadersThroughServerVars() {
        yield [true];
        yield [false];
    }

    /**
     * @dataProvider dataForSettingHeadersThroughServerVars
     */
    public function testSettingHeadersThroughServerVars($useGlobalServerVar) {
        $serverVars = [
            "HOME" => "/foo/bar",
            "USER" => "user-name",
            "HTTP_CACHE_CONTROL" => "max-age=0",
            "HTTP_CONNECTION" => "keep-alive",
            "HTTP_UPGRADE_INSECURE_REQUESTS" => "1",
            "HTTP_COOKIE" => "TestCookie=something+from+somewhere",
            "HTTP_ACCEPT_LANGUAGE" => "en-US,en;q=0.5",
            "HTTP_ACCEPT_ENCODING" =>  "gzip, deflate",
            "HTTP_USER_AGENT" => "Test user agent",
            "REDIRECT_STATUS" => "200",
            "HTTP_HOST" => "localhost",
            "SERVER_NAME" => "localhost",
            "SERVER_ADDR" => "127.0.0.1",
            "HTTP_FOO" => "Bar",
            "SERVER_PORT" => "80",
            "REMOTE_PORT" => "12345",
            "HTTP_ACCEPT" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "SCRIPT_NAME" => "/test.php",
            "CONTENT_LENGTH" => "4521",
            "CONTENT_TYPE"   => "",
            "REQUEST_METHOD" => "POST",
            "CONTENT_MD5" => "Q2hlY2sgSW50ZWdyaXR5IQ==",
        ];
        $expectedHeaders = [
            'Cache-Control' => $serverVars['HTTP_CACHE_CONTROL'],
            'Connection' => $serverVars['HTTP_CONNECTION'],
            'Upgrade-Insecure-Requests' => $serverVars['HTTP_UPGRADE_INSECURE_REQUESTS'],
            'Accept-Language' => $serverVars['HTTP_ACCEPT_LANGUAGE'],
            'Accept-Encoding' => $serverVars['HTTP_ACCEPT_ENCODING'],
            'User-Agent' => $serverVars['HTTP_USER_AGENT'],
            'Host' => $serverVars['HTTP_HOST'],
            'Foo' => $serverVars['HTTP_FOO'],
            'Accept' => $serverVars['HTTP_ACCEPT'],
            'Content-Length' => $serverVars['CONTENT_LENGTH'],
            'Content-Type' => $serverVars['CONTENT_TYPE'],
            'Content-MD5' => $serverVars['CONTENT_MD5'],
        ];
        if ($useGlobalServerVar) {
            $_SERVER = $serverVars;
            $request = $this->newRequest(null);
        } else {
            $request = $this->newRequest($serverVars);
        }
        $this->assertSame($expectedHeaders, $request->headers()->getArrayCopy());
    }

    public function testHeadersAccessors() {
        $this->assertSame([], $this->request->headers()->getArrayCopy());
        $this->request->headers()['foo'] = 'bar';
        $this->assertSame('bar', $this->request->headers()['foo']);
        $this->assertSame(['foo' => 'bar'], $this->request->headers()->getArrayCopy());
    }

    public function testParamAccessors() {
        $this->assertEquals(new \ArrayObject(), $this->request->params());

        $params = $this->request->params();
        $params['foo'] = 'bar';

        $this->assertSame('bar', $this->request->params()['foo']);
    }

    public function testHandlerAccessors() {
        $handler = ['foo', 'bar', 'baz'];
        $this->request->setHandler($handler);
        $this->assertEquals($handler, $this->request->handler());
    }

    public function testHasQuery() {
        $this->assertFalse($this->request->hasQuery('some'));
        $_GET['some'] = 'ok';
        $this->assertTrue($this->request->hasQuery('some'));
    }

    public function testHasPost() {
        $this->assertFalse($this->request->hasPost('some'));
        $_POST['some'] = 'ok';
        $this->assertTrue($this->request->hasPost('some'));
    }

    public function testUri_HasValidComponents() {
        $trustedProxyIp = '127.0.0.3';
        $_SERVER['REMOTE_ADDR'] = $trustedProxyIp;
        $this->request->setTrustedProxyIps([$trustedProxyIp]);
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $_SERVER['HTTP_HOST'] = 'blog.example.com:8042';
        $_SERVER['REQUEST_URI'] = '/top.htm?page=news&skip=10';
        $_SERVER['QUERY_STRING'] = 'page=news&skip=10';
        $_SERVER['SCRIPT_NAME'] = '/';
        $uri = $this->request->uri();
        $this->assertEquals('https://blog.example.com:8042/top.htm?page=news&skip=10', $uri->toStr(true));
    }

    public function dataForIsHttpMethod() {
        $data = [];
        foreach (Request::methods() as $httpMethod) {
            $data[] = [$httpMethod];
        }
        return $data;
    }

    /**
     * @dataProvider dataForIsHttpMethod
     */
    public function testIsHttpMethod($httpMethod) {
        $_SERVER['REQUEST_METHOD'] = 'unknown';
        if ($httpMethod === Request::GET_METHOD) {
            $this->assertTrue($this->request->isGetMethod());
        } else {
            $this->assertFalse($this->request->{'is' . $httpMethod . 'Method'}());
        }
        $this->request->setMethod($httpMethod);
        $this->assertTrue($this->request->{'is' . $httpMethod . 'Method'}());
    }

    public function testIsDispatched() {
        $this->checkBoolAccessor([$this->request, 'isDispatched'], false);
    }

    public function testRoutingParamsAccessors() {
        $this->assertFalse($this->request->hasRoutingParams());
        $this->request->setRoutingParam('foo', 'bar');
        $this->assertTrue($this->request->hasRoutingParams());
        $this->assertEquals(['foo' => 'bar'], $this->request->routingParams());
        $this->request->setRoutingParams([]);
        $this->assertFalse($this->request->hasRoutingParams());
        $this->request->setRoutingParams(['cat' => 'dog']);
        $this->assertEquals(['cat' => 'dog'], $this->request->routingParams());
    }

    public function testTrim_Query() {
        $val = '   baz  ';
        $_GET['foo']['bar'] = $val;
        $this->assertEquals('baz', $this->request->query('foo')['bar']);
        $this->assertEquals($val, $this->request->query('foo', false)['bar']);
    }

    public function testTrim_Post() {
        $val = '   baz  ';
        $_POST['foo']['bar'] = $val;
        $this->assertEquals('baz', $this->request->post('foo')['bar']);
        $this->assertEquals($val, $this->request->post('foo', false)['bar']);
    }

    public function testDoesNotChangeGlobals() {
        $_GET['foo'] = ['one' => 1];

        $v = $this->request->query('foo');
        $v['one'] = 2;

        $this->assertEquals(['one' => 1], $_GET['foo']);
    }

    public function testGetGet_ReturnsNullWhenNotSet() {
        $this->assertNull($this->request->query('foo', true));
        $this->assertNull($this->request->query('foo', false));
    }

    public function dataForGetArgs() {
        return [
            [
                'GET',
            ],
            [
                'POST',
            ],
        ];
    }

    /**
     * @dataProvider dataForGetArgs
     */
    public function testArgs($httpMethod) {
        // @TODO: Test patch, put
        $_SERVER['REQUEST_METHOD'] = $httpMethod;

        $GLOBALS['_' . $httpMethod]['foo']['bar'] = 'baz';

        $this->assertEquals(
            ['non' => null, 'foo' => ['bar' => 'baz']],
            $this->request->args(['foo', 'non'])
        );
    }

    public function testUriInitialization_BasePath() {
        $basePath = '/foo/bar/baz';
        $request = $this->newRequest([
            'REQUEST_URI' => $basePath . '/index.php/one/two',
            'SCRIPT_NAME' => $basePath . '/index.php'
        ]);
        $uri = $request->uri();
        $this->assertSame($basePath, $uri->path()->basePath());
    }

    public function dataForUriInitialization_Scheme() {
        yield [false, []];
        yield [true, ['HTTPS' => 'on']];
        yield [false, ['HTTPS' => 'off']];
        yield [false, ['HTTPS' => 'OFF']];
        yield [true, ['HTTP_X_FORWARDED_PROTO' => 'https']];
        yield [true, ['HTTP_X_FORWARDED_PROTO' => 'on']];
        yield [false, ['HTTP_X_FORWARDED_PROTO' => 'off']];
        yield [false, ['HTTP_X_FORWARDED_PROTO' => 'OFF']];
        yield [true, ['HTTP_X_FORWARDED_PROTO' => 'ssl']];
        yield [true, ['HTTP_X_FORWARDED_PROTO' => '1']];
        yield [false, ['HTTP_X_FORWARDED_PROTO' => '']];
    }

    /**
     * @dataProvider dataForUriInitialization_Scheme
     */
    public function testUriInitialization_Scheme($isHttps, $serverVars) {
        $trustedProxyIp = '127.0.0.2';
        $serverVars['REMOTE_ADDR'] = $trustedProxyIp;
        $request = $this->newRequest($serverVars);
        $request->setTrustedProxyIps([$trustedProxyIp]);
        if ($isHttps) {
            $this->assertSame('https', $request->uri()->scheme());
        } else {
            $this->assertSame('http', $request->uri()->scheme());
        }
    }

    public function testUriInitialization_Query() {
        $request = $this->newRequest([
            'REQUEST_URI' => '/',
            'SCRIPT_NAME' => '/index.php',
            'QUERY_STRING' => '',
            'HTTP_HOST' => 'framework',
        ]);
        $uri = $request->uri();
        $this->assertSame('http://framework/', $uri->toStr(true));
    }
    
    public function testUriInitialization_ThrowsBadRequestOnNotAllowedHost() {
        $request = new Request(
            ['HTTP_HOST' => 'malicious'],
            new class implements IFn {
                public function __invoke($value) {
                    return false;
                }
            }
        );
        $this->expectException(BadRequestException::class, 'Invalid URI');
        $request->uri();
    }

    public function testData() {
        $request = $this->newRequest();
        $this->assertSame(['bar' => 'baz'], $request->data(['foo' => ['bar' => ' baz  ']], 'foo'));
    }
    
    public function testMappingPostToPatch() {
        $request = $this->newRequest();
        $data = ['foo' => 'bar', 'baz' => 'abc'];
        $_POST = array_merge($data, ['_method' => Request::PATCH_METHOD]);
        $this->assertTrue($request->isPatchMethod());
        $this->assertSame($data, $request->patch());
    }

    private function newRequest(array $serverVars = null) {
        return new Request(
            $serverVars,
            new class implements IFn {
                public function __invoke($value) {
                    return true;
                }
            }
        );
    }
}