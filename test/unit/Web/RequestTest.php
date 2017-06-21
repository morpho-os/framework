<?php declare(strict_types=1);
namespace MorphoTest\Unit\Web;

use Morpho\Test\TestCase;
use Morpho\Web\Request;

class RequestTest extends TestCase {
    private $request;

    public function setUp() {
        $this->request = new Request();
    }

    public function testResponse_ReturnsTheSameInstance() {
        $response = $this->request->response();
        $this->assertSame($response, $this->request->response());
    }

    public function testIsAjax_BoolAccessor() {
        $this->checkBoolAccessor([$this->request, 'isAjax'], false);
    }

    public function testIsAjax_ByDefaultReturnsValueFromHeaders() {
        $this->request->headers()->addHeaderLine('X_REQUESTED_WITH', 'XMLHttpRequest');
        $this->assertTrue($this->request->isAjax());
        $this->request->headers()->clearHeaders();
        $this->assertFalse($this->request->isAjax());
    }

    public function testInternalParamAccessors() {
        $this->assertNull($this->request->internalParam('foo'));
        $this->assertEquals('default', $this->request->internalParam('foo', 'default'));
        $this->assertNull($this->request->setInternalParam('foo', 'bar'));
        $this->assertEquals('bar', $this->request->internalParam('foo'));
        $this->assertEquals('bar', $this->request->internalParam('foo', 'default'));
        $this->assertNull($this->request->unsetInternalParam('foo'));
        $this->assertNull($this->request->internalParam('foo'));
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
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $_SERVER['HTTP_HOST'] = 'blog.example.com:8042';
        $_SERVER['REQUEST_URI'] = '/top.htm?page=news&skip=10';
        $_SERVER['QUERY_STRING'] = 'page=news&skip=10';
        $uri = $this->request->uri();
        $this->assertEquals('https://blog.example.com:8042/top.htm?page=news&skip=10', $uri->__toString());
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
}