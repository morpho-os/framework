<?php
namespace MorphoTest\Web;

use Morpho\Test\TestCase;
use Morpho\Web\Request;

class RequestTest extends TestCase {
    public function dataForIsHttpMethod() {
        $data = [];
        foreach (Request::getAllMethods() as $httpMethod) {
            $data[] = [$httpMethod];
        }
        return $data;
    }

    /**
     * @dataProvider dataForIsHttpMethod
     */
    public function testIsHttpMethod($httpMethod) {
        $request = new Request();
        $request->setMethod($httpMethod);
        $this->assertTrue($request->{'is' . $httpMethod . 'Method'}());
    }

    public function testUriAccessors_UriAsString() {
        $request = new Request();
        $uri = '/foo/bar/baz?one=1&two=2#test';
        $request->setUri($uri);
        $this->assertEquals($uri, (string)$request->getUri());
    }

    public function testIsDispatched() {
        $this->assertBoolAccessor([new Request, 'isDispatched'], false);
    }

    public function dataForTrim() {
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
     * @dataProvider dataForTrim
     */
    public function testTrim($httpMethod) {
        $val = '   baz  ';
        if ($httpMethod === 'GET') {
            $_GET['foo']['bar'] = $val;
        } else {
            $_POST['foo']['bar'] = $val;
        }

        $request = new Request();

        $this->assertEquals('baz', $request->{'get' . $httpMethod}('foo')['bar']);

        $this->assertEquals($val, $request->{'get' . $httpMethod}('foo', false)['bar']);
    }

    public function testDoesNotChangeGlobals() {
        $_GET['foo'] = ['one' => 1];

        $request = new Request();

        $v = $request->getGet('foo');
        $v['one'] = 2;

        $this->assertEquals(['one' => 1], $_GET['foo']);
    }

    public function testGetReturnsNullWhenNotSet() {
        $request = new Request();
        $this->assertNull($request->getGet('foo', true));
        $this->assertNull($request->getGet('foo', false));
    }

    public function dataForGetRequestArgsArray() {
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
     * @dataProvider dataForGetRequestArgsArray
     */
    public function testGetRequestArgsArray($httpMethod) {
        $_SERVER['REQUEST_METHOD'] = $httpMethod;
        $request = new Request();

        $GLOBALS['_' . $httpMethod]['foo']['bar'] = 'baz';

        $this->assertEquals(
            ['non' => null, 'foo' => ['bar' => 'baz']],
            $request->getArgs(['foo', 'non'])
        );
    }
}
