<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Php\Reflection;

use function array_diff;
use function array_values;
use function Morpho\Base\requireFile;

class DiffStrategy implements IDiscoverStrategy {
    /**
     * @todo: fix breakage of the order, use some heuristic like regular expressions to find out the actual order and then fix the result
     */
    public function classTypesDefinedInFile(string $filePath): array {
        $pre = ClassTypeDiscoverer::definedClassTypes();
        requireFile($filePath);
        $post = ClassTypeDiscoverer::definedClassTypes();
        return array_values(array_diff($post, $pre));
    }
}
