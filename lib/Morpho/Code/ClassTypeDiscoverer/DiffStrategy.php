<?php
namespace Morpho\Code\ClassTypeDiscoverer;

class DiffStrategy implements IDiscoverStrategy {
    public function classTypesDefinedInFile(string $filePath): array {
        $preClasses = $this->declaredClasses();
        $preInterfaces = get_declared_interfaces();
        $preTraits = get_declared_traits();
        // We need to use the 'require' here, because if we will use the 'require_once',
        // the diffs below may not work.
        require $filePath;
        $postClasses = $this->declaredClasses();
        $postInterfaces = get_declared_interfaces();
        $postTraits = get_declared_traits();

        return array_merge(
            array_values(array_diff($postClasses, $preClasses)),
            array_values(array_diff($postInterfaces, $preInterfaces)),
            array_values(array_diff($postTraits, $preTraits))
        );
    }

    protected function declaredClasses(): array {
        return array_filter(get_declared_classes(), function ($class) {
            // Skip anonymous classes.
            return 'class@anonymous' !== substr($class, 0, 15);
        });
    }
}
