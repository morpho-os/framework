<?php
namespace MorphoTest\Core;

use function Morpho\Base\decodeJson;
use Morpho\Base\Node;
use Morpho\Di\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\Request;
use Morpho\Web\Theme;

class ThemeTest extends TestCase {
    public function testIsLayoutRendered() {
        $this->assertBoolAccessor([new Theme(), 'isLayoutRendered'], false);
    }

    public function testAfterDispatch_Redirect_NonAjax_DoesNotChangeResponseContent() {
        $request = new Request();
        $request->isDispatched(true);
        $request->isAjax(false);

        $redirectUri = '/foo/bar';
        $content = 'foo bar baz';
        $response = $request->getResponse();
        $response->redirect($redirectUri);
        $response->setContent($content);

        $event = ['afterDispatch', ['request' => $request]];
        $theme = new Theme();

        $this->assertFalse($theme->isLayoutRendered());

        $theme->afterDispatch($event);

        $this->assertTrue($theme->isLayoutRendered());
        $this->assertEquals($content, $response->getContent());
    }

    public function testAfterDispatch_Redirect_Ajax_SetsAjaxSpecificContent() {
        $request = new Request();
        $request->isDispatched(true);
        $request->isAjax(true);

        $redirectUri = '/foo/bar';
        $response = $request->getResponse();
        $response->redirect($redirectUri);
        $response->setContent('');

        $event = ['afterDispatch', ['request' => $request]];
        $theme = new Theme();

        $this->assertFalse($theme->isLayoutRendered());

        $theme->afterDispatch($event);

        $this->assertTrue($theme->isLayoutRendered());
        $this->assertEquals(['success' => ['redirect' => $redirectUri]], decodeJson($response->getContent()));
        $this->assertEquals('application/json', $response->getHeaders()->get('Content-Type')->getFieldValue());
    }

    public function testRender_Ajax() {
        $theme = new Theme();
        $viewVars = ['foo' => 'bar'];
        $event = ['render', ['vars' => $viewVars]];

        $request = new Request();
        $request->isAjax(true);
        $serviceManager = new ServiceManager(['request' => $request]);
        $theme->setServiceManager($serviceManager);

        $rendered = $theme->render($event);

        $this->assertEquals($viewVars, decodeJson($rendered));
    }

    public function testRender_NonAjax() {
        $theme = new Theme();
        $viewName = 'my-view-name';
        $viewVars = ['news' => '123'];
        $controller = new class extends Node {
            protected $name = 'MyControllerName';
        };
        $event = [
            'render',
            [
                'vars' => $viewVars,
                'node' => $controller,
                'name' => $viewName,
            ]
        ];
        $moduleName = 'foo-bar';
        $moduleDirPath = $this->getTestDirPath() . '/' . $moduleName;
        $theme->setParent(new class ($moduleName, $moduleDirPath) extends Node {
            protected $name = 'ModuleManager';

            private $moduleName, $moduleDirPath;

            public function __construct($moduleName, $moduleDirPath) {
                $this->moduleName = $moduleName;
                $this->moduleDirPath = $moduleDirPath;
            }

            public function getModuleFs() {
                return new class ($this->moduleName, $this->moduleDirPath) {
                    private $moduleDirPath;

                    public function __construct($moduleName, $moduleDirPath) {
                        $this->moduleDirPath[$moduleName] = $moduleDirPath;
                    }

                    public function getModuleViewDirPath($moduleName) {
                        return $this->moduleDirPath[$moduleName];
                    }
                };
            }
        });

        $request = new Request();
        $request->setModuleName($moduleName);
        $request->isAjax(false);
        $_SERVER['REQUEST_URI'] = '/base/path/test/me?arg=val';
        $viewRelFilePath = 'my-controller-name/' . $viewName;
        $viewAbsFilePath = $moduleDirPath . '/' . $viewRelFilePath . $theme->getViewFileSuffix();
        $viewVars['node'] = $controller;
        $templateEngine = $this->createMock(\Morpho\Web\View\TemplateEngine::class);
        $expectedRendered = 'abcdefg123';
        $templateEngine->expects($this->once())
            ->method('renderFile')
            ->with($this->equalTo($viewAbsFilePath), $this->equalTo($viewVars))
            ->will($this->returnValue($expectedRendered));
        $serviceManager = new ServiceManager([
            'request' => $request,
            'templateEngine' => $templateEngine,
        ]);
        $theme->setServiceManager($serviceManager);

        $actualRendered = $theme->render($event);

        $this->assertEquals($expectedRendered, $actualRendered);
    }
    
    public function testBasePathAccessors() {
        $theme = new Theme();
        $this->assertEquals([], $theme->getBaseDirPaths());
        $baseDirPath = $this->getTestDirPath() . '/foo/bar';
        $theme->addBaseDirPath($baseDirPath);
        $this->assertEquals([$baseDirPath], $theme->getBaseDirPaths());
        // Add the same path twice.
        $theme->addBaseDirPath($baseDirPath);
        $this->assertEquals([$baseDirPath], $theme->getBaseDirPaths());
        $theme->clearBaseDirPaths();
        $this->assertEquals([], $theme->getBaseDirPaths());
    }
}