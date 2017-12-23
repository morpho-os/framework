<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web\Routing;

use Morpho\Base\IFn;
use Morpho\Test\TestCase;
use Morpho\Web\Routing\RouteMetaProvider;

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
        $moduleName = 'foo-mod';
        $actionMetas = [
            [
                'module' => $moduleName,
                'controller' => 'bar-ctrl',
                'action' => $action,
                'class' => __CLASS__,
            ],
        ];
        $routeMetaProvider = new RouteMetaProvider();
        $actual = iterator_to_array($routeMetaProvider->__invoke($actionMetas));
        $this->assertEquals(
            [
                [
                    'httpMethod' => $expectedHttpMethod,
                    'uri' => '/foo-mod/bar-ctrl' . (null === $expectedRelUriPath ? '' : '/' . $expectedRelUriPath),
                    'module' => $moduleName,
                    'controller' => 'bar-ctrl',
                    'action' => $action,
                    'title' => null,
                    'class' => __CLASS__,
                ]
            ],
            $actual
        );
    }

    public function testProvider_DocCommentsWithMultipleHttpMethodsWithCustomPathWithoutTitle() {
        $module = 'foo-mod';
        $controller = 'bar-ctrl';
        $action = 'do-it';
        $relUriPath = '/some/custom/$id/edit';
        $actionMetas = [
            [
                'module' => $module,
                'controller' => $controller,
                'action' => $action,
                'docComment' => "/** @POST|PATCH $relUriPath */",
                'class' => __CLASS__,
            ],
        ];
        $routeMetaProvider = new RouteMetaProvider();
        $actual = iterator_to_array($routeMetaProvider->__invoke($actionMetas));
        $this->assertEquals(
            [
                [
                    'httpMethod' => 'POST',
                    'uri' => $relUriPath,
                    'module' => $module,
                    'controller' => $controller,
                    'action' => $action,
                    'title' => null,
                    'class' => __CLASS__,
                ],
                [
                    'httpMethod' => 'PATCH',
                    'uri' => $relUriPath,
                    'module' => $module,
                    'controller' => $controller,
                    'action' => $action,
                    'title' => null,
                    'class' => __CLASS__,
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