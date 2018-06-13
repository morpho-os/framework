<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Morpho\App\IActionResult;
use Morpho\App\IResponse;
use Morpho\App\Web\View\View;
use function Morpho\Base\dasherize;
use Morpho\Ioc\IHasServiceManager;
use Morpho\Ioc\IServiceManager;
use Morpho\App\Controller as BaseController;

abstract class Controller extends BaseController implements IHasServiceManager {
    /**
     * @var \Morpho\Ioc\IServiceManager
     */
    protected $serviceManager;

    /**
     * @var \Morpho\App\Web\Request
     */
    protected $request;

    /**
     * @param array|null|IActionResult|IResponse|string $actionResult
     */
    protected function handleActionResult($actionResult): IResponse {
        if (\is_array($actionResult) || null === $actionResult) {
            $actionResult = $this->arrToActionResult((array) $actionResult);
        } elseif ($actionResult instanceof IActionResult) {
            // Do nothing
        } elseif ($actionResult instanceof IResponse) {
            return $actionResult;
        } elseif (\is_string($actionResult)) {
            $response = $this->request->response();
            $response->setBody($actionResult);
            return $response;
        } else {
            throw new \UnexpectedValueException();
        }
        $response = $this->request->response();
        $response['result'] = $actionResult;
        return $response;
    }

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }

    protected function mkView(string $name = null, $vars = null, View $parent = null): View {
        if (null === $name) {
            $name = dasherize($this->request->actionName());
        }
        return new View($name, $vars, $parent);
    }

    protected function mkJson($value): Json {
        return new Json($value);
    }

    protected function mkResponse(int $statusCode = null, string $body = null): IResponse {
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

    protected function arrToActionResult(array $values): IActionResult {
        return $this->mkView(null, (array) $values);
    }

    protected function redirectWithSuccessMessage(string $uri, string $text, array $args = null): IResponse {
        $this->serviceManager['messenger']->addSuccessMessage($text, $args);
        return $this->redirect($uri);
    }

    protected function redirect(string $uri): IResponse {
        /** @var Response $response */
        $response = $this->request->response();
        $uri = prependBasePath(function () {
            return $this->request->uri()->path()->basePath();
        }, $uri);
        return $response->redirect($uri);
    }

    protected function args($name = null, bool $trim = true) {
        return $this->request->args($name, $trim);
    }

    protected function query($name = null, bool $trim = true) {
        return $this->request->query($name, $trim);
    }
}
