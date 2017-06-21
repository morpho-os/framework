<?php declare(strict_types=1);
namespace MorphoTest\Unit\Core;

use function Morpho\Base\fromJson;
use Morpho\Base\Node as BaseNode;
use Morpho\Core\Node;
use Morpho\Di\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\Module;
use Morpho\Web\Request;
use Morpho\Web\Theme;

class ThemeTest extends TestCase {
    public function testIsLayoutRendered() {
        $this->checkBoolAccessor([$this->newTheme(), 'isLayoutRendered'], false);
    }

    public function testAfterDispatch_Redirect_NonAjax_DoesNotChangeResponseContent() {
        $request = new Request();
        $request->isDispatched(true);
        $request->isAjax(false);

        $redirectUri = '/foo/bar';
        $content = 'foo bar baz';
        $response = $request->response();
        $response->redirect($redirectUri);
        $response->setContent($content);

        $event = ['afterDispatch', ['request' => $request]];
        $theme = $this->newTheme();

        $this->assertFalse($theme->isLayoutRendered());

        $theme->afterDispatch($event);

        $this->assertTrue($theme->isLayoutRendered());
        $this->assertEquals($content, $response->content());
    }

    public function testAfterDispatch_Redirect_Ajax_SetsAjaxSpecificContent() {
        $request = new Request();
        $request->isDispatched(true);
        $request->isAjax(true);

        $redirectUri = '/foo/bar';
        $response = $request->response();
        $response->redirect($redirectUri);
        $response->setContent('');

        $event = ['afterDispatch', ['request' => $request]];
        $theme = $this->newTheme();

        $this->assertFalse($theme->isLayoutRendered());

        $theme->afterDispatch($event);

        $this->assertTrue($theme->isLayoutRendered());
        $this->assertEquals(['success' => ['redirect' => $redirectUri]], fromJson($response->content()));
        $this->assertEquals('application/json', $response->headers()->get('Content-Type')->getFieldValue());
    }

    public function testRender_Ajax() {
        $theme = $this->newTheme();
        $viewVars = ['foo' => 'bar'];
        $event = ['render', ['vars' => $viewVars]];

        $request = new Request();
        $request->isAjax(true);
        $serviceManager = new ServiceManager(['request' => $request]);
        $theme->setServiceManager($serviceManager);

        $rendered = $theme->render($event);

        $this->assertEquals($viewVars, fromJson($rendered));
    }

    public function testRender_NonAjax() {
        $theme = $this->newTheme();

        $moduleName = 'foo-bar';
        $controllerName = 'my-controller-name';
        $moduleDirPath = $this->getTestDirPath() . '/' . $moduleName;
        $viewName = 'my-view-name';
        $viewVars = ['news' => '123'];

        $request = new Request();
        $request->setModuleName($moduleName)
            ->setControllerName($controllerName);
        $request->isAjax(false);
        $_SERVER['REQUEST_URI'] = '/base/path/test/me?arg=val';
        
        $viewAbsFilePath = $moduleDirPath . '/' . $controllerName . '/' . $viewName . Theme::VIEW_FILE_EXT;
        $expectedRendered = 'abcdefg123';

        $templateEngine = $this->createMock(\Morpho\Web\View\TemplateEngine::class);
        $templateEngine->expects($this->once())
            ->method('renderFile')
            ->with($this->equalTo($viewAbsFilePath), $this->equalTo($viewVars))
            ->will($this->returnValue($expectedRendered));

        $serviceManager = new ServiceManager([
            'request' => $request,
            'templateEngine' => $templateEngine,
        ]);
        $theme->setServiceManager($serviceManager);

        $module = $this->createMock(Module::class);
        $module->expects($this->any())
            ->method('viewDirPath')
            ->willReturn($moduleDirPath);
        $module->expects($this->any())
            ->method('name')
            ->willReturn($moduleName);
        $moduleManager = new class ($module) extends Node {
            protected $name = 'ModuleManager';

            private $module;

            public function __construct($module) {
                $this->module = $module;
            }

            public function child(string $name): BaseNode {
                if ($name === $this->module->name()) {
                    return $this->module;
                }
                throw new \RuntimeException();
            }
        };
        $theme->setParent($moduleManager);

        $event = [
            'render',
            [
                'vars' => $viewVars,
                'view' => $viewName,
            ]
        ];

        $actualRendered = $theme->render($event);

        $this->assertEquals($expectedRendered, $actualRendered);
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
        return new Theme('foo/bar', $this->getTestDirPath());
    }
}