<?php
namespace Morpho\Code\ClassTypeDiscoverer;

use function Morpho\Base\requireFile;
use Morpho\Code\ClassTypeDiscoverer;

class DiffStrategy implements IDiscoverStrategy {
    public function classTypesDefinedInFile(string $filePath): array {
        $pre = ClassTypeDiscoverer::definedClassTypes();
        requireFile($filePath);
        $post = ClassTypeDiscoverer::definedClassTypes();
        return array_values(array_diff($post, $pre));
    }
}
