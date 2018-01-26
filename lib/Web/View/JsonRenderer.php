<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\View;

use Morpho\Base\IFn;
use function Morpho\Base\toJson;
use Morpho\Web\Response;

class JsonRenderer implements IFn {
    /**
     * @param \Morpho\Web\Request $request
     */
    public function __invoke($request): void {
        /** @var \Morpho\Web\Response $response */
        $response = $request->response();

        $page = $response['resource'];

        // https://tools.ietf.org/html/rfc7231#section-3.1.1
        $response->headers()['Content-Type'] = 'application/json;charset=utf-8';
        if ($request->isAjax()) {
            if ($response->isRedirect()) {
                $page['redirect'] = $response->headers()['Location'];
                unset($response->headers()['Location']);
                $response->setStatusCode(Response::OK_STATUS_CODE);
            }
        }
        $response->setBody(toJson($page));
    }
}