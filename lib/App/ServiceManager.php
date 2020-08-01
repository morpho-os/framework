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

    protected function mkServerModuleIndexService() {
        return new ModuleIndex($this['serverModuleIndexer']);
    }

    protected function mkServerModuleIndexerService() {
        return new ModuleIndexer($this['serverModuleIterator'], $this->mkCache($this->cacheDirPath() . '/module-indexer'));
    }

    protected function mkServerModuleIteratorService() {
        return new ApplyingSiteConfModuleIterator($this);
    }

    protected function cacheDirPath() {
        return $this['site']->conf()['path']['cacheDirPath'];
    }

    protected function mkCache($conf): ICache {
        return new VarExportFileCache($conf);
    }
}
