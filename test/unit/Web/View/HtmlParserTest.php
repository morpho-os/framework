<?php
declare(strict_types=1);

namespace MorphoTest\Web\View;

use Morpho\Di\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\Request;
use Morpho\Web\Uri;
use Morpho\Web\View\HtmlParser;

class HtmlParserTest extends TestCase {
    public function testPrependUriWithBasePath_MustNotProcessUriWhichStartsWithPhp() {
        $serviceManager = new ServiceManager();
        $request = $this->createMock(Request::class);

        $uriStr = '<?= $this->moduleUri() ?>/lib/jquery.js';

        $uri = $this->createMock(Uri::class);
        $uri->expects($this->never())
            ->method('prependWithBasePath');

        $request->expects($this->never())
            ->method('uri')
            ->willReturn($uri);
        $serviceManager->set('request', $request);
        $htmlParser = new class($serviceManager) extends HtmlParser {
            // This method is important for child classes so we don't leave it protected
            // but make here public for testing
            public function prependUriWithBasePath(string $uri): string {
                return parent::prependUriWithBasePath($uri);
            }
        };
        $this->assertEquals($uriStr, $htmlParser->prependUriWithBasePath($uriStr));
    }

    public function testPrependUriWithBasePath_MustProcessUriWhichDoesNotStartsWithPhp() {
        $serviceManager = new ServiceManager();
        $request = $this->createMock(Request::class);

        $uriStr = '/foo/bar/<?= $this->moduleUri() ?>/lib/jquery.js';

        $uri = $this->createMock(Uri::class);
        $uri->expects($this->once())
            ->method('prependWithBasePath')
            ->with($this->equalTo($uriStr))
            ->will($this->returnValue($uriStr));

        $request->expects($this->once())
            ->method('uri')
            ->willReturn($uri);
        $serviceManager->set('request', $request);
        $htmlParser = new class($serviceManager) extends HtmlParser {
            // This method is important for child classes so we don't leave it protected
            // but make here public for testing
            public function prependUriWithBasePath(string $uri): string {
                return parent::prependUriWithBasePath($uri);
            }
        };
        $this->assertEquals($uriStr, $htmlParser->prependUriWithBasePath($uriStr));
    }
}

