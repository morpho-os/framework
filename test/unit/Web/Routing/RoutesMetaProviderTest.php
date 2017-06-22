<?php declare(strict_types=1);
namespace MorphoTest\Unit\Web\Routing;

use Morpho\Test\TestCase;
use Morpho\Web\Routing\RoutesMetaProvider;

class RoutesMetaProviderTest extends TestCase {
    public function testInterface() {
        $this->assertInstanceOf('\Traversable', new RoutesMetaProvider());
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
        $routesMetaProvider = new RoutesMetaProvider();
        $actionsMetaProvider = new \ArrayIterator([
            [
                'module' => 'foo-mod',
                'controller' => 'bar-ctrl',
                'action' => $action,
                'class' => __CLASS__,
            ],
        ]);
        $routesMetaProvider->setActionsMetaProvider($actionsMetaProvider);
        $actual = iterator_to_array($routesMetaProvider);
        $this->assertEquals(
            [
                [
                    'httpMethod' => $expectedHttpMethod,
                    'uri' => '/foo-mod/bar-ctrl' . (null === $expectedRelUriPath ? '' : '/' . $expectedRelUriPath),
                    'module' => 'foo-mod',
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
        $routesMetaProvider = new RoutesMetaProvider();
        $module = 'foo-mod';
        $controller = 'bar-ctrl';
        $action = 'do-it';
        $relUriPath = '/some/custom/$id/edit';
        $actionsMetaProvider = new \ArrayIterator([
            [
                'module' => $module,
                'controller' => $controller,
                'action' => $action,
                'docComment' => "/** @POST|PATCH $relUriPath */",
                'class' => __CLASS__,
            ],
        ]);
        $routesMetaProvider->setActionsMetaProvider($actionsMetaProvider);
        $actual = iterator_to_array($routesMetaProvider);
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
            RoutesMetaProvider::parseDocComment($docComment)
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
            RoutesMetaProvider::parseDocComment($docComment)
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
            RoutesMetaProvider::parseDocComment($docComment)
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
            RoutesMetaProvider::parseDocComment($docComment)
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
            RoutesMetaProvider::parseDocComment($docComment)
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
            RoutesMetaProvider::parseDocComment($docComment)
        );
    }
}