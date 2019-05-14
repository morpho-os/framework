<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Ioc;

/**
 * Implements IoC pattern and allows to use two approaches to manage dependencies:
 *     1) DI/Dependency Injection - inject/push dependent objects to the objects but not inject self
 *     2) Service Locator - inject/push self to the object and allow to pull from self
 */
class ServiceManager extends \ArrayObject implements IServiceManager {
    private const FACTORY_METHOD_PREFIX = 'mk';
    private const FACTORY_METHOD_SUFFIX = 'Service';

    protected $aliases = [];

    protected $config;

    private $loading = [];

    public function __construct(array $services = null) {
        if (null !== $services) {
            foreach ($services as $id => $service) {
                $this->offsetSet($id, $service);
            }
        }
    }

    /**
     * This method uses logic found in the Symfony\Component\DependencyInjection\Container::get().
     */
    public function offsetGet($id) {
        // Resolve alias:
        $id = \strtolower($id);
        while (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }
        if (parent::offsetExists($id)) {
            return parent::offsetGet($id);
        }

        if (isset($this->loading[$id])) {
            throw new \RuntimeException(
                \sprintf(
                    "Circular reference detected for the service '%s', path: '%s'",
                    $id,
                    \implode(' -> ', \array_keys($this->loading)) . ' -> ' . $id
                )
            );
        }
        $this->loading[$id] = true;
        try {
            $this[$id] = $service = $this->mkService($id);
        } catch (\Exception $e) {
            unset($this->loading[$id]);
            throw $e;
        }
        unset($this->loading[$id]);

        return $service;
    }

    public function offsetSet($id, $service): void {
        parent::offsetSet(\strtolower($id), $service);
        if ($service instanceof IHasServiceManager) {
            $service->setServiceManager($this);
        }
    }

    public function offsetExists($id): bool {
        // Resolve alias:
        $id = \strtolower($id);

        while (isset($this->aliases[$id]) && $this->aliases[$id] !== $id) {
            $id = $this->aliases[$id];
        }

        if (parent::offsetExists($id)) {
            return true;
        }

        $method = self::FACTORY_METHOD_PREFIX . $id . self::FACTORY_METHOD_SUFFIX;
        return \method_exists($this, $method);
    }

    public function offsetUnset($id): void {
        parent::offsetUnset(\strtolower($id));
    }

    public function setConfig($config): void {
        $this->config = $config;
    }

    public function config() {
        return $this->config;
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
    protected function mkService(string $id) {
        $method = self::FACTORY_METHOD_PREFIX . $id . self::FACTORY_METHOD_SUFFIX;
        if (\method_exists($this, $method)) {
            $this->beforeCreate($id);
            $service = $this->$method();
            $this->afterCreate($id, $service);
            return $service;
        }
        throw new ServiceNotFoundException($id);
    }
}
