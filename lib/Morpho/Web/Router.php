<?php
namespace Morpho\Web;

use Morpho\Base\NotImplementedException;

/**
 * This class applies some ideas found at:
 *     * the Drupal-8 routing system (http://drupal.org)
 *     * Rails 4.x Routing, @see http://guides.rubyonrails.org/routing.html
 */
class Router extends BaseRouter {
    public function rebuildRoutes(...$args) {
        // TODO: Implement route() method.
        //throw new NotImplementedException();
    }

    public function assemble($action, $httpMethod, $controller, $module, $params) {
        throw new NotImplementedException();
    }

    public function route($request) {
        // TODO: Implement route() method.
    }
}