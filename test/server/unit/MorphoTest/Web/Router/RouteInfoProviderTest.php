<?php
namespace MorphoTest\Web\Router;

use Morpho\Test\TestCase;
use Morpho\Web\Router\RouteInfoProvider;

class RouteInfoProviderTest extends TestCase {
    public function testBuildMetaForControllersInFile() {
        $expectedDocComment = <<<OUT
/**
     * @foo Bar
     */
OUT;
        $nsPrefix = __NAMESPACE__ . '\\RouteInfoProviderTest\\';

        $this->assertEquals(
            [
                [
                    'class' => $nsPrefix . 'MyFirstController',
                    'controller' => 'MyFirst',
                    'actions' => [
                        [
                            'action' => 'foo',
                        ],
                    ],
                ],
                [
                    'class' => $nsPrefix . 'MySecondController',
                    'controller' => 'MySecond',
                    'actions' => [
                        [
                            'action' => 'doSomething',
                        ],
                        [
                            'action' => 'process',
                            'docComment' => $expectedDocComment,
                        ],
                    ],
                ],
                [
                    'class' => $nsPrefix . 'ThirdController',
                    'controller' => 'Third',
                ],
            ],
            RouteInfoProvider::buildMetaForControllersInFile($this->getTestDirPath() . '/MyController.php')
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
                'methods' => ['GET', 'POST'],
                'uri' => null,
                'title' => null,
            ],
            RouteInfoProvider::parseDocComment($docComment)
        );

        $docComment = <<<OUT
/**
 * @GET /
 */
OUT;
        $this->assertEquals(
            [
                'methods' => ['GET'],
                'uri' => '/',
                'title' => null,
            ],
            RouteInfoProvider::parseDocComment($docComment)
        );


        $docComment = <<<OUT
/**
 * @GET /some/path
 */
OUT;
        $this->assertEquals(
            [
                'methods' => ['GET'],
                'uri' => '/some/path',
                'title' => null,
            ],
            RouteInfoProvider::parseDocComment($docComment)
        );

        $docComment = <<<OUT
/**
 * @Title Foo
 * @GET /some/path
 */
OUT;
        $this->assertEquals(
            [
                'methods' => ['GET'],
                'uri' => '/some/path',
                'title' => 'Foo',
            ],
            RouteInfoProvider::parseDocComment($docComment)
        );

        $docComment = <<<OUT
/**
 * @Title My menu item
 */
OUT;
        $this->assertEquals(
            [
                'methods' => null,
                'uri' => null,
                'title' => 'My menu item',
            ],
            RouteInfoProvider::parseDocComment($docComment)
        );

        $docComment = <<<OUT
/**
 *
 */
OUT;
        $this->assertEquals(
            [
                'methods' => null,
                'uri' => null,
                'title' => null,
            ],
            RouteInfoProvider::parseDocComment($docComment)
        );
    }
}