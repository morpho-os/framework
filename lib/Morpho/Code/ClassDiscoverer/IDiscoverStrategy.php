<?php
namespace Morpho\Code\ClassDiscoverer;

interface IDiscoverStrategy {
    /**
     * @return array An array of classes|interfaces|traits from file with $filePath.
     */
    public function getClassesForFile($filePath);
}
