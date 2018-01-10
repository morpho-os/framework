<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\View;

use Morpho\Base\IFn;
use function Morpho\Base\toJson;

class JsonRenderer implements IFn {
    /**
     * @param \Morpho\Web\Request $request
     */
    public function __invoke($request): void {
        /** @var \Morpho\Web\Response $response */
        $response = $request->response();

        $page = $request['page'];

        $response->headers()['Content-Type'] = 'application/json';
        if ($request->isAjax()) {
            $body = [
                'code' => $response->statusCode(),
                'page' => $page
            ];
            if ($response->isRedirect()) {
                $body['redirect'] = $response->headers()['Location'];
                unset($response->headers()['Location']);
            }
            $response->setBody(toJson($body));
        } else {
            $response->setBody(toJson($page));
        }
    }
}