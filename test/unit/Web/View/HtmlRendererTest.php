<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web\View;

use Morpho\Ioc\IServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\Request;
use Morpho\Web\Response;
use Morpho\Web\View\HtmlRenderer;
use Morpho\Web\View\Page;
use Morpho\Web\View\View;

class HtmlRendererTest extends TestCase {
    public function testInvoke() {
        $page = new Page('test');
        $view = new View('edit');
        $layout = new View('front');
        $page->setLayout($layout);
        $page->setView($view);

        $response = new Response();
        $response->setStatusCode(Response::OK_STATUS_CODE);

        $viewModuleName = 'foo/bar';
        $layoutModuleName = 'abc/test';

        $request = new Request(['page' => $page]);
        $request->setModuleName('foo/bar');
        $request->setControllerName('News');
        $request->setResponse($response);

        $serviceManager = $this->createMock(IServiceManager::class);
        $serviceManager->expects($this->any())
            ->method('config')
            ->willReturn(['view' => ['layoutModule' => $layoutModuleName]]);

        $renderer = new class ($serviceManager) extends HtmlRenderer {
            public $map;
            protected function render(string $moduleName, View $view): string {
                $renderer = $this->map[$moduleName];
                return $renderer($view);
            }
        };
        $renderer->map[$viewModuleName] = function (View $viewArg) use ($view): string {
            $this->assertSame('news', $viewArg->dirPath());
            $this->assertSame($view, $viewArg);
            return 'hello';
        };
        $renderer->map[$layoutModuleName] = function (View $layoutArg) use ($layout): string {
            $this->assertSame(['body' => 'hello'], $layoutArg->vars()->getArrayCopy());
            $this->assertSame($layout, $layoutArg);
            return 'cat';
        };

        $renderer->__invoke($request);

        $this->assertSame('cat', $response->body());
        $this->assertSame(['Content-Type' => 'text/html; charset=UTF-8'], $response->headers()->getArrayCopy());
    }
}