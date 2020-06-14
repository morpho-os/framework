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
            var_dump('in __invoke()');
            /** @var IRequest $request */
            $request = $serviceManager['request'];
            var_dump('before route');
            $serviceManager['router']->route($request);
            var_dump('after route');
            $serviceManager['dispatcher']->dispatch($request);
            var_dump('after dispatch');
            $response = $request->response();
            var_dump('before send');
            $response->send();
            var_dump('after send');
            return $response;
        } catch (\Throwable $e) {
            d($e);
            $errorHandler = $serviceManager['errorHandler'];
            $errorHandler->handleException($e);
            //$this->trigger(new Event('error', ['exception' => $e]));
            return false;
        }
    }
}
