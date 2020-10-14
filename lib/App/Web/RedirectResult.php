<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Morpho\App\Web\Messages\TWithMessenger;
use Morpho\App\Web\View\JsonResult;

class RedirectResult implements IActionResult {
    use TActionResult;
    use TWithMessenger;

    public ?string $uri;

    public ?int $statusCode;

    public function __construct(?string $uri, int $statusCode = null) {
        $this->uri = $uri;
        $this->statusCode = $statusCode;
    }
    public function __invoke($serviceManager) {
        $request = $serviceManager['request'];
        $response = $request->response();
        $response['result'] = $this;
        $redirectUri = null;
        $headers = $response->headers();
        if (null !== $this->uri) {
            $redirectUri = $this->uri;
        } elseif (!empty($headers['Location'])) {
            $redirectUri = $headers['Location'];
        }
        if (null === $redirectUri) {
            throw new \UnexpectedValueException();
        }
        if ($this->allowAjax() && $request->isAjax()) {
            // todo: render messages as json
            $this->messenger->clearMessages();

            $response->setStatusCode(200);

            if (isset($headers['Location'])) {
                unset($headers['Location']);
            }

            $actionResult = $response['result'] = new JsonResult([
                'redirect' => $redirectUri,
            ]);
            $actionResult->__invoke($serviceManager);
        } else {
            $response->setStatusCode(null !== $this->statusCode ? $this->statusCode : 301);
            $response->headers()['Location'] = $redirectUri;
        }
    }
}
