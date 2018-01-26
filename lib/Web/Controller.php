<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use function Morpho\Base\dasherize;
use function Morpho\Base\typeOf;
use Morpho\Core\IRestResource;
use Morpho\Ioc\IHasServiceManager;
use Morpho\Ioc\IServiceManager;
use Morpho\Web\Messages\Messenger;
use Morpho\Web\Session\Session;
use Morpho\Web\View\Page;
use Morpho\Core\Controller as BaseController;
use MOrpho\Core\Request;

class Controller extends BaseController implements IHasServiceManager {
    /**
     * @var \Morpho\Ioc\IServiceManager
     */
    protected $serviceManager;

    /**
     * @var \Morpho\Web\Request
     */
    protected $request;

    protected function handleActionResult(Request $request, $actionResult): void {
        if (!$request->isDispatched()) {
            return;
        }

        /** @var Response $response */
        $response = $request->response();

        if ($actionResult instanceof IRestResource || $actionResult instanceof Page) {
            $response['resource'] = $actionResult;
            return;
        }
        if (is_array($actionResult)) {
            $response['resource'] = $this->newPage($actionResult);
            return;
        }

        /** @var \Morpho\Web\Request $request */
        if ($request->isAjax()) {
            if ($actionResult instanceof Response) {
                $response['resource'] = $this->newPage();
                return;
            }
        } else {
            if ($actionResult instanceof Response) {
                $request->setResponse($actionResult);
                return;
            }
            if ($response->isRedirect()) {
                return;
            }
        }
        if (null === $actionResult) {
            $response['resource'] = $this->newPage($actionResult);
        } else {
            throw new \UnexpectedValueException('Type: ' . typeOf($actionResult));
        }
    }

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }

    protected function messenger(): Messenger {
        return $this->serviceManager->get('messenger');
    }

    protected function forward(string $actionName, string $controllerName = null, string $moduleName = null, array $routingParams = null): void {
        $request = $this->request;
        if (null !== $moduleName) {
            $request->setModuleName($moduleName);
        }
        if (null !== $controllerName) {
            $request->setControllerName($controllerName);
        }
        $request->setActionName($actionName);

        if (null !== $routingParams) {
            $request['routing'] = $routingParams;
        }

        $request->isDispatched(false);
    }

    protected function redirect($uri = null, int $httpStatusCode = null): Response {
        $request = $this->request;
        if (null === $uri) {
            $uri = $request->uri();
        }
        $uri = prependBasePath(function () {
            return $this->request->uri()->path()->basePath();
        }, $uri);
        /** @var Response $response */
        $response = $request->response();
        return $response->redirect($uri, $httpStatusCode);
    }

    protected function accessDenied(): void {
        throw new AccessDeniedException();
    }

    protected function notFound(): void {
        throw new NotFoundException();
    }

    protected function badRequest(): void {
        throw new BadRequestException();
    }

    protected function session(string $key = null): Session {
        return new Session(get_class($this) . ($key ?: ''));
    }

    protected function args($name = null, bool $trim = true) {
        return $this->request->args($name, $trim);
    }

    protected function data(array $source, $name = null, bool $trim = true) {
        return $this->request->data($source, $name, $trim);
    }

    protected function isPost(): bool {
        return $this->request->isPostMethod();
    }

    protected function post($name = null, bool $trim = true) {
        return $this->request->post($name, $trim);
    }

    protected function query($name = null, bool $trim = true) {
        return $this->request->query($name, $trim);
    }

    protected function newPage(array $vars = null): Page {
        return new Page(dasherize($this->request->actionName()), $vars);
    }

    protected function jsConfig(): \ArrayObject {
        if (!isset($this->request['jsConfig'])) {
            $this->request['jsConfig'] = new \ArrayObject();
        }
        return $this->request['jsConfig'];
    }
}