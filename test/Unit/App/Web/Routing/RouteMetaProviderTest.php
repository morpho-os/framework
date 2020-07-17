<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\Routing;

use Morpho\Base\IFn;
use Morpho\Testing\TestCase;
use Morpho\App\Web\Routing\RouteMetaProvider;
use function Morpho\Base\dasherize;

class RouteMetaProviderTest extends TestCase {
    public function testInterface() {
        $this->assertInstanceOf(IFn::class, new RouteMetaProvider());
    }

    public function dataForInvoke_RestActions() {
        return [
            [
                'index',
                'GET',
                null,
            ],
            [
                'list',
                'GET',
                'list',
            ],
            [
                'new',
                'GET',
                'new',
            ],
            [
                'create',
                'POST',
                null,
            ],
            [
                'show',
                'GET',
                '$id',
            ],
            [
                'edit',
                'GET',
                '$id/edit',
            ],
            [
                'update',
                'PATCH',
                '$id',
            ],
            [
                'delete',
                'DELETE',
                '$id',
            ],
        ];
    }

    /**
     * @dataProvider dataForInvoke_RestActions
     */
    public function testInvoke_RestActions(string $action, string $expectedHttpMethod, ?string $expectedActionPath) {
        $actionMetas = [
            [
                'module' => 'capture/group',
                'method' => $action,
                'class' => 'Foo\\Bar\\Web\\My\\Nested\\VerySimpleController',
            ],
        ];
        $routeMetaProvider = new RouteMetaProvider();
        $actual = \iterator_to_array($routeMetaProvider->__invoke($actionMetas));

        $expectedControllerPath = 'my/nested/very-simple';
        $expectedUri = '/' . explode('/', $actionMetas[0]['module'])[1] . '/' . $expectedControllerPath . (null === $expectedActionPath ? '' : '/' . $expectedActionPath);
        $this->assertEquals(
            [
                [
                    'module' => $actionMetas[0]['module'],
                    'method' => $actionMetas[0]['method'],
                    'class' => $actionMetas[0]['class'],
                    'httpMethod' => $expectedHttpMethod,
                    'uri' => $expectedUri,
                    'modulePath' => explode('/', $actionMetas[0]['module'])[1],
                    'controllerPath' => $expectedControllerPath,
                    'actionPath' => $expectedActionPath,
                ]
            ],
            $actual
        );
    }

    public function testInvoke_DocCommentsWithMultipleHttpMethodsWithCustomPathWithoutTitle() {
        $module = 'my-vendor/foo-mod';
        $method = 'doIt';
        $relUriPath = '/some/custom/$id/edit';
        $actionMetas = [
            [
                'module' => $module,
                'method' => $method,
                'docComment' => "/** @POST|PATCH $relUriPath */",
                'class' => 'Foo\\Bar\\Web\\MySimpleController',
            ],
        ];
        $routeMetaProvider = new RouteMetaProvider();
        $actual = \iterator_to_array($routeMetaProvider->__invoke($actionMetas));
        $this->assertEquals(
            [
                [
                    'module' => $module,
                    'class' => $actionMetas[0]['class'],
                    'method' => $method,
                    'docComment' => $actionMetas[0]['docComment'],
                    'httpMethod' => 'POST',
                    'uri' => $relUriPath,
                    'modulePath' => 'foo-mod',
                    'controllerPath' => 'my-simple',
                    'actionPath' => dasherize($method),
                ],
                [
                    'module' => $module,
                    'class' => $actionMetas[0]['class'],
                    'method' => $method,
                    'docComment' => $actionMetas[0]['docComment'],
                    'httpMethod' => 'PATCH',
                    'uri' => $relUriPath,
                    'modulePath' => 'foo-mod',
                    'controllerPath' => 'my-simple',
                    'actionPath' => dasherize($method),
                ],
            ],
            $actual
        );
    }

    public function testParseDocComments() {
        $docComment = <<<OUT
/**
 * @GET|POST
 */
OUT;
        $this->assertEquals(
            [
                'httpMethods' => ['GET', 'POST'],
                'uri'     => null,
                'title'   => null,
            ],
            RouteMetaProvider::parseDocComment($docComment)
        );

        // --------------

        $docComment = <<<OUT
/**
 * @GET /
 */
OUT;
        $this->assertEquals(
            [
                'httpMethods' => ['GET'],
                'uri'     => '/',
                'title'   => null,
            ],
            RouteMetaProvider::parseDocComment($docComment)
        );

        // --------------

        $docComment = <<<OUT
/**
 * @GET /some/path
 */
OUT;
        $this->assertEquals(
            [
                'httpMethods' => ['GET'],
                'uri'     => '/some/path',
                'title'   => null,
            ],
            RouteMetaProvider::parseDocComment($docComment)
        );

        // --------------

        $docComment = <<<OUT
/**
 * @Title Foo
 * @GET /some/path
 */
OUT;
        $this->assertEquals(
            [
                'httpMethods' => ['GET'],
                'uri'     => '/some/path',
                'title'   => 'Foo',
            ],
            RouteMetaProvider::parseDocComment($docComment)
        );

        // --------------

        $docComment = <<<OUT
/**
 * @Title My menu item
 */
OUT;
        $this->assertEquals(
            [
                'httpMethods' => null,
                'uri'     => null,
                'title'   => 'My menu item',
            ],
            RouteMetaProvider::parseDocComment($docComment)
        );

        // --------------

        $docComment = <<<OUT
/**
 *
 */
OUT;
        $this->assertEquals(
            [
                'httpMethods' => null,
                'uri'     => null,
                'title'   => null,
            ],
            RouteMetaProvider::parseDocComment($docComment)
        );
    }
}
