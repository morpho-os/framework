<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use ArrayObject;
use Morpho\App\Controller as BaseController;
use Morpho\Base\Err;
use Morpho\Base\IHasServiceManager;
use Morpho\Base\IServiceManager;
use Morpho\Base\Ok;
use Morpho\Base\Result;

abstract class Controller extends BaseController implements IHasServiceManager {
    private IRequest $request;

    protected IServiceManager $serviceManager;

    public function setServiceManager(IServiceManager $serviceManager): self {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    public function __invoke(mixed $request): IRequest {
        return parent::__invoke($request);
    }

    protected function beforeEach($request): void {
        parent::beforeEach($request);
        $this->request = $request;
    }

    protected function request(): IRequest {
        return $this->request;
    }

    protected function redirect(string $uri = null, int $statusCode = null): IResponse {
        return $this->request->response()->redirect($uri, $statusCode);
    }

    protected function args($name = null, callable|bool $filter = true): mixed {
        return $this->request->args($name, $filter);
    }

    protected function query($name = null, callable|bool $filter = true) {
        return $this->request->query($name, $filter);
    }

    protected function post($name, callable|bool $filter = true) {
        return $this->request->post($name, $filter);
    }

    protected function jsConf(): ArrayObject {
        if (!isset($this->request['jsConf'])) {
            $this->request['jsConf'] = new ArrayObject();
        }
        return $this->request['jsConf'];
    }

    protected function messenger(): View\Messenger {
        return $this->serviceManager['messenger'];
    }

    protected function ok(mixed $val = null): Ok {
        return new Ok($val);
    }

    protected function err(mixed $val = null): Err {
        return new Err($val);
    }

    protected function pathArg(string $name = null) {
        $args = $this->request->handler()['args'];
        if (null === $name) {
            return $args;
        }
        return $args[$name];
    }

    protected function handleResult(mixed $actionResult): mixed {
        if ($actionResult instanceof Result) {
            $response = $this->request->response();
            $response->allowAjax(true)
                ->setFormats([ContentFormat::JSON]);
        }
        return $actionResult;
    }
}
