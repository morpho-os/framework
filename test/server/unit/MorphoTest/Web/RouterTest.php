<?php
namespace MorphoTest\Web;

use Morpho\Fs\Directory;
use Morpho\Fs\File;
use Morpho\Web\Menu;
use Morpho\Web\Router;
use Morpho\Web\Request;
use Morpho\Test\DbTestCase;
use Morpho\Web\MenuItem;

class RouterTest extends DbTestCase {
    public function setUp() {
        $this->markTestIncomplete();

        parent::setUp();
        $db = $this->createDb();
        $db->dropTables(['menu_item', 'menu', 'route', 'file']);
        $db->createTableForClasses([
            '\Morpho\Web\Menu',
            '\Morpho\Core\File',
            '\Morpho\Web\Route',
            '\Morpho\Web\MenuItem',
        ]);
        $this->router = new Router($db);
        $this->db = $db;
        $db->insert('menu', ['name' => Menu::SYSTEM_NAME]);
        $this->controllerNs = 'MorphoTest\Web\RouterTest\Controller';
        $this->moduleDasherized = $this->getModuleNameDasherized();
    }

    public function dataForPatternsGeneration() {
        return [
            [
                [
                    '',
                ],
                [
                    '',
                ],
            ],
            [
                [
                    'module',
                ],
                [
                    'module',
                    '',
                ],
            ],
            [
                [
                    'module',
                    'controller',
                ],
                [
                    'module/controller',
                    'module/%',
                    '%/controller',
                    'module',
                    '',
                ],
            ],
            [
                [
                    'module',
                    'controller',
                    'action',
                ],
                [
                    'module/controller/action', // 111
                    'module/controller/%',      // 110
                    'module/%/action',          // 101
                    'module/%/%',               // 100
                    '%/controller/action',      // 011
                    '%/controller/%',           // 010
                    '%/%/action',               // 001
                    'module/controller',        // 11
                    'module/%',                 // 10
                    '%/controller',             // 01
                    'module',
                    '',
                ],
            ],
            [
                [
                    'system',
                    'table',
                    'list',
                    'route',
                ],
                [
                    'system/table/list/route',
                    'system/table/list/%',
                    'system/table/%/route',
                    'system/table/%/%',
                    'system/%/list/route',
                    'system/%/list/%',
                    'system/%/%/route',
                    'system/%/%/%',
                    '%/table/list/route',
                    '%/table/list/%',
                    '%/table/%/route',
                    '%/table/%/%',
                    '%/%/list/route',
                    '%/%/list/%',
                    '%/%/%/route',
                    'system/table/list',
                    'system/table/%',
                    'system/%/list',
                    'system/%/%',
                    '%/table/list',
                    '%/table/%',
                    '%/%/list',
                    'system/table',
                    'system/%',
                    '%/table',
                    'system',
                    '',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataForPatternsGeneration
     */
    public function testPatternsGeneration(array $uriParts, $expected) {
        $router = new MyRouter($this->mock('\Morpho\Db\Db'));
        $patterns = $router->generatePatterns($uriParts);
        $this->assertEquals($expected, $patterns);
    }

    public function testUserParams() {
        $this->markTestIncomplete();
    }

    public function testRebuildRoutes_AfterDeletionOfControllerFile() {
        $tmpDirPath = $this->createTmpDir(__FUNCTION__);
        $testDirPath = $this->getTestDirPath();
        Directory::copy($testDirPath, $tmpDirPath);
        unlink($tmpDirPath . '/RouterTest/' . CONTROLLER_DIR_NAME . '/MultipleHttpMethodsController.php');

        $moduleDirPath = $tmpDirPath . '/' . basename($testDirPath);

        $sql = "SELECT path, type FROM file ORDER BY id, path";

        $this->assertEquals([], $this->db->selectRows($sql));

        #$regExp = '~(?<!Setting)Controller\.php$~s';
        $regExp = null;
        $this->router->rebuildRoutes($moduleDirPath, $regExp);

        $type = 'controller';
        $this->assertEquals(
            [
                [
                    'path' => CONTROLLER_DIR_NAME . '/MultipleControllersController.php',
                    'type' => $type,
                ],
                [
                    'path' => CONTROLLER_DIR_NAME . '/MyEntityController.php',
                    'type' => $type,
                ],
                [
                    'path' => CONTROLLER_DIR_NAME . '/MyOtherController.php',
                    'type' => $type,
                ],
            ],
            $this->db->selectRows($sql)
        );

        File::delete($moduleDirPath . '/' . CONTROLLER_DIR_NAME . '/MyEntityController.php');
        $this->router->rebuildRoutes($moduleDirPath, $regExp);

        $this->assertEquals(
            [
                [
                    'path' => CONTROLLER_DIR_NAME . '/MultipleControllersController.php',
                    'type' => $type,
                ],
                [
                    'path' => CONTROLLER_DIR_NAME . '/MyOtherController.php',
                    'type' => $type,
                ],
            ],
            $this->db->selectRows($sql)
        );

        File::delete($moduleDirPath . '/' . CONTROLLER_DIR_NAME . '/MyOtherController.php');
        $this->router->rebuildRoutes($moduleDirPath, $regExp);

        $this->assertEquals(
            [
                [
                    'path' => CONTROLLER_DIR_NAME . '/MultipleControllersController.php',
                    'type' => $type,
                ],
            ],
            $this->db->selectRows($sql)
        );

        File::delete($moduleDirPath . '/' . CONTROLLER_DIR_NAME . '/MultipleControllersController.php');
        $this->router->rebuildRoutes($moduleDirPath, $regExp);

        $this->assertEquals([], $this->db->selectRows($sql));
    }

    public function testRebuildRoutes_AfterDeletionOfControllerClass() {
        $moduleDirPath = $tmpDirPath = $this->createTmpDir(__FUNCTION__);
        $controllerDirPath = Directory::create($tmpDirPath . '/' . CONTROLLER_DIR_NAME);
        $uniqId = 'C' . uniqid();
        $php = <<<OUT
<?php
namespace $this->controllerNs;

use Morpho\Web\Controller;

class {$uniqId}FirstController extends Controller
{
    public function my1Action()
    {
    }
}

class {$uniqId}SecondController extends Controller
{
    public function my2Action()
    {
    }
}
OUT;
        $controllerFilePath = $controllerDirPath . '/MultipleControllersController.php';
        file_put_contents($controllerFilePath, $php);

        $sql = "SELECT * FROM route ORDER BY module, controller, action";

        $this->assertEquals([], $this->db->selectRows($sql));

        $this->router->rebuildRoutes($moduleDirPath);

        $prefix = strtolower($uniqId);
        $this->assertEquals(
            [
                [
                    'module' => $this->moduleDasherized,
                    'controller' => $prefix . '-first',
                    'action' => 'my1',
                    'params' => NULL,
                    'httpMethod' => 'GET',
                    'uri' => $this->moduleDasherized . '/' . $prefix . '-first/my1',
                    'pattern' => $this->moduleDasherized . '/' . $prefix . '-first/my1',
                    'fit' => '7',
                    'fileId' => '1',
                ],
                [
                    'module' => $this->moduleDasherized,
                    'controller' => $prefix . '-second',
                    'action' => 'my2',
                    'params' => NULL,
                    'httpMethod' => 'GET',
                    'uri' => $this->moduleDasherized . '/' . $prefix . '-second/my2',
                    'pattern' => $this->moduleDasherized . '/' . $prefix . '-second/my2',
                    'fit' => '7',
                    'fileId' => '1',
                ],
            ],
            $this->db->selectRows($sql)
        );

        $php = <<<OUT
<?php
namespace $this->controllerNs;

use Morpho\Web\Controller;

class {$uniqId}SecondController extends Controller
{
    public function my2Action()
    {
    }
}
OUT;
        file_put_contents($controllerFilePath, $php);

        $this->router->rebuildRoutes($moduleDirPath);

        $this->assertEquals(
            [
                [
                    'module' => $this->moduleDasherized,
                    'controller' => $prefix . '-second',
                    'action' => 'my2',
                    'params' => NULL,
                    'httpMethod' => 'GET',
                    'uri' => $this->moduleDasherized . '/' . $prefix . '-second/my2',
                    'pattern' => $this->moduleDasherized . '/' . $prefix . '-second/my2',
                    'fit' => '7',
                    'fileId' => '1',
                ],
            ],
            $this->db->selectRows($sql)
        );
    }

    public function testRebuildRoutes_AfterDeletionOfAction() {
        $moduleDirPath = $tmpDirPath = $this->createTmpDir(__FUNCTION__);
        $controllerDirPath = Directory::create($tmpDirPath . '/' . CONTROLLER_DIR_NAME);
        $uniqId = 'C' . uniqid();
        $php = <<<OUT
<?php
namespace $this->controllerNs;

use Morpho\Web\Controller;

class {$uniqId}FirstController extends Controller
{
    public function my1Action()
    {
    }

    public function my2Action()
    {
    }
}
OUT;
        $controllerFilePath = $controllerDirPath . '/MultipleControllersController.php';
        file_put_contents($controllerFilePath, $php);

        $sql = "SELECT * FROM route ORDER BY module, controller, action";

        $this->assertEquals([], $this->db->selectRows($sql));

        $this->router->rebuildRoutes($moduleDirPath);

        $prefix = strtolower($uniqId);
        $this->assertEquals(
            [
                [
                    'module' => $this->moduleDasherized,
                    'controller' => $prefix . '-first',
                    'action' => 'my1',
                    'params' => NULL,
                    'httpMethod' => 'GET',
                    'uri' => $this->moduleDasherized . '/' . $prefix . '-first/my1',
                    'pattern' => $this->moduleDasherized . '/' . $prefix . '-first/my1',
                    'fit' => '7',
                    'fileId' => '1',
                ],
                [
                    'module' => $this->moduleDasherized,
                    'controller' => $prefix . '-first',
                    'action' => 'my2',
                    'params' => NULL,
                    'httpMethod' => 'GET',
                    'uri' => $this->moduleDasherized . '/' . $prefix . '-first/my2',
                    'pattern' => $this->moduleDasherized . '/' . $prefix . '-first/my2',
                    'fit' => '7',
                    'fileId' => '1',
                ],
            ],
            $this->db->selectRows($sql)
        );

        $php = <<<OUT
<?php
namespace $this->controllerNs;

use Morpho\Web\Controller;

class {$uniqId}FirstController extends Controller
{
    public function my2Action()
    {
    }
}
OUT;
        file_put_contents($controllerFilePath, $php);

        $this->router->rebuildRoutes($moduleDirPath);

        $this->assertEquals(
            [
                [
                    'module' => $this->moduleDasherized,
                    'controller' => $prefix . '-first',
                    'action' => 'my2',
                    'params' => NULL,
                    'httpMethod' => 'GET',
                    'uri' => $this->moduleDasherized . '/' . $prefix . '-first/my2',
                    'pattern' => $this->moduleDasherized . '/' . $prefix . '-first/my2',
                    'fit' => '7',
                    'fileId' => '1',
                ],
            ],
            $this->db->selectRows($sql)
        );
    }

    public function testRebuildRoutes_AfterDeletionOfTitleAnnotation() {
        $moduleDirPath = $tmpDirPath = $this->createTmpDir(__FUNCTION__);
        $controllerDirPath = Directory::create($tmpDirPath . '/' . CONTROLLER_DIR_NAME);
        $uniqId = 'C' . uniqid();
        $php = <<<OUT
<?php
namespace $this->controllerNs;

use Morpho\Web\Controller;

class {$uniqId}SettingController extends Controller
{
    /**
     * @Title Settings
     */
    public function listAction()
    {
    }

    /**
     * @Title Do it
     */
    public function fooAction()
    {
    }
    
    /**
     * @Title Persistent
     */
    public function testAction()
    {
    }
}
OUT;
        $controllerFilePath = $controllerDirPath . '/SettingController.php';
        file_put_contents($controllerFilePath, $php);

        $sql = "SELECT * FROM menu_item";
        $this->assertEquals([], $this->db->selectRows($sql));

        $this->router->rebuildRoutes($moduleDirPath);

        $prefix = strtolower($uniqId);
        $rows = $this->db->selectRows($sql);

        $this->assertCount(3, $rows);

        $this->assertEquals($this->moduleDasherized, $rows[0]['module']);
        $this->assertEquals($prefix . '-setting', $rows[0]['controller']);
        $this->assertEquals('list', $rows[0]['action']);
        $this->assertEquals('Settings', $rows[0]['title']);
        $this->assertEquals(MenuItem::CONTROLLER_TYPE, $rows[0]['type']);

        $this->assertEquals($this->moduleDasherized, $rows[1]['module']);
        $this->assertEquals($prefix . '-setting', $rows[1]['controller']);
        $this->assertEquals('foo', $rows[1]['action']);
        $this->assertEquals('Do it', $rows[1]['title']);
        $this->assertEquals(MenuItem::CONTROLLER_TYPE, $rows[1]['type']);

        $this->assertEquals($this->moduleDasherized, $rows[2]['module']);
        $this->assertEquals($prefix . '-setting', $rows[2]['controller']);
        $this->assertEquals('test', $rows[2]['action']);
        $this->assertEquals('Persistent', $rows[2]['title']);
        $this->assertEquals(MenuItem::CONTROLLER_TYPE, $rows[2]['type']);

        $php = <<<OUT
<?php
namespace $this->controllerNs;

use Morpho\Web\Controller;

class {$uniqId}SettingController extends Controller
{
    public function listAction()
    {
    }

    /**
     *
     */
    public function fooAction()
    {
    }
    
    /**
     * @Title Persistent
     */
    public function testAction()
    {
    }
}
OUT;
        file_put_contents($controllerFilePath, $php);

        $this->router->rebuildRoutes($moduleDirPath);

        $rows = $this->db->selectRows($sql);
        $this->assertCount(1, $rows);
        $this->assertEquals($this->moduleDasherized, $rows[0]['module']);
        $this->assertEquals($prefix . '-setting', $rows[0]['controller']);
        $this->assertEquals('test', $rows[0]['action']);
        $this->assertEquals('Persistent', $rows[0]['title']);
        $this->assertEquals(MenuItem::CONTROLLER_TYPE, $rows[0]['type']);

        $php = <<<OUT
<?php
namespace $this->controllerNs;

use Morpho\Web\Controller;

class {$uniqId}SettingController extends Controller
{
    public function listAction()
    {
    }

    /**
     *
     */
    public function fooAction()
    {
    }

    public function testAction()
    {
    }
}
OUT;
        file_put_contents($controllerFilePath, $php);

        $this->router->rebuildRoutes($moduleDirPath);

        $this->assertEquals([], $this->db->selectRows($sql));
    }

    public function dataForRoute_RestActions() {
        $module = $this->getModuleNameDasherized();
        $controller = 'my-entity';
        $uri = "/$module/$controller";
        return [
            // POST /$prefix/$entityType -> createAction()
            [
                'post',
                $uri,
                $module,
                $controller,
                'create',
                [],
            ],
            // GET /$prefix/$entityType/$entityId -> showAction($entityId)
            [
                'get',
                "$uri/123",
                $module,
                $controller,
                'show',
                ['id' => '123'],
            ],
            // PATCH /$prefix/$entityType/$entityId -> updateAction($entityId)
            [
                'patch',
                "$uri/456",
                $module,
                $controller,
                'update',
                ['id' => '456'],
            ],
            // PATCH /$prefix/$entityType -> 404
            [
                'patch',
                $uri,  // no required parameter -> not found
                null,
                null,
                null,
                [],
            ],
            // DELETE /$prefix/$entityType/$entityId -> deleteAction($entityId)
            [
                'delete',
                "$uri/789",
                $module,
                $controller,
                'delete',
                ['id' => '789'],
            ],
            // DELETE /$prefix/$entityType -> 404
            [
                'delete',
                $uri,  // no required parameter -> not found
                null,
                null,
                null,
                [],
            ],
            // GET /$prefix/$entityType/new -> newAction()
            [
                'get',
                "$uri/new",
                $module,
                $controller,
                'new',
                [],
            ],
            // GET /$prefix/$entityType/$entityId/edit -> editAction($entityId)
            [
                'get',
                "$uri/123/edit",
                $module,
                $controller,
                'edit',
                ['id' => 123],
            ],
            // GET /$prefix/$entityType/list -> listAction()
            [
                'get',
                "$uri/list",
                $module,
                $controller,
                'list',
                [],
            ],
        ];
    }

    /**
     * @dataProvider dataForRoute_RestActions
     */
    public function testRoute_RestActions($method, $uri, $expectedModule, $expectedController, $expectedAction, array $expectedParams) {
        $this->rebuildRoutes('MyEntityController.php');

        $request = new Request();
        $request->setUri($uri);
        $request->setMethod($method);

        $this->router->route($request);

        $this->assertEquals($expectedModule, $request->getModuleName());
        $this->assertEquals($expectedController, $request->getControllerName());
        $this->assertEquals($expectedAction, $request->getActionName());
        $this->assertEquals($expectedParams, $request->getParams());
    }

    public function testAssemble_WithPositionalParameters() {
        $this->rebuildRoutes('MyEntityController.php');

        $module = $this->moduleDasherized;
        $controller = 'my-entity';
        $action = 'edit';
        $uri = $this->router->assemble('get', $module, $controller, $action, ['v1', 'v2']);
        $this->assertEquals("$module/$controller/v1/edit", $uri);
    }

    public function testAssemble_WithNamedParameters() {
        $this->rebuildRoutes('MyEntityController.php');

        $module = $this->moduleDasherized;
        $controller = 'my-entity';
        $action = 'edit';
        $uri = $this->router->assemble('get', $module, $controller, $action, ['id' => 123]);
        $this->assertEquals("$module/$controller/123/edit", $uri);
    }

    public function testRoute_MultipleHttpMethodsToOneAction() {
        $this->rebuildRoutes('MultipleHttpMethodsController.php');

        $request = new Request();
        $request->setUri('/login/foo/bar');
        $request->setMethod('get');

        $this->router->route($request);

        $this->assertEquals($this->getModuleNameDasherized(), $request->getModuleName());
        $this->assertEquals('multiple-http-methods', $request->getControllerName());
        $this->assertEquals('log-in', $request->getActionName());
        $this->assertEquals(['foo' => 'bar'], $request->getParams());
    }

    public function testRebuildRoutes_AfterDeletionOfHttpMethod() {
        $this->markTestIncomplete();
    }

    private function rebuildRoutes($fileName) {
        $this->router->rebuildRoutes(
            $this->getTestDirPath(),
            function ($path, $isDir) use ($fileName) {
                return $isDir || basename($path) == $fileName;
            }
        );
    }

    private function getModuleNameDasherized() {
        return 'morpho-test-web-router-test';
    }
}
/*
class MyRouter extends Router {
    public function generatePatterns(array $uriParts) {
        return parent::generatePatterns($uriParts);
    }
}
*/