<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web\View;

use function Morpho\Base\fromJson;
use Morpho\Web\View\TemplateEngine;
use Morpho\Web\View\View;
use Morpho\Di\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\Request;
use Morpho\Web\View\Theme;

class ThemeTest extends TestCase {
    public function testRenderLayout_NonAjaxRedirect() {
        $request = new Request();
        $request->isDispatched(true);
        $request->isAjax(false);

        $redirectUri = '/foo/bar';
        $content = 'foo bar baz';
        $response = $request->response();
        $response->redirect($redirectUri);
        $response->setBody($content);

        $theme = $this->newTheme();

        $theme->renderLayout($request);

        $this->assertEquals($content, $response->body());
    }

    public function testRenderLayout_AjaxRedirect() {
        $request = new Request();
        $request->isDispatched(true);
        $request->isAjax(true);

        $redirectUri = '/foo/bar';
        $response = $request->response();
        $response->redirect($redirectUri);
        $response->setBody('');

        $theme = $this->newTheme();

        $theme->renderLayout($request);

        $this->assertEquals(['success' => ['redirect' => $redirectUri]], fromJson($response->body()));
        $this->assertEquals(
            'application/json',
            $response->headers()['Content-Type']
        );
    }

    public function testRenderLayout_RenderedOnce() {
        $request = new Request();
        $layoutName = 'index';
        $request->params()['layout'] = new View($layoutName);
        $request->isDispatched(true);

        $newTheme = function () {
            return new class ('foo/bar', $this->getTestDirPath()) extends Theme {
                public $renderFileArgs;

                protected function renderFile(string $relFilePath, array $vars, array $instanceVars = null): string {
                    $this->renderFileArgs = func_get_args();
                    return 'ok';
                }
            };
        };

        $theme1 = $newTheme();

        $theme1->renderLayout($request);

        $this->assertSame([$layoutName, ['body' => '']], $theme1->renderFileArgs);
        $this->assertTrue($request->params()['layout']->isRendered());

        $theme2 = $newTheme();

        $theme2->renderLayout($request);

        $this->assertNull($theme2->renderFileArgs);
        $this->assertTrue($request->params()['layout']->isRendered());
    }

    public function testRenderView_Ajax() {
        $theme = $this->newTheme();
        $viewVars = ['foo' => 'bar'];
        $request = new Request();
        $request->isAjax(true);
        $serviceManager = new ServiceManager(['request' => $request]);
        $theme->setServiceManager($serviceManager);

        $rendered = $theme->renderView(new View('some', $viewVars));

        $this->assertEquals($viewVars, fromJson($rendered));
    }

    public function testRenderView_NonAjax() {
        $theme = new Theme();

        $controllerName = 'baz';
        $viewName = 'edit';

        $request = $this->createMock(Request::class);
        $request->expects($this->any())
            ->method('isAjax')
            ->willReturn(false);
        $request->expects($this->any())
            ->method('controllerName')
            ->willReturn($controllerName);

        $expected = 'abcdefg123';

        $moduleDirPath = $this->createTmpDir();
        $theme->appendBaseDirPath($moduleDirPath);
        $viewAbsFilePath = $moduleDirPath . '/' . $controllerName . '/' . $viewName . Theme::VIEW_FILE_EXT;
        mkdir(dirname($viewAbsFilePath), 0777, true);
        touch($viewAbsFilePath);

        $viewVars = ['k' => 'v'];

        $templateEngine = $this->createMock(TemplateEngine::class);
        $templateEngine->expects($this->once())
            ->method('renderFile')
            ->with($this->equalTo($viewAbsFilePath), $this->equalTo($viewVars))
            ->will($this->returnValue($expected));

        $serviceManager = new ServiceManager([
            'request' => $request,
            'templateEngine' => $templateEngine,
        ]);

        $theme->setServiceManager($serviceManager);

        $view = $this->createConfiguredMock(View::class, [
            'name' => $viewName,
            'vars' => $viewVars,
        ]);

        $actual = $theme->renderView($view);

        $this->assertSame($expected, $actual);
    }

    public function testBasePathAccessors() {
        $theme = $this->newTheme();

        $this->assertEquals([], $theme->baseDirPaths());

        $baseDirPath1 = $this->getTestDirPath() . '/foo';
        $baseDirPath2 = $this->getTestDirPath() . '/bar';

        $theme->appendBaseDirPath($baseDirPath1);

        $this->assertEquals([$baseDirPath1], $theme->baseDirPaths());

        $theme->appendBaseDirPath($baseDirPath2);

        $this->assertEquals([$baseDirPath1, $baseDirPath2], $theme->baseDirPaths());

        // Append the same path twice, it must be placed at the end to render it in FIFO order.
        $theme->appendBaseDirPath($baseDirPath1);

        $this->assertEquals([$baseDirPath2, $baseDirPath1], $theme->baseDirPaths());

        $theme->clearBaseDirPaths();

        $this->assertEquals([], $theme->baseDirPaths());
    }

    private function newTheme() {
        return new Theme();
    }
}