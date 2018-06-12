<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Autoloading;

use function Morpho\Base\requireFile;
use Morpho\Code\Reflection\ClassTypeDiscoverer;
use Morpho\Fs\File;

class ClassTypeMapAutoloader extends Autoloader {
    protected $processor;

    protected $searchDirPaths;

    protected $mapFilePath;

    protected $useCache = true;

    protected $map;

    /**
     * @param array|string|null $searchDirPaths
     * @param string|\Closure $processor
     */
    public function __construct(string $mapFilePath = null, $searchDirPaths = null, $processor = null, bool $useCache = true) {
        $this->mapFilePath = $mapFilePath;
        $this->searchDirPaths = $searchDirPaths;
        $this->processor = $processor;
        $this->useCache = $useCache;
    }

    public function filePath(string $class) {
        if (null === $this->map) {
            $this->map = $this->mkTypeMap();
        }
        return isset($this->map[$class]) ? $this->map[$class] : false;
    }

    public function clearMap() {
        $this->map = null;
        if (\is_file($this->mapFilePath)) {
            File::delete($this->mapFilePath);
        }
    }

    public function useCache(bool $flag = null): bool {
        if (null !== $flag) {
            $this->useCache = $flag;
        }
        return $this->useCache;
    }

    protected function mkTypeMap(): array {
        $useCache = $this->useCache;
        if ($useCache && \is_file($this->mapFilePath)) {
            return requireFile($this->mapFilePath);
        }
        $classTypeDiscoverer = new ClassTypeDiscoverer();
        $map = $classTypeDiscoverer->classTypesDefinedInDir($this->searchDirPaths, $this->processor, ['followLinks' => true]);
        if ($useCache) {
            File::write($this->mapFilePath, '<?php return ' . \var_export($map, true) . ';');
        }

        return $map;
    }
}
