<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

/**
 * Index of all known modules.
 */
class ModuleIndex implements \IteratorAggregate {
    private $index;
    private $indexer;
    private $loaded;

    public function __construct(IModuleIndexer $indexer) {
        $this->indexer = $indexer;
    }

    public function moduleNames(): iterable {
        $this->init();
        return \array_keys($this->index);
    }

    public function moduleExists(string $moduleName): bool {
        $this->init();
        return isset($this->index[$moduleName]);
    }

    public function module(string $moduleName): Module {
        $this->init();
        if (!isset($this->index[$moduleName])) {
            throw new \RuntimeException("Unable to get meta for the module '$moduleName'");
        }
        if (isset($this->loaded[$moduleName])) {
            return $this->loaded[$moduleName];
        }
        return $this->loaded[$moduleName] = $this->mkModule($moduleName, $this->index[$moduleName]);
    }

    public function rebuild(): void {
        $this->index = $this->loaded = null;
        $this->indexer->clear();
    }

    protected function mkModule(string $moduleName, $meta): Module {
        return new Module($moduleName, $meta);
    }

    private function init(): void {
        if (null === $this->index) {
            $this->index = $this->indexer->index();
        }
    }

    public function getIterator() {
        $this->init();
        foreach ($this->index as $moduleName => $_) {
            yield $moduleName;
        }
    }
}
