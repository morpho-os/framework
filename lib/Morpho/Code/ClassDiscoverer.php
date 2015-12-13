<?php
namespace Morpho\Code;

use Morpho\Code\ClassDiscoverer\IDiscoverStrategy;
use Morpho\Code\ClassDiscoverer\TokenStrategy;
use Morpho\Fs\Directory;

class ClassDiscoverer {
    private $discoverStrategy;

    const PHP_FILES_REG_EXP = '~\.php$~si';

    /**
     * @return array
     */
    public function getClassesForDir($dirPaths, $regexp = null, array $options = []) {
        if (!$regexp) {
            $regexp = self::PHP_FILES_REG_EXP;
        }
        $filePaths = Directory::listEntries($dirPaths, $regexp, $options);
        $map = array();
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

    public function getClassesForFile($filePath) {
        return $this->getDiscoverStrategy()->getClassesForFile($filePath);
    }

    public function setDiscoverStrategy(IDiscoverStrategy $strategy) {
        $this->discoverStrategy = $strategy;

        return $this;
    }

    public function getDiscoverStrategy() {
        if (null === $this->discoverStrategy) {
            $this->discoverStrategy = new TokenStrategy();
        }

        return $this->discoverStrategy;
    }
}
