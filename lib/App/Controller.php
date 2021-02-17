<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

use Morpho\Base\IFn;

abstract class Controller implements IFn {
    protected IRequest $request;

    public function __invoke(mixed $request): IRequest {
        $this->request = $request;
        $this->beforeEach();
        $this->runAction($request);
        $this->afterEach();
        return $request;
    }

    protected function runAction(IRequest $request): void {
        $handler = $request->handler();
        $methodName = $handler['method'];
        // @todo: ensure that is is safe to pass ...$args
        //$args = $handler['args'];
        $actionResult = $this->$methodName(/*...array_values($args)*/);
        $result = $this->handleResult($actionResult);
        if (!$result instanceof IResponse) {
            $response = $this->request->response();
            $response['result'] = $result;
        }
    }

    /**
     * Called before calling of any action.
     */
    protected function beforeEach(): void {
    }

    /**
     * Called after calling of any action.
     */
    protected function afterEach(): void {
    }

    protected function handleResult(mixed $actionResult) {
        return $actionResult;
    }
}
