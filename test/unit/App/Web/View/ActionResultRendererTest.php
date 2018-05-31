<?php declare(strict_types=1);
namespace Morpho\Test\Unit\App\Web\View;

use Morpho\App\Web\Request;
use Morpho\App\Web\Response;
use Morpho\App\Web\View\ActionResultRenderer;
use Morpho\Ioc\IServiceManager;
use Morpho\Testing\TestCase;

class ActionResultRendererTest extends TestCase {
    public function dataForShouldRender() {
        yield [true, false];
        yield [false, true];
    }

    /**
     * @dataProvider dataForShouldRender
     */
    public function testShouldRender(bool $isRedirect, bool $expected) {
        $request = new Request();
        $request->setResponse($this->mkResponse($isRedirect));

        /** @noinspection PhpParamsInspection */
        $actionResultRenderer = new ActionResultRenderer($this->createMock(IServiceManager::class));

        $this->assertSame($expected, $actionResultRenderer->shouldRender($request));
    }

    public function testInvoke_EmptyOrNotSetActionResult_ThrowsException() {
        $request = new Request();
        $serviceManager = $this->createMock(IServiceManager::class);
        /** @noinspection PhpParamsInspection */
        $actionResultRenderer = new ActionResultRenderer($serviceManager);

        $this->expectException(\UnexpectedValueException::class, 'Empty response result');

        $actionResultRenderer->__invoke($request);
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
