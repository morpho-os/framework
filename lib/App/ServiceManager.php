<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

use Morpho\Caching\ICache;
use Morpho\Caching\VarExportFileCache;
use Morpho\Ioc\ServiceManager as BaseServiceManager;

abstract class ServiceManager extends BaseServiceManager {
    protected function mkHandlerInstanceProviderService() {
        return new HandlerInstanceProvider($this);
    }

    protected function mkDispatcherService() {
        return new Dispatcher($this['handlerInstanceProvider'], $this['eventManager']);
    }

    protected abstract function mkEventManagerService();

    protected function mkBackendModuleIndexService() {
        return new ModuleIndex($this['backendModuleIndexer']);
    }

    protected function mkBackendModuleIndexerService() {
        return new ModuleIndexer(
            $this['backendModuleIterator'],
            $this->mkCache($this->cacheDirPath() . '/module-indexer')
        );
    }

    protected function mkCache($conf): ICache {
        return new VarExportFileCache($conf);
    }

    protected function cacheDirPath() {
        return $this['site']->conf()['paths']['cacheDirPath'];
    }

    protected function mkBackendModuleIteratorService() {
        return new BackendModuleIterator($this['site']);
    }
}