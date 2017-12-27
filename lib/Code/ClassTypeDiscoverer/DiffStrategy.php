<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\ClassTypeDiscoverer;

use function Morpho\Base\requireFile;

class DiffStrategy implements IDiscoverStrategy {
    public function classTypesDefinedInFile(string $filePath): array {
        $pre = ClassTypeDiscoverer::definedClassTypes();
        requireFile($filePath);
        $post = ClassTypeDiscoverer::definedClassTypes();
        return array_values(array_diff($post, $pre));
    }
}
