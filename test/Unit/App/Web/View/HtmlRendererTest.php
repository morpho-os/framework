<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\View;

use Morpho\Testing\TestCase;
use Morpho\App\Web\Request;
use Morpho\App\Web\Response;
use Morpho\App\Web\View\HtmlRenderer;
use Morpho\App\Web\View\Theme;
use Morpho\App\ModuleIndex;
use Morpho\App\ServerModule;
use Morpho\App\Web\ActionResult;

class HtmlRendererTest extends TestCase {
    public function testInvoke() {
        /*
        $page = (new ActionResult())->setPath('test');
        $actionResult = (new ActionResult())
            ->setPath('edit-user')
            ->setPage($page);
         */

        $actionResult = new ActionResult();

        $response = new Response();
        $response->setStatusCode(Response::OK_STATUS_CODE);
        $response['result'] = $actionResult;

        $pageRenderingModule = 'abc/test';
/*
        $request = new Request();
        $request->setHandler();
        $request->setResponse($response);

        $renderer = new class ($request, $theme, $moduleIndex, $pageRenderingModuleName) extends HtmlRenderer {
            public $map;
            protected function renderView(string $moduleName, $actionResult): string {
                $renderer = $this->map[$moduleName];
                return $renderer($actionResult);
            }
        };
        $renderer->map[$viewModuleName] = function ($viewArg) use ($actionResult): string {
            $this->assertSame('news/edit-user', $actionResult->path());
            $this->assertSame($actionResult, $viewArg);
            return 'hello';
        };
        $renderer->map[$pageRenderingModuleName] = function ($pageArg) use ($page): string {
            $this->assertSame(['body' => 'hello'], $page->getArrayCopy());
            $this->assertSame($page, $pageArg);
            return 'cat';
        };

        $renderer->__invoke($request);

         */

        $bodyRenderingModule = 'foo/bar';

        $request = $this->createMock(Request::class);
        $request->expects($this->any())
                ->method('handler')
                ->willReturn([
                    'module' => $bodyRenderingModule,
                    'controllerPath' => 'news',
                    'method' => 'editUser',
                ]);
        $request->expects($this->any())
                ->method('response')
                ->willReturn($response);

        $theme = $this->createMock(Theme::class);
        $theme->expects($this->exactly(2))
              ->method('render')
              ->will($this->returnCallback(function ($actionResult) {
                  if ($actionResult->path() === 'news/edit-user') {
                      return 'This is a body text.';
                  }
                  if ($actionResult->path() === 'index') {
                      return 'This is a <main>' . $actionResult['body'] . '</main> page text.';
                  }
                  throw new \UneexpectedValueException();
              }));

        $moduleIndex = $this->createMock(ModuleIndex::class);
        $moduleIndex->expects($this->any())
                    ->method('module')
                    ->will($this->returnCallback(function ($moduleName) use ($bodyRenderingModule, $pageRenderingModule) {
                        if ($moduleName == $bodyRenderingModule) {
                            return $this->createMock(ServerModule::class);
                        } elseif ($moduleName == $pageRenderingModule) {
                            return $this->createMock(ServerModule::class);
                        }
                        throw new \UneexpectedValueException();
                    }));

        $renderer = new HtmlRenderer($request, $theme, $moduleIndex, $pageRenderingModule);

        $renderer->__invoke($actionResult);

        $this->assertSame('This is a <main>This is a body text.</main> page text.', $response->body());
        $this->assertSame(['Content-Type' => 'text/html;charset=utf-8'], $response->headers()->getArrayCopy());
    }
}
