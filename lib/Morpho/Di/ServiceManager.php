<?php
namespace Morpho\Di;

class ServiceManager implements IServiceManager {
    protected $loading = array();

    protected $services = array();

    protected $aliases = array();

    protected $invokable = array();

    public function set($id, $service) {
        $isInvokable = is_object($service) && method_exists($service, '__invoke');
        $id = strtolower($id);
        if ($isInvokable) {
            $this->invokable[$id] = $service;
        } else {
            $this->services[$id] = $service;
        }
    }

    /**
     * This method uses logic found in the Symfony\Component\DependencyInjection\Container::get().
     */
    public function get($id) {
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

        if (isset($this->invokable[$id])) {
            $service = $this->createFromInvokable($id);
        } else {
            $service = $this->createFromMethod($id);
        }

        $this->services[$id] = $service;

        return $service;
    }

    public function setAliases(array $aliases) {
        $this->aliases = $aliases;
    }

    public function setAlias($alias, $name) {
        $this->aliases[$alias] = $name;
    }

    protected function createFromInvokable($id) {
        if (!isset($this->invokable[$id])) {
            throw new ServiceNotFoundException($id);
        }

        $this->loading[$id] = true;

        try {
            $this->beforeCreate($id);
            $service = $this->invokable[$id]->__invoke($this);
            $this->afterCreate($service, $id);
        } catch (\Exception $e) {
            unset($this->loading[$id]);
            throw $e;
        }

        unset($this->loading[$id]);

        return $service;
    }

    protected function createFromMethod($id) {
        $method = 'create' . $id . 'Service';
        if (!method_exists($this, $method)) {
            throw new ServiceNotFoundException($id);
        }

        $this->loading[$id] = true;

        try {
            $this->beforeCreate($id);
            $service = $this->$method();
            $this->afterCreate($service, $id);
        } catch (\Exception $e) {
            unset($this->loading[$id]);
            throw $e;
        }

        unset($this->loading[$id]);

        return $service;
    }

    protected function beforeCreate($id) {
        // Do nothing by default.
    }

    protected function afterCreate($service, $id) {
        // Do nothing by default.
    }
}
