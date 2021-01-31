<?php declare(strict_types=1);
namespace Morpho\App;

use Morpho\Caching\ICache;
use Morpho\Caching\VarExportFileCache;
use Morpho\Ioc\ServiceManager as BaseServiceManager;

abstract class ServiceManager extends BaseServiceManager {
    protected function mkHandlerInstanceProviderService() {
        return new HandlerInstanceProvider($this);
    }

    protected function mkDispatcherService() {
        return new Dispatcher(
            $this['handlerInstanceProvider'],
            $this['eventManager']
        );
    }

    abstract protected function mkEventManagerService();

    protected function mkBackendModuleIndexService() {
        return new ModuleIndex($this['backendModuleIndexer']);
    }

    protected function mkBackendModuleIndexerService() {
        return new ModuleIndexer($this['backendModuleIterator'], $this->mkCache($this->cacheDirPath() . '/module-indexer'));
    }

    protected function mkBackendModuleIteratorService() {
        return new BackendModuleIterator($this['site']);
    }

    protected function cacheDirPath() {
        return $this['site']->conf()['paths']['cacheDirPath'];
    }

    protected function mkCache($conf): ICache {
        return new VarExportFileCache($conf);
    }
}
