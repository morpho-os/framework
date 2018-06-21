<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Morpho\App\IActionResult;
use Morpho\App\IResponse;
use Morpho\App\Web\View\ViewResult;
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
    protected function handleResult($actionResult): IResponse {
        $shouldMakeDefaultResult = \is_array($actionResult) || null === $actionResult;
        $response = null;
        if ($shouldMakeDefaultResult) {
            $actionResult = $this->mkDefaultResult($actionResult);
        } elseif ($actionResult instanceof IActionResult) {
            if ($actionResult instanceof RedirectResult) {
                $response = $this->redirect($actionResult->uri, $actionResult->statusCode);
            } elseif ($actionResult instanceof StatusCodeResult) {
                $response = $this->request->response();
                $response->setStatusCode($actionResult->statusCode);
            }
        } elseif ($actionResult instanceof IResponse) {
            $response = $actionResult;
            $actionResult = null;
        } elseif (\is_string($actionResult)) {
            $response = $this->request->response();
            $response->setBody($actionResult);
        } else {
            throw new \UnexpectedValueException();
        }
        if (null === $response) {
            $response = $this->request->response();
        }
        $response['result'] = $actionResult;
        return $response;
    }

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }

    protected function mkViewResult(string $name = null, $vars = null, ViewResult $parent = null): ViewResult {
        if (null === $name) {
            $name = dasherize($this->request->actionName());
        }
        return new ViewResult($name, $vars, $parent);
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

    protected function mkNotFoundResult(): IActionResult {
        return new NotFoundResult();
    }

    protected function mkBadRequestResult(): IActionResult {
        return new BadRequestResult();
    }

    protected function mkForbiddenResult(): IActionResult {
        return new ForbiddenResult();
    }

    protected function mkDefaultResult($values): IActionResult {
        return $this->mkViewResult(null, (array) $values);
    }

    protected function mkJsonResult($value): JsonResult {
        return new JsonResult($value);
    }

    protected function mkRedirectResult(string $uri, int $statusCode = null): RedirectResult {
        return new RedirectResult($uri, $statusCode, $this->serviceManager['messenger']);
    }

    protected function redirect(string $uri, int $statusCode = null): IResponse {
        /** @var Response $response */
        $response = $this->request->response();
        $uri = prependBasePath(function () {
            return $this->request->uri()->path()->basePath();
        }, $uri);
        return $response->redirect($uri, $statusCode);
    }

    protected function args($name = null, bool $trim = true) {
        return $this->request->args($name, $trim);
    }

    protected function query($name = null, bool $trim = true) {
        return $this->request->query($name, $trim);
    }

    protected function jsConfig(): \ArrayObject {
        if (!isset($this->request['jsConfig'])) {
            $this->request['jsConfig'] = new \ArrayObject();
        }
        return $this->request['jsConfig'];
    }
}
