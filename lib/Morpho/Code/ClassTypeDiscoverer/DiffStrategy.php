<?php
namespace Morpho\Code\ClassTypeDiscoverer;

use Morpho\Code\ClassTypeDiscoverer;

class DiffStrategy implements IDiscoverStrategy {
    public function definedClassTypesInFile(string $filePath): array {
        $pre = ClassTypeDiscoverer::definedClassTypes();
        require $filePath;
        $post = ClassTypeDiscoverer::definedClassTypes();
        return array_values(array_diff($post, $pre));
    }
}
