<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace MorphoTest\Unit\Web\View;

use Morpho\Di\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\Request;
use Morpho\Web\Uri;
use Morpho\Web\View\UriProcessor;

class UriProcessorTest extends TestCase {
    public function dataForProcessUrisInTags() {
        yield ['/base/path', '/base/path'];
        yield ['', '/'];
    }

    /**
     * @dataProvider dataForProcessUrisInTags
     */
    public function testProcessUrisInTags(string $expectedBasePath, string $basePath) {
        $html = <<<OUT
    <form action="http://host/news/test1"></form>
    <form action="news/test1"></form>
    <form action="//host/news/test1"></form>
    <form action="/news/test1"></form>
    <form action="<?= 'test' ?>/news/test1"></form>
    <form action="/news/<?= 'test' ?>/test1"></form>
        
    <link href="http://host/css/test1.css">
    <link href="css/test1.css">
    <link href="//host/css/test1.css">
    <link href="/css/test1.css">
    <link href="<?= 'test' ?>/css/test1.css">
    <link href="/css/<?= 'test' ?>/test1.css">
    
    <a href="http://host/css/test1"></a>
    <a href="css/test1"></a>
    <a href="//host/css/test1"></a>
    <a href="/css/test1"></a>
    <a href="<?= 'test' ?>/css/test1"></a>
    <a href="/css/<?= 'test' ?>/test1"></a>
    
    <script src="http://host/js/test1.js"></script>
    <script src="js/test1.js"></script>
    <script src="//host/js/test1.js"></script>
    <script src="/js/test1.js"></script>
    <script src="<?= 'test' ?>/js/test1.js"></script>
    <script src="/js/<?= 'test' ?>/test1.js"></script>
OUT;

        $uri = $this->createConfiguredMock(Uri::class, ['basePath' => $basePath]);

        $request = $this->createMock(Request::class);
        $request->expects($this->any())
            ->method('uri')
            ->willReturn($uri);

        $serviceManager = $this->createMock(ServiceManager::class);
        $serviceManager->expects($this->any())
            ->method('get')
            ->with('request')
            ->willReturn($request);
        $processor = new UriProcessor($serviceManager);

        $processedHtml = $processor->__invoke($html);

        $expected = <<<OUT
    <form action="http://host/news/test1"></form>
    <form action="news/test1"></form>
    <form action="//host/news/test1"></form>
    <form action="$expectedBasePath/news/test1"></form>
    <form action="<?= 'test' ?>/news/test1"></form>
    <form action="$expectedBasePath/news/<?= 'test' ?>/test1"></form>
        
    <link href="http://host/css/test1.css">
    <link href="css/test1.css">
    <link href="//host/css/test1.css">
    <link href="$expectedBasePath/css/test1.css">
    <link href="<?= 'test' ?>/css/test1.css">
    <link href="$expectedBasePath/css/<?= 'test' ?>/test1.css">
    
    <a href="http://host/css/test1"></a>
    <a href="css/test1"></a>
    <a href="//host/css/test1"></a>
    <a href="$expectedBasePath/css/test1"></a>
    <a href="<?= 'test' ?>/css/test1"></a>
    <a href="$expectedBasePath/css/<?= 'test' ?>/test1"></a>
    
    <script src="http://host/js/test1.js"></script>
    <script src="js/test1.js"></script>
    <script src="//host/js/test1.js"></script>
    <script src="$expectedBasePath/js/test1.js"></script>
    <script src="<?= 'test' ?>/js/test1.js"></script>
    <script src="$expectedBasePath/js/<?= 'test' ?>/test1.js"></script>
OUT;

        $this->assertHtmlEquals($expected, $processedHtml);
    }
}