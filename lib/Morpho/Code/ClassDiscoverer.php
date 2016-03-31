<?php
namespace Morpho\Code;

use Morpho\Code\ClassDiscoverer\IDiscoverStrategy;
use Morpho\Code\ClassDiscoverer\TokenStrategy;
use Morpho\Fs\Directory;

class ClassDiscoverer {
    private $discoverStrategy;

    const PHP_FILES_REG_EXP = '~\.php$~si';

    public function getClassMapForDir($dirPaths, $regexp = null, array $options = []): array {
        if (!$regexp) {
            $regexp = self::PHP_FILES_REG_EXP;
        }
        $filePaths = Directory::listFiles($dirPaths, $regexp, $options);
        $map = [];
        foreach ($filePaths as $filePath) {
            $classes = $this->getClassesForFile($filePath);
            foreach ($classes as $class) {
                if (array_key_exists($class, $map)) {
                    throw new \RuntimeException("Cannot redeclare class or interface '$class' in '$filePath'.");
                }
                $map[$class] = $filePath;
            }
        }
        return $map;
    }

    public function getClassMapForFile(string $filePath): array {
        $map = [];
        foreach ($this->getClassesForFile($filePath) as $class) {
            $map[$class] = $filePath;
        }
        return $map;
    }

    public function getClassesForFile(string $filePath): array {
        return $this->getDiscoverStrategy()->getClassesForFile($filePath);
    }

    public function setDiscoverStrategy(IDiscoverStrategy $strategy): self {
        $this->discoverStrategy = $strategy;
        return $this;
    }

    public function getDiscoverStrategy(): IDiscoverStrategy {
        if (null === $this->discoverStrategy) {
            $this->discoverStrategy = new TokenStrategy();
        }
        return $this->discoverStrategy;
    }
}
