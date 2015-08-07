<?php
namespace Morpho\Code\ClassDiscoverer;

class DiffStrategy implements IDiscoverStrategy {
    public function getClassesForFile($filePath) {
        $preClasses = get_declared_classes();
        $preInterfaces = get_declared_interfaces();
        $preTraits = get_declared_traits();
        // We need to use the 'require' here, because if we will use the 'require_once',
        // the diffs below may not work.
        require $filePath;
        $postClasses = get_declared_classes();
        $postInterfaces = get_declared_interfaces();
        $postTraits = get_declared_traits();

        return array_merge(
            array_values(array_diff($postClasses, $preClasses)),
            array_values(array_diff($postInterfaces, $preInterfaces)),
            array_values(array_diff($postTraits, $preTraits))
        );
    }
}
