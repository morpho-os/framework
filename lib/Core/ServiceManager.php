<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Base\MethodNotFoundException;
use Morpho\Di\ServiceManager as BaseServiceManager;
use Morpho\Error\DumpListener;
use Morpho\Error\ErrorHandler;
use Morpho\Error\LogListener;
use Morpho\Error\NoDupsListener;

abstract class ServiceManager extends BaseServiceManager {
    protected $config = [];

    public function __construct(array $services = null, array $config = null) {
        parent::__construct($services);
        $this->config = (array) $config;
    }

    public function setConfig(array $config): void {
        $this->config = $config;
    }

    public function config(): array {
        return $this->config;
    }

    /**
     * Replaces the calls in form get$name() with the get($name), for example: getFoo() -> get('foo').
     */
    public function __call($method, array $args) {
        if (substr($method, 0, 3) === 'get' && strlen($method) > 3) {
            return $this->get(substr($method, 3));
        }
        throw new MethodNotFoundException($this, $method);
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
}
