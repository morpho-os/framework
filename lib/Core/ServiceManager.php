<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Error\DumpListener;
use Morpho\Error\ErrorHandler;
use Morpho\Error\LogListener;
use Morpho\Error\NoDupsListener;
use Morpho\Di\ServiceManager as BaseServiceManager;

abstract class ServiceManager extends BaseServiceManager {
    protected $config;

    public function setConfig($config): void {
        $this->config = $config;
    }

    public function config() {
        return $this->config;
    }

    protected function newErrorHandlerService() {
        $listeners = [];
        $logListener = new LogListener($this->get('errorLogger'));
        $listeners[] = $this->config['errorHandler']['noDupsListener']
            ? new NoDupsListener($logListener)
            : $logListener;
        if ($this->config['errorHandler']['dumpListener']) {
            $listeners[] = new DumpListener();
        }
        return new ErrorHandler($listeners);
    }

    abstract protected function newRouterService(): IRouter;
}