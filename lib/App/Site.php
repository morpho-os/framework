<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

class Site implements ISite {
    protected string $moduleName;

    protected \ArrayObject $config;

    private string $hostName;

    public function __construct(string $moduleName, \ArrayObject $config, string $hostName) {
        $this->moduleName = $moduleName;
        $this->config = $config;
        $this->hostName = $hostName;
    }

    public function moduleName(): string {
        return $this->moduleName;
    }

    public function config(): \ArrayObject {
        return $this->config;
    }

    public function hostName(): string {
        return $this->hostName;
    }

    /**
     * @param ServiceManager $serviceManager
     * @return \Morpho\App\IResponse|false
     */
    public function __invoke($serviceManager) {
        try {
            /** @var IRequest $request */
            $request = $serviceManager['request'];
            $serviceManager['router']->route($request);
            $serviceManager['dispatcher']->dispatch($request);
            $response = $request->response();
            $response->send();
            return $response;
        } catch (\Throwable $e) {
            $errorHandler = $serviceManager['errorHandler'];
            $errorHandler->handleException($e);
            //$this->trigger(new Event('error', ['exception' => $e]));
            return false;
        }
    }
}
