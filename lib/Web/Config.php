<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

abstract class Config implements \ArrayAccess {
    protected $config;

    protected $pathManager;

    public function __construct($pathManager) {
        $this->pathManager = $pathManager;
    }

    public function offsetExists($key): bool {
        $this->init();
        return isset($this->config[$key]);
    }

    public function offsetSet($key, $value): void {
        $this->init();
        $this->config[$key] = $value;
    }

    public function offsetUnset($key): void {
        $this->init();
        unset($this->config[$key]);
    }

    protected function init(): void {
        if (null === $this->config) {
            $this->config = $this->load();
        }
    }

    abstract protected function load();
}