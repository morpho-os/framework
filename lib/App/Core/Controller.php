<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Core;

use Morpho\Base\IFn;

abstract class Controller implements IFn {
    /**
     * @var null|Request
     */
    protected $request;

    /**
     * @param Request $request
     */
    public function __invoke($request): void {
        $request->response()->exchangeArray([]);
        if (!$request->isDispatched()) {
            throw new \LogicException('Request must be dispatched');
        }
        $action = $request->actionName();
        if (empty($action)) {
            throw new \LogicException("Empty action name");
        }
        $this->request = $request;
        $this->beforeEach();
        $method = $action . 'Action';
        if (method_exists($this, $method)) {
            $this->handleActionResult($request, $this->$method());
        }
        $this->afterEach();
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

    /**
     * @param null|Response|array|\ArrayObject $actionResult
     */
    protected function handleActionResult(Request $request, $actionResult): void {
    }
}
