<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Morpho\App\IActionResult;
use Morpho\App\IResponse;
use Morpho\App\IRequest;
use Morpho\App\Web\View\HtmlResult;
use Morpho\App\Web\View\JsonResult;
use Morpho\Ioc\IHasServiceManager;
use Morpho\Ioc\IServiceManager;
use Morpho\App\Controller as BaseController;
use function Morpho\Base\dasherize;

abstract class Controller extends BaseController implements IHasServiceManager {
    protected $parentActionResult;

    protected IServiceManager $serviceManager;

    protected $request;

    /**
     * Handles any possible ActionResult by normalizing it and setting as property of the Response. Returns the Response containing it (ActionResult).
     * @param array|null|IActionResult|IResponse|string $actionResult
     */
    protected function handleResult($actionResult): IResponse {
        $response = null;
        if (!$actionResult instanceof IActionResult) {
            $shouldMakeDefaultResult = \is_array($actionResult) || null === $actionResult;
            if ($shouldMakeDefaultResult) {
                $actionResult = $this->mkDefaultResult($actionResult);
            }/* elseif ($actionResult instanceof IActionResult) {
                if ($actionResult instanceof RedirectResult) {
                    $response = $this->redirect($actionResult->uri, $actionResult->statusCode);
                } elseif ($actionResult instanceof StatusCodeResult) {
                    $response = $this->request->response();
                    $response->setStatusCode($actionResult->statusCode);
                }
                } */ elseif ($actionResult instanceof IResponse) {
                $response = $actionResult;
                $actionResult = null;
            } elseif (\is_string($actionResult)) {
                $response = $this->request->response();
                $response->setBody($actionResult);
            } else {
                throw new \UnexpectedValueException();
            }
        }
        if (null === $response) {
            $response = $this->request->response();
        }
        $response['result'] = $actionResult;

        if ($this->parentActionResult) {
            // todo Generilize by extracting an interface for the HtmlResult when > 1 classes with setParent() will be introduced
            if ($response['result'] instanceof HtmlResult) {
                $response['result']->setParent($this->parentActionResult);
            }
        }
        return $response;
    }

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }

    /**
     * @param string|HtmlResult $parentActionResultOrPath
     * @return void
     */
    protected function setParentActionResult($parentActionResultOrPath): void {
        $this->parentActionResult = $parentActionResultOrPath;
    }

    protected function resetState(IRequest $request): void {
        parent::resetState($request);
        $this->parentActionResult = null;
    }

    /**
     * @param string|null $path
     * @param array|null|\ArrayObject $vars
     * @param HtmlResult|null|string $parent
     * @return HtmlResult
     */
    protected function mkHtmlResult(string $path = null, $vars = null, $parent = null): HtmlResult {
        if (null === $path) {
            $path = dasherize($this->request->handler()['method']);
        }
        return new HtmlResult($path, $vars, $parent);
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

    protected function mkDefaultResult(array $values = null): IActionResult {
        return $this->mkHtmlResult(null, (array) $values);
    }

    /**
     * @param mixed $value
     * @return JsonResult
     */
    protected function mkJsonResult($value): JsonResult {
        return new JsonResult($value);
    }

    protected function mkRedirectResult(string $uri, int $statusCode = null): RedirectResult {
        return (new RedirectResult($uri, $statusCode))
            ->setMessenger($this->serviceManager['messenger']);
    }

    protected function mkStatusCodeResult(int $statusCode): StatusCodeResult {
        return new StatusCodeResult($statusCode);
    }

    /**
     * Says the Response about redirect possibly changing it and returns it. Usually should not be called directly, instead the mk{$ActionResultType}Result() should be called.
    protected function redirect(string $uri, int $statusCode = null): IResponse {
        /** @var Response $response * /
        $response = $this->request->response();
        $uri = prependBasePath(function () {
            return $this->request->uri()->path()->basePath();
        }, $uri);
        return $response->redirect($uri, $statusCode);
    }
     */

    protected function args($name = null, bool $trim = true) {
        return $this->request->args($name, $trim);
    }

    protected function query($name = null, bool $trim = true) {
        return $this->request->query($name, $trim);
    }

    protected function post($name, bool $trim = true) {
        return $this->request->post($name, $trim);
    }

    protected function jsConf(): \ArrayObject {
        if (!isset($this->request['jsConf'])) {
            $this->request['jsConf'] = new \ArrayObject();
        }
        return $this->request['jsConf'];
    }

    protected function messenger(): Messages\Messenger {
        return $this->serviceManager['messenger'];
    }
}
