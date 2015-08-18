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

    public function testUri_EmptyUri() {
        $this->markTestIncomplete();
        /*
        $request = $this->createRequestMock();
        $request->expects($this->any())
            ->method('getRequestUri')
            ->will($this->returnValue('/foo/bar/baz?one=1&two=2'));
        $pathManager = new PathManager($request, $this->createSiteManager());
        $this->assertEquals('/foo/bar/baz?one=1&two=2', $pathManager->uri());
        */
    }

    public function testUri_RelativeUri() {
        $this->markTestIncomplete();
        /*
        $request = $this->createRequestMock();
        $request->expects($this->any())
            ->method('getBaseUri')
            ->will($this->returnValue('/base/path'));
        $pathManager = new PathManager($request, $this->createSiteManager());
        $expectedUri = '/base/path/foo/bar/baz?one=1&two=2#fragment';
        $this->assertEquals($expectedUri, $pathManager->uri('foo/bar/baz?one=1&two=2#fragment'));
        $this->assertEquals($expectedUri, $pathManager->uri('/foo/bar/baz?one=1&two=2#fragment'));
        */
    }

    public function testUri_AbsoluteUri() {
        $this->markTestIncomplete();
        /*
        $request = $this->createRequestMock();
        $request->expects($this->once())
            ->method('getUri')
            ->will($this->returnCallback(function () {
                return new UriMock();
            }));
        $request->expects($this->once())
            ->method('getBaseUri')
            ->will($this->returnValue('/base/path'));
        $pathManager = new PathManager($request, $this->createSiteManager());
        $this->assertEquals('https://subdomain.domain.com/base/path/foo/bar', $pathManager->uri('foo/bar', null, null, ['absolute' => true]));
        */
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

    public function dataForGetData() {
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
     * @dataProvider dataForGetData
     */
    public function testGetData($httpMethod) {
        $_SERVER['REQUEST_METHOD'] = $httpMethod;
        $request = new Request();

        $GLOBALS['_' . $httpMethod]['foo']['bar'] = 'baz';

        $this->assertEquals(
            ['non' => null, 'foo' => ['bar' => 'baz']],
            $request->getData(['foo', 'non'])
        );
    }
}

/*
class UriMock {
    public function getScheme() {
        return 'https';
    }

    public function getHost() {
        return 'subdomain.domain.com';
    }
}
*/