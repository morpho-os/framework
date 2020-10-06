<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use Morpho\App\Web\Request;
use Morpho\App\Web\Response;
use Morpho\App\Web\IActionResult;
use Morpho\Testing\TestCase;

abstract class ActionResultTest extends TestCase {
    public function testInterface() {
        $this->assertInstanceOf(IActionResult::class, $this->mkActionResult());
    }

    public function dataForAllowAjax() {
        yield [true];
        yield [false];
    }

    /**
     * @dataProvider dataForAllowAjax
     */
    public function testAllowAjax(bool $val) {
        $actionResult = $this->mkActionResult();
        $this->assertFalse($actionResult->allowAjax());
        $this->assertSame($actionResult, $actionResult->allowAjax($val));
        $this->assertSame($val, $actionResult->allowAjax());
    }

    protected abstract function mkActionResult(): IActionResult;

    protected function mkRequest($response, bool $isAjax) {
        $request = new Request();
        $request->isAjax($isAjax);
        $request->setResponse($response);
        return $request;
    }

    protected function mkResponse(array $headers, ?bool $isRedirect) {
        $response = new class ($headers, $isRedirect) extends Response {
            private ?bool $isRedirect;
            public function __construct(array $headers, ?bool $isRedirect) {
                parent::__construct();
                $this->headers->exchangeArray($headers);
                $this->isRedirect = $isRedirect;
            }
        };
        return $response;
    }

    public function isRedirect(): bool {
        return $this->isRedirect;
    }
}
