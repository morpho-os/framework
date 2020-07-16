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

class RouteMetaProviderTest extends TestCase {
    public function testInterface() {
        $this->assertInstanceOf(IFn::class, new RouteMetaProvider());
    }

    public function dataForIterator_RestActions() {
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
     * @dataProvider dataForIterator_RestActions
     */
    public function testIterator_RestActions($action, $expectedHttpMethod, $expectedRelUriPath) {
        $actionMetas = [
            [
                'module' => 'foo-mod',
                'action' => $action,
                'class' => 'Foo\\Bar\\Web\\My\\Nested\\VerySimpleController',
            ],
        ];
        $routeMetaProvider = new RouteMetaProvider();
        $actual = \iterator_to_array($routeMetaProvider->__invoke($actionMetas));
        $expectedControllerPath = 'my/nested/very-simple';
        $this->assertEquals(
            [
                [
                    'httpMethod' => $expectedHttpMethod,
                    'uri' => '/foo-mod/' . $expectedControllerPath . (null === $expectedRelUriPath ? '' : '/' . $expectedRelUriPath),
                    'module' => $actionMetas[0]['module'],
                    'action' => $actionMetas[0]['action'],
                    'class' => $actionMetas[0]['class'],
                    'shortModule' => 'foo-mod',
                    'controllerPath' => $expectedControllerPath,
                ]
            ],
            $actual
        );
    }

    public function testProvider_DocCommentsWithMultipleHttpMethodsWithCustomPathWithoutTitle() {
        $module = 'my-vendor/foo-mod';
        $action = 'do-it';
        $relUriPath = '/some/custom/$id/edit';
        $actionMetas = [
            [
                'module' => $module,
                'action' => $action,
                'docComment' => "/** @POST|PATCH $relUriPath */",
                'class' => 'Foo\\Bar\\Web\\MySimpleController',
            ],
        ];
        $routeMetaProvider = new RouteMetaProvider();
        $actual = \iterator_to_array($routeMetaProvider->__invoke($actionMetas));
        $this->assertEquals(
            [
                [
                    'httpMethod' => 'POST',
                    'uri' => $relUriPath,
                    'module' => $module,
                    'action' => $action,
                    'class' => $actionMetas[0]['class'],
                    'docComment' => $actionMetas[0]['docComment'],
                    'shortModule' => 'foo-mod',
                    'controllerPath' => 'my-simple',
                ],
                [
                    'httpMethod' => 'PATCH',
                    'uri' => $relUriPath,
                    'module' => $module,
                    'action' => $action,
                    'class' => $actionMetas[0]['class'],
                    'docComment' => $actionMetas[0]['docComment'],
                    'shortModule' => 'foo-mod',
                    'controllerPath' => 'my-simple',
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
