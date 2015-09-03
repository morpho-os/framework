<?php
namespace Morpho\Di;

class ServiceManager implements IServiceManager {
    protected $services = [];

    protected $aliases = [];

    //protected $factories = [];

    private $loading = [];

    public function __construct(array $services = null) {
        if (null !== $services) {
            foreach ($services as $id => $service) {
                $this->set($id, $service);
            }
        }
    }

    /*
    public function setFactory(string $serviceId, $factory) {
        $this->factories[$serviceId] = $factory;
    }
    */

    public function set(string $id, $service) {
        $this->services[strtolower($id)] = $service;
        if ($service instanceof IServiceManagerAware) {
            $service->setServiceManager($this);
        }
    }

    /**
     * This method uses logic found in the Symfony\Component\DependencyInjection\Container::get().
     */
    public function get(string $id) {
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
                    "Circular reference detected for the service '%s', path: '%s'.",
                    $id,
                    implode(' -> ', array_keys($this->loading))
                )
            );
        }
        $this->loading[$id] = true;
        try {
            $this->services[$id] = $service = $this->createService($id);
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

    protected function beforeCreate(string $id) {
        // Do nothing by default.
    }

    protected function afterCreate(string $id, $service) {
        if ($service instanceof IServiceManagerAware) {
            $service->setServiceManager($this);
        }
    }

    protected function createService($id) {
        /*
        if (isset($this->factories[$id])) {
            $this->beforeCreate($id);
            $service = $this->factories[$id]();
            $this->afterCreate($id, $service);
        } else {
        }
        */
        $method = 'create' . $id . 'Service';
        if (method_exists($this, $method)) {
            $this->beforeCreate($id);
            $service = $this->$method();
            $this->afterCreate($id, $service);
            return $service;
        }
        throw new ServiceNotFoundException($id);
    }
}
