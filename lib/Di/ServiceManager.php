<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Di;

class ServiceManager implements IServiceManager {
    protected $services = [];

    protected $aliases = [];

    private $loading = [];

    public function __construct(array $services = null) {
        if (null !== $services) {
            foreach ($services as $id => $service) {
                $this->set($id, $service);
            }
        }
    }

    public function set(string $id, $service): void {
        $this->services[strtolower($id)] = $service;
        if ($service instanceof IHasServiceManager) {
            $service->setServiceManager($this);
        }
    }

    /**
     * This method uses logic found in the Symfony\Component\DependencyInjection\Container::get().
     */
    public function get(string $id) {
        // Resolve alias:
        $id = strtolower($id);
        while (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }

        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        if (isset($this->loading[$id])) {
            throw new \RuntimeException(
                sprintf(
                    "Circular reference detected for the service '%s', path: '%s'",
                    $id,
                    implode(' -> ', array_keys($this->loading))
                )
            );
        }
        $this->loading[$id] = true;
        try {
            $this->services[$id] = $service = $this->newService($id);
        } catch (\Exception $e) {
            unset($this->loading[$id]);
            throw $e;
        }
        unset($this->loading[$id]);

        return $service;
    }

    public function setAliases(array $aliases) {
        $this->aliases = $aliases;
    }

    public function setAlias(string $alias, string $name) {
        $this->aliases[$alias] = $name;
    }

    protected function beforeCreate(string $id): void {
        // Do nothing by default.
    }

    protected function afterCreate(string $id, $service): void {
        if ($service instanceof IHasServiceManager) {
            $service->setServiceManager($this);
        }
    }

    /**
     * @return mixed
     */
    protected function newService(string $id) {
        $method = 'new' . $id . 'Service';
        if (method_exists($this, $method)) {
            $this->beforeCreate($id);
            $service = $this->$method();
            $this->afterCreate($id, $service);
            return $service;
        }
        throw new ServiceNotFoundException($id);
    }
}
