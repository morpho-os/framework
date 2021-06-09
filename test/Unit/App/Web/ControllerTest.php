<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use Morpho\App\Web\ContentFormat;
use Morpho\App\Web\Controller;
use Morpho\App\Web\IRequest;
use Morpho\App\Web\Response;
use Morpho\Base\Ok;
use Morpho\Testing\TestCase;

class ControllerTest extends TestCase {
    public function testReturnResultInstanceFromAction() {
        $val = 'test';

        $controller = new class ($val) extends Controller {
            public bool $called = false;
            private $val;

            public function __construct($val) {
                $this->val = $val;
            }

            public function someAction() {
                $this->called = true;
                return new Ok($this->val);
            }
        };
        $response = new Response();
        $request = $this->mkConfiguredRequest($response);

        $request = $controller->__invoke($request);

        $this->assertTrue($controller->called);
        $result = $request->response()['result'];
        $this->assertEquals(new Ok($val), $result);
        $this->assertTrue($response->allowAjax());
        $this->assertSame([ContentFormat::JSON], $response->formats());
    }

    public function testRedirect() {
        $response = new Response();
        $request = $this->mkConfiguredRequest($response);

        $controller = new class extends Controller {
            public function someAction() {
                return $this->redirect('/foo/bar', 399);
            }
        };

        $request = $controller->__invoke($request);

        $changedResponse = $request->response();
        $this->assertSame($changedResponse, $response);
        $this->assertSame('/foo/bar', $changedResponse->headers()['Location']);
        $this->assertSame(399, $changedResponse->statusCode());
    }

    private function mkConfiguredRequest($response) {
        // The IRequest contains `method()` method and it can't be handled by PHPUnit using createConfiguredMock(), so another approach is used to create configured request mock.
        /*$request = $this->createConfiguredMock(
            IRequest::class,
            ['handler' => ['method' => 'someAction'], 'response' => $response]
        );*/
        $request = $this->createMock(IRequest::class);
        $request->expects($this->any())
            ->method('handler')
            ->willReturn(['method' => 'someAction']);
        $request->expects($this->any())
            ->method('response')
            ->willReturn($response);
        return $request;
    }
}
