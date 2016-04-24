<?php
namespace Morpho\Code\ClassTypeDiscoverer;

interface IDiscoverStrategy {
    /**
     * @return array An array of classes|interfaces|traits from file with $filePath.
     */
    public function definedClassTypesInFile(string $filePath): array;
}
