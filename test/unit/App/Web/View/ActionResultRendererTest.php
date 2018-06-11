<?php declare(strict_types=1);
namespace Morpho\Test\Unit\App\Web\View;

use Morpho\App\Web\Json;
use Morpho\App\Web\Request;
use Morpho\App\Web\Response;
use Morpho\App\Web\View\ActionResultRenderer;
use Morpho\App\Web\View\HtmlRenderer;
use Morpho\App\Web\View\JsonRenderer;
use Morpho\App\Web\View\View;
use Morpho\Ioc\IServiceManager;
use Morpho\Testing\TestCase;

class ActionResultRendererTest extends TestCase {
    /**
     * @var ActionResultRenderer
     */
    private $actionResultRenderer;

    public function setUp() {
        parent::setUp();
        /** @noinspection PhpParamsInspection */
        $this->actionResultRenderer = new ActionResultRenderer($this->createMock(IServiceManager::class));
    }

    public function dataForShouldRender_IsRedirectCase() {
        yield [true, false];
        yield [false, true];
    }

    /**
     * @dataProvider dataForShouldRender_IsRedirectCase
     */
    public function testShouldRender_IsRedirectCase(bool $isRedirect, bool $expected) {
        $request = new Request();
        $response = $this->mkResponse($isRedirect);
        $response['result'] = 'foo';
        $request->setResponse($response);

        $this->assertSame($expected, $this->actionResultRenderer->shouldRender($request));
    }

    public function testShouldRender_ActionResultIsNotSet() {
        $request = new Request();
        $response = $this->mkResponse(false);
        $request->setResponse($response);

        $this->assertFalse($this->actionResultRenderer->shouldRender($request));
    }

    public function testRender_CallsRenderer() {
        $renderer = function (...$args) use (&$rendererArgs) {
            $rendererArgs = $args;
        };

        $serviceManager = $this->createMock(IServiceManager::class);
        /** @noinspection PhpParamsInspection */
        $actionResultRenderer = new class ($renderer, $serviceManager) extends ActionResultRenderer {
            private $renderer;
            public $invokeArgs;
            public function __construct(callable $renderer, $serviceManager) {
                parent::__construct($serviceManager);
                $this->renderer = $renderer;
            }

            public function chooseRenderer(Request $request): callable {
                return $this->renderer;
            }
        };

        $request = new Request();

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertVoid($actionResultRenderer->render($request));

        $this->assertSame([$request], $rendererArgs);
    }

    public function dataForChooseRenderer_ValidCases() {
        yield [
            new View('test'),
            HtmlRenderer::class,
        ];
        yield [
            new Json('test'),
            JsonRenderer::class,
        ];
    }

    /**
     * @dataProvider dataForChooseRenderer_ValidCases
     */
    public function testChooseRenderer_ValidCases($actionResult, $expectedClass) {
        $request = new Request();
        $response = $this->mkResponse(false);
        $response['result'] = $actionResult;
        $request->setResponse($response);

        $renderer = $this->actionResultRenderer->chooseRenderer($request);
        $this->assertInstanceOf($expectedClass, $renderer);
    }

    public function dataForChooseRenderer_InvalidCases() {
        yield [
            new \stdClass(),
        ];
        yield [
            null,
        ];
        yield [
            '',
        ];
        yield [
            'some',
        ];
    }

    /**
     * @dataProvider dataForChooseRenderer_InvalidCases
     */
    public function testChooseRenderer_InvalidCases($actionResult) {
        $request = new Request();
        $response = $this->mkResponse(false);
        $response['result'] = $actionResult;
        $request->setResponse($response);

        $this->expectException(\UnexpectedValueException::class, 'Unexpected action result');

        $this->actionResultRenderer->chooseRenderer($request);
    }

    private function mkResponse(bool $isRedirect) {
        $response = new class ($isRedirect) extends Response {
            private $isRedirect;

            public function __construct(bool $isRedirect) {
                parent::__construct([]);
                $this->isRedirect = $isRedirect;
            }

            public function isRedirect(): bool {
                return $this->isRedirect;
            }
        };
        return $response;
    }
}
