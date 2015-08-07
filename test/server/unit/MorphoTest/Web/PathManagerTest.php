<?php
namespace MorphoTest\Web;

use Morpho\Test\TestCase;
use Morpho\Web\PathManager;

class PathManagerTest extends TestCase {
    public function setUp() {
        $request = $this->createRequestMock();
        $siteManager = $this->createSiteManager();
        $this->pathManager = new PathManager($request, $siteManager);
    }

    public function testInterfaces() {
        $this->assertInstanceOf('\Morpho\Di\IServiceManagerAware', $this->pathManager);
    }

    public function testWebDirPathAccessors() {
        $this->pathManager->setWebDirPath(__DIR__);
        $this->assertEquals(str_replace('\\', '/', __DIR__), $this->pathManager->getWebDirPath());
    }

    public function testUri_EmptyUri() {
        $request = $this->createRequestMock();
        $request->expects($this->any())
            ->method('getRequestUri')
            ->will($this->returnValue('/foo/bar/baz?one=1&two=2'));
        $pathManager = new PathManager($request, $this->createSiteManager());
        $this->assertEquals('/foo/bar/baz?one=1&two=2', $pathManager->uri());
    }

    public function testUri_RelativeUri() {
        $request = $this->createRequestMock();
        $request->expects($this->any())
            ->method('getBaseUri')
            ->will($this->returnValue('/base/path'));
        $pathManager = new PathManager($request, $this->createSiteManager());
        $expectedUri = '/base/path/foo/bar/baz?one=1&two=2#fragment';
        $this->assertEquals($expectedUri, $pathManager->uri('foo/bar/baz?one=1&two=2#fragment'));
        $this->assertEquals($expectedUri, $pathManager->uri('/foo/bar/baz?one=1&two=2#fragment'));
    }

    public function testUri_AbsoluteUri() {
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
    }

    public function testCacheDirPathAccessors() {
        $this->assertEquals(str_replace('\\', '/', __DIR__) . '/my-site/cache', $this->pathManager->getCacheDirPath());
        $this->pathManager->setCacheDirPath(__DIR__);
        $this->assertEquals(str_replace('\\', '/', __DIR__), $this->pathManager->getCacheDirPath());
    }

    public function testLogDirPathAccessors() {
        $this->assertEquals(str_replace('\\', '/', __DIR__) . '/my-site/log', $this->pathManager->getLogDirPath());
        $this->pathManager->setLogDirPath(__DIR__);
        $this->assertEquals(str_replace('\\', '/', __DIR__), $this->pathManager->getLogDirPath());
    }

    private function createRequestMock() {
        return $this->mock('\Morpho\Web\Request');
    }

    private function createSiteManager() {
        $siteManager = $this->mock('\Morpho\Web\SiteManager');
        $siteManager->expects($this->any())
            ->method('getCurrentSiteName')
            ->will($this->returnValue('my-site'));
        $siteManager->expects($this->any())
            ->method('getAllSiteDirPath')
            ->will($this->returnValue(str_replace('\\', '/', __DIR__)));
        return $siteManager;
    }
}

class UriMock {
    public function getScheme() {
        return 'https';
    }

    public function getHost() {
        return 'subdomain.domain.com';
    }
}
