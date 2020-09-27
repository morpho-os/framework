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
    /**
     * @param \Morpho\App\Web\Request $request
     */
    public function __invoke($request): void {
        /** @var \Morpho\App\Web\Response $response */
        $response = $request->response();
        $result = $response['result'];
        // https://tools.ietf.org/html/rfc7231#section-3.1.1
        $response->headers()['Content-Type'] = 'application/json;charset=utf-8';
        $response->setBody(toJson($result));
    }
}
