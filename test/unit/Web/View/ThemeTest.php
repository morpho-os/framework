<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web\View;

use function Morpho\Base\fromJson;
use Morpho\Base\Node as BaseNode;
use Morpho\Core\Node;
use Morpho\Core\View;
use Morpho\Di\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\Module;
use Morpho\Web\ModuleFs;
use Morpho\Web\Request;
use Morpho\Web\View\TemplateEngine;
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
        $response->setContent($content);

        $theme = $this->newTheme();

        $theme->renderLayout($request);

        $this->assertEquals($content, $response->content());
    }

    public function testRenderLayout_AjaxRedirect() {
        $request = new Request();
        $request->isDispatched(true);
        $request->isAjax(true);

        $redirectUri = '/foo/bar';
        $response = $request->response();
        $response->redirect($redirectUri);
        $response->setContent('');

        $theme = $this->newTheme();

        $theme->renderLayout($request);

        $this->assertEquals(['success' => ['redirect' => $redirectUri]], fromJson($response->content()));
        $this->assertEquals('application/json', $response->headers()->get('Content-Type')->getFieldValue());
    }

    public function testRenderLayout_RenderedOnce() {
        $request = new Request();
        $layoutName = 'index';
        $request->setInternalParam('layout', new View($layoutName));
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
        $this->assertTrue($request->internalParam('layout')->isRendered());

        $theme2 = $newTheme();

        $theme2->renderLayout($request);

        $this->assertNull($theme2->renderFileArgs);
        $this->assertTrue($request->internalParam('layout')->isRendered());
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
        $theme = $this->newTheme();
        $viewName = 'my-view-name';
        $viewVars = ['news' => '123'];

        $expected = 'abcdefg123';

        $moduleName = 'foo-bar';
        $controllerName = 'my-controller-name';
        $moduleDirPath = $this->getTestDirPath() . '/' . $moduleName;

        $request = new Request();
        $request->setModuleName($moduleName)
            ->setControllerName($controllerName);
        $request->isAjax(false);
        $_SERVER['REQUEST_URI'] = '/base/path/test/me?arg=val';


        $viewAbsFilePath = $moduleDirPath . '/' . $controllerName . '/' . $viewName . Theme::VIEW_FILE_EXT;
        $templateEngine = $this->createMock(TemplateEngine::class);
        $templateEngine->expects($this->once())
            ->method('renderFile')
            ->with($this->equalTo($viewAbsFilePath), $this->equalTo($viewVars))
            ->will($this->returnValue($expected));

        $module = $this->createMock(Module::class);
        $fs = $this->createMock(ModuleFs::class);
        $fs->expects($this->any())
            ->method('viewDirPath')
            ->willReturn($moduleDirPath);
        $module->expects($this->any())
            ->method('name')
            ->willReturn($moduleName);
        $module->expects(($this->any()))
            ->method('fs')
            ->willReturn($fs);
        $moduleManager = new class ($module) extends Node {
            protected $name = 'ModuleManager';

            private $module;

            public function __construct($module) {
                $this->module = $module;
            }

            public function offsetGet($name): BaseNode {
                if ($name === $this->module->name()) {
                    return $this->module;
                }
                throw new \RuntimeException();
            }
        };
        $serviceManager = new ServiceManager([
            'request' => $request,
            'templateEngine' => $templateEngine,
            'moduleManager' => $moduleManager,
        ]);
        $theme->setServiceManager($serviceManager);

        $actual = $theme->renderView(new View($viewName, $viewVars));

        $this->assertEquals($expected, $actual);
    }

    public function testBasePathAccessors() {
        $theme = $this->newTheme();
        $this->assertEquals([], $theme->baseDirPaths());
        $baseDirPath = $this->getTestDirPath() . '/foo/bar';
        $theme->addBaseDirPath($baseDirPath);
        $this->assertEquals([$baseDirPath], $theme->baseDirPaths());
        // Add the same path twice.
        $theme->addBaseDirPath($baseDirPath);
        $this->assertEquals([$baseDirPath], $theme->baseDirPaths());
        $theme->clearBaseDirPaths();
        $this->assertEquals([], $theme->baseDirPaths());
    }

    private function newTheme() {
        return new Theme();
    }
}