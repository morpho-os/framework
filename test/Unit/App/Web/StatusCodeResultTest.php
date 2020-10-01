<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use Morpho\App\Web\StatusCodeResult;
use Morpho\App\Web\View\JsonResult;
use Morpho\Testing\TestCase;

class StatusCodeResultTest extends TestCase {
    use TActionResultTest;

    /*
    public function testInvoke_Ajax() {
        $statusCode = 404;
        $actionResult = new StatusCodeResult(404);

        $response = $this->mkResponse([], false);
        $request = $this->mkRequest($response, true);
        $serviceManager = [
            'request' => $request,
            'jsonRenderer' => function ($request) use (&$passedResult) {
                $passedResult = $request->response()['result'];
            },
        ];

        $actionResult->__invoke($serviceManager);

        $this->assertIsObject($response['result']);
        $this->assertSame($statusCode, $response->statusCode());
        $this->assertInstanceOf(JsonResult::class, $passedResult);
        $this->assertSame(['err' => 'Not Found'], $passedResult->val());
    }
     */

    public function testInvoke_NotAjax() {
        $statusCode = 404;
        $actionResult = new StatusCodeResult(404);
        $response = $this->mkResponse([], false);
        $request = $this->mkRequest($response, false);
        $notFoundHandler = ['this', 'is', 'not-found', 'handler'];
        $serviceManager = new class (['request' => $request], $notFoundHandler) extends \ArrayObject {
            public function __construct($vals, $notFoundHandler) {
                parent::__construct($vals);
                $this->notFoundHandler = $notFoundHandler;
            }

            public function conf() {
                return [
                    'actionResultHandler' => [
                        404 => $this->notFoundHandler,
                    ],
                ];
            }
        };

        $actionResult->__invoke($serviceManager);

        $this->assertIsObject($response['result']);
        $this->assertFalse($request->isHandled());
        $this->assertSame($statusCode, $response->statusCode());
        $this->assertSame($notFoundHandler, $request->handler());
    }
}
