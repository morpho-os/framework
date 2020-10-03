<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\View;

use Morpho\Test\Unit\App\Web\ActionResultTest;
use Morpho\App\Web\View\HtmlResult;
use Morpho\App\Web\View\HtmlRenderer;
use Morpho\App\Web\IActionResult;

class HtmlResultTest extends ActionResultTest {
    public function testInvoke_NotAjax() {
        $response = $this->mkResponse([], false);
        $request = $this->mkRequest($response, false);
        $serviceManager = [
            'request' => $request,
            'htmlRenderer' => function () use (&$called) {
                $called = true;
            },
        ];
        $actionResult = new HtmlResult('foo');
        $response['result'] = $actionResult;

        $actionResult->__invoke($serviceManager);

        $this->assertTrue($called);
    }

    public function testInvoke_Ajax() {
        $response = $this->mkResponse([], false);
        $request = $this->mkRequest($response, true);
        $renderer = $this->createMock(HtmlRenderer::class);
        $renderedBody = 'this is rendered body';
        $renderer->expects($this->once())
                 ->method('renderBody')
                 ->willReturn($renderedBody);
        $renderer->expects($this->never())
                 ->method('renderPage');
        $serviceManager = [
            'request' => $request,
            'htmlRenderer' => $renderer,
        ];
        $actionResult = new HtmlResult('foo');
        $actionResult->allowAjax(true);

        $actionResult->__invoke($serviceManager);

        $this->assertSame($renderedBody, $response->body());
        $this->assertIsObject($response['result']);
    }

    public function testApi() {
        $vars = ['foo' => 'bar'];
        $path = 'edit';
        $view = new HtmlResult('edit', $vars);
        $view->isNotVar = 123;
        $this->assertSame($path, $view->path());
        $this->assertInstanceOf(\ArrayObject::class, $view->vars());
        $this->assertSame($vars, $view->vars()->getArrayCopy());

        $dirPath = $this->getTestDirPath();
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($view->setPath($dirPath));
        $this->assertSame($dirPath, $view->path());
    }

    protected function mkActionResult(): IActionResult {
        return new HtmlResult('foo');
    }
}
