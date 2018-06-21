<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

use Morpho\Base\Config;
use Morpho\Base\IFn;

abstract class Controller implements IFn {
    /**
     * @var null|IRequest
     */
    protected $request;

    private $checkActionMethodExistence = false;

    public function __construct(array $config = null) {
        if (null !== $config) {
            $config = Config::check(['checkActionMethodExistence' => true], $config);
            foreach ($config as $name => $value) {
                $this->$name = $value;
            }
        }
    }

    /**
     * @param IRequest $request
     */
    public function __invoke($request): void {
        $this->resetState($request);
        $this->request = $request;
        $actionName = $request->actionName();
        if (empty($actionName)) {
            throw new \LogicException("Empty action name");
        }
        $this->beforeEach();
        $this->runAction($actionName);
        $this->afterEach();
    }

    protected function runAction(string $actionName): void {
        $methodName = $actionName . 'Action';
        if ($this->checkActionMethodExistence) {
            $actionResult = \method_exists($this, $methodName)
                ? $this->$methodName()
                : $this->mkNotFoundResult();
        } else {
            $actionResult = $this->$methodName();
        }
        //$this->request->response()['result'] = $actionResult;
        $response = $this->handleResult($actionResult);
        $this->request->setResponse($response);
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
     * @param array|null|IActionResult|IResponse|string $actionResult
     */
    abstract protected function handleResult($actionResult): IResponse;

    abstract protected function mkNotFoundResult(): IActionResult;

    protected function resetState(IRequest $request): void {
        $request->response()->resetState();
    }
}
