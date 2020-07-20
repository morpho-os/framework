<?php declare(strict_types=1);
namespace Morpho\App;

use Morpho\Ioc\ServiceManager as BaseServiceManager;

abstract class ServiceManager extends BaseServiceManager {
    protected function mkInstanceProviderService() {
        return new InstanceProvider($this);
    }

    protected function mkDispatcherService() {
        return new Dispatcher(
            $this['instanceProvider'],
            $this['eventManager']
        );
    }

    abstract protected function mkEventManagerService();
}
