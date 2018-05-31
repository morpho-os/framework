<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Morpho\App\Core\IResponse;
use Morpho\App\Web\View\View;
use function Morpho\Base\dasherize;
use Morpho\Ioc\IHasServiceManager;
use Morpho\Ioc\IServiceManager;
use Morpho\App\Core\Controller as BaseController;

class Controller extends BaseController implements IHasServiceManager {
    /**
     * @var \Morpho\Ioc\IServiceManager
     */
    protected $serviceManager;

    /**
     * @var \Morpho\App\Web\Request
     */
    protected $request;

    /**
     * @param mixed $actionResult
     */
    protected function handleActionResult($actionResult): IResponse {
        if ($actionResult instanceof IResponse) {
            return $actionResult;
        } else {
            $response = $this->request->response();
            if (is_string($actionResult)) {
                $response->setBody($actionResult);
            }  else {
                if (empty($actionResult)) {
                     $actionResult = $this->mkView();
                }
                $response['result'] = $actionResult;
            }
        }
        return $response;
    }

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }

    protected function mkJson($value): Json {
        return new Json($value);
    }

    protected function mkResponse(int $statusCode = null, string $body = null): Response {
        $response = new Response();
        if (null !== $statusCode) {
            $response->setStatusCode($statusCode);
        }
        if (null !== $body) {
            $response->setBody($body);
        }
        return $response;
    }

    protected function mkNotFoundResponse(string $actionName): IResponse {
        return new Response(Response::NOT_FOUND_STATUS_CODE);
    }

    protected function mkView(string $name = null, $vars = null, View $parent = null): View {
        if (null === $name) {
            $name = dasherize($this->request->actionName());
        }
        return new View($name, $vars, $parent);
    }
}
