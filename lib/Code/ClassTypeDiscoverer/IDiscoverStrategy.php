<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\ClassTypeDiscoverer;

interface IDiscoverStrategy {
    /**
     * @return array An array of classes|interfaces|traits from file with $filePath.
     */
    public function classTypesDefinedInFile(string $filePath): array;
}
