<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\Routing;

use Morpho\Di\{
    IServiceManager, IWithServiceManager
};

/**
 * @TODO: The next statement is not true for now.
 * This class applies some ideas found at:
 *     * the Drupal-8 routing system (http://drupal.org) (@TODO: Not actual anymore?)
 *     * Rails 4.x Routing, @see http://guides.rubyonrails.org/routing.html
 */
abstract class Router implements IWithServiceManager {
    protected $serviceManager;

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    abstract public function route($request): void;

    abstract public function rebuildRoutes(): void;

/*    abstract function assemble(string $httpMethod, array $handler, array $params = null): string;*/

    public function dumpRoutes(): array {
        return iterator_to_array($this->routesMeta(), false);
    }

    protected function routesMeta(): \Traversable {
        return $this->serviceManager->get('routesMetaProvider')
            ->getIterator();
    }
}