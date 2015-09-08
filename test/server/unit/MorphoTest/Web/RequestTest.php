<?php
namespace MorphoTest\Web;

use Morpho\Test\TestCase;
use Morpho\Web\Request;

class RequestTest extends TestCase {
    public function setUp() {
        $this->initCliEnv();
        $this->request = new Request();
    }

    public function testCurrentUri_ReturnsUniqueInstance() {
        $this->assertNotSame($this->request->currentUri(), $this->request->currentUri());
    }

    public function testHasGet() {
        $this->assertFalse($this->request->hasGet('some'));
        $_GET['some'] = 'ok';
        $this->assertTrue($this->request->hasGet('some'));
    }

    public function hasPost() {
        $this->assertFalse($this->request->hasPost('some'));
        $_POST['some'] = 'ok';
        $this->assertTrue($this->request->hasPost('some'));
    }

    public function testCurrentUri_HasValidComponents() {
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $_SERVER['HTTP_HOST'] = 'blog.example.com:8042';
        $_SERVER['REQUEST_URI'] = '/top.htm?page=news&skip=10';
        $_SERVER['QUERY_STRING'] = 'page=news&skip=10';
        $uri = $this->request->currentUri();
        $this->assertEquals('https://blog.example.com:8042/top.htm?page=news&skip=10', $uri->__toString());
    }

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
        $this->request->setMethod($httpMethod);
        $this->assertTrue($this->request->{'is' . $httpMethod . 'Method'}());
    }

    public function testIsDispatched() {
        $this->assertBoolAccessor([$this->request, 'isDispatched'], false);
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

        $this->assertEquals('baz', $this->request->{'get' . $httpMethod}('foo')['bar']);

        $this->assertEquals($val, $this->request->{'get' . $httpMethod}('foo', false)['bar']);
    }

    public function testDoesNotChangeGlobals() {
        $_GET['foo'] = ['one' => 1];

        $v = $this->request->getGet('foo');
        $v['one'] = 2;

        $this->assertEquals(['one' => 1], $_GET['foo']);
    }

    public function testGetReturnsNullWhenNotSet() {
        $this->assertNull($this->request->getGet('foo', true));
        $this->assertNull($this->request->getGet('foo', false));
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
    public function testGetArgs($httpMethod) {
        $_SERVER['REQUEST_METHOD'] = $httpMethod;

        $GLOBALS['_' . $httpMethod]['foo']['bar'] = 'baz';

        $this->assertEquals(
            ['non' => null, 'foo' => ['bar' => 'baz']],
            $this->request->getArgs(['foo', 'non'])
        );
    }
}