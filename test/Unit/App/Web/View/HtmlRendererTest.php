<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\View;

use Morpho\Ioc\IServiceManager;
use Morpho\Testing\TestCase;
use Morpho\App\Web\Request;
use Morpho\App\Web\Response;
use Morpho\App\Web\View\HtmlRenderer;
use Morpho\App\Web\View\HtmlResult;

class HtmlRendererTest extends TestCase {
    public function testInvoke() {
        $page = new HtmlResult('test');
        $actionResult = new HtmlResult('edit-user', null, $page);

        $response = new Response();
        $response->setStatusCode(Response::OK_STATUS_CODE);
        $response['result'] = $actionResult;

        $viewModuleName = 'foo/bar';
        $pageRendererModuleName = 'abc/test';

        $request = new Request();
        $request->setHandler([
            'module' => 'foo/bar',
            'controllerPath' => 'news'
        ]);
        $request->setResponse($response);

        $serviceManager = $this->createMock(IServiceManager::class);
        $serviceManager->expects($this->any())
            ->method('conf')
            ->willReturn(['view' => ['pageRenderer' => $pageRendererModuleName]]);

        $renderer = new class ($serviceManager) extends HtmlRenderer {
            public $map;
            protected function renderView(string $moduleName, HtmlResult $actionResult): string {
                $renderer = $this->map[$moduleName];
                return $renderer($actionResult);
            }
        };
        $renderer->map[$viewModuleName] = function (HtmlResult $viewArg) use ($actionResult): string {
            $this->assertSame('news/edit-user', $actionResult->path());
            $this->assertSame($actionResult, $viewArg);
            return 'hello';
        };
        $renderer->map[$pageRendererModuleName] = function (HtmlResult $pageArg) use ($page): string {
            $this->assertSame(['body' => 'hello'], $page->vars()->getArrayCopy());
            $this->assertSame($page, $pageArg);
            return 'cat';
        };

        $renderer->__invoke($request);

        $this->assertSame('cat', $response->body());
        $this->assertSame(['Content-Type' => 'text/html;charset=utf-8'], $response->headers()->getArrayCopy());
    }
}
