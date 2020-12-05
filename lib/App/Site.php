<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

use Throwable;

class Site implements ISite {
    protected string $name;

    protected string $moduleName;

    protected array $conf;

    private string $hostName;

    public function __construct(string $name, string $moduleName, array $conf, string $hostName) {
        $this->name = $name;
        $this->moduleName = $moduleName;
        $this->conf = $conf;
        $this->hostName = $hostName;
    }

    public function name(): string {
        return $this->name;
    }

    public function moduleName(): string {
        return $this->moduleName;
    }

    public function conf(): array {
        return $this->conf;
    }

    public function hostName(): string {
        return $this->hostName;
    }

    public function serverModuleDirPaths(): iterable {
        $moduleDirPaths = [];
        foreach ($this->conf['modules'] as $name => $conf) {
            $moduleDirPaths[] = $conf['paths']['dirPath'];
        }
        return $moduleDirPaths;
    }

    public function moduleConf(string $moduleName): array {
        return $this->conf['modules'][$moduleName];
    }

    /**
     * @param ServiceManager $serviceManager
     * @return IResponse|false
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
        } catch (Throwable $e) {
            $errorHandler = $serviceManager['errorHandler'];
            $errorHandler->handleException($e);
            //$this->trigger(new Event('error', ['exception' => $e]));
            return false;
        }
    }
}
