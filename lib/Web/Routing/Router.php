<?php
namespace Morpho\Web\Routing;

use Morpho\Di\{
    IServiceManager, IServiceManagerAware
};

/**
 * @TODO: The next statement is not true for now.
 * This class applies some ideas found at:
 *     * the Drupal-8 routing system (http://drupal.org) (@TODO: Not actual anymore?)
 *     * Rails 4.x Routing, @see http://guides.rubyonrails.org/routing.html
 */
abstract class Router implements IServiceManagerAware {
    //const MAX_PARTS_COUNT = 9;

    protected $serviceManager;

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    abstract public function route($request): void;

    abstract public function rebuildRoutes(): void;

    /*
    public function assemble(string $httpMethod, array $handler, array $params = null): string {
        throw new NotImplementedException();
    }
    */

    public function dumpRoutes(): array {
        return iterator_to_array($this->routesMeta(), false);
    }

    protected function routesMeta(): \Traversable {
        return $this->serviceManager->get('routesMetaProvider')
            ->getIterator();
    }
/**
     * @param string $uri
     * @return array

    protected function splitUri($uri) {
        $uriParts = array_slice(array_filter(explode('/', $uri), function ($value) {
            return $value !== null && $value !== '';
        }), 0, self::MAX_PARTS_COUNT);
        return $uriParts;
    }
 */
}