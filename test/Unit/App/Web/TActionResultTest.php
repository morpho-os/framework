<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use Morpho\App\Web\Request;
use Morpho\App\Web\Response;

trait TActionResultTest {
    private function mkRequest($response, bool $isAjax) {
        $request = new Request();
        $request->isAjax($isAjax);
        $request->setResponse($response);
        return $request;
    }

    private function mkResponse(array $headers, ?bool $isRedirect) {
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
}
