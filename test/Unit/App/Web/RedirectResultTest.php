<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use Morpho\App\Web\RedirectResult;
use Morpho\App\Web\View\JsonResult;
use Morpho\App\Web\IActionResult;

class RedirectResultTest extends ActionResultTest {
    public function dataForInvoke() {
        $redirectUri = 'http://localhost/foo/bar?one=1&two=2';
        yield [
            $redirectUri,
            [],
            $redirectUri,
        ];
        yield [
            $redirectUri,
            ['Location' => $redirectUri],
            null,
        ];
    }

    /**
     * @dataProvider dataForInvoke
     */
    public function testInvoke_Ajax($redirectUri, $headers, $constructorArg) {
        $actionResult = new RedirectResult($constructorArg);

        $response = $this->mkResponse($headers, null);
        $request = $this->mkRequest($response, true);
        $serviceManager = [
            'request' => $request,
            'jsonRenderer' => function ($request) use (&$passedResult) {
                $passedResult = $request->response()['result'];
            },
        ];
        $actionResult->allowAjax(true);

        $actionResult->__invoke($serviceManager);

        $this->assertIsObject($response['result']);
        $this->assertSame(200, $response->statusCode());
        $this->assertInstanceOf(JsonResult::class, $passedResult);
        $this->assertSame(['redirect' => $redirectUri], $passedResult->val());
        $this->assertSame([], $response->headers()->getArrayCopy());
    }

    /**
     * @dataProvider dataForInvoke
     */
    public function testInvoke_NotAjax($redirectUri, $headers, $constructorArg) {
        $statusCode = 302;
        $actionResult = new RedirectResult($constructorArg, $statusCode);

        $response = $this->mkResponse($headers, null);
        $request = $this->mkRequest($response, false);
        $serviceManager = [
            'request' => $request,
        ];

        $actionResult->__invoke($serviceManager);

        $this->assertIsObject($response['result']);
        $this->assertSame($statusCode, $response->statusCode());
        $this->assertSame('', $response->body());
        $this->assertSame(['Location' => $redirectUri], $response->headers()->getArrayCopy());
    }

    protected function mkActionResult(): IActionResult {
        return new RedirectResult('http://localhost');
    }
}
