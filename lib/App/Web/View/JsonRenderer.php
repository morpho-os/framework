<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

use Morpho\Base\IFn;
use function Morpho\Base\toJson;

class JsonRenderer implements IFn {
    public function __construct($request) {
        $this->request = $request;
    }

    public function __invoke($actionResult): void {
        $actionResult->clearMessages();
        $response = $this->request->response();
        // https://tools.ietf.org/html/rfc7231#section-3.1.1
        $response->headers()['Content-Type'] = 'application/json;charset=utf-8';
        $response->setBody(toJson($actionResult->getArrayCopy()));
    }
}
