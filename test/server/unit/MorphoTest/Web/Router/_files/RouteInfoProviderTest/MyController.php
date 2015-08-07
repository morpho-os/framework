<?php
namespace MorphoTest\Web\Router\RouteInfoProviderTest;

use Morpho\Core\Controller;

class MyFirstController extends Controller {
    public function dispatch($request) {
    }

    public function fooAction() {

    }
}

class SomeClass {
}

class MySecondController extends \Morpho\Web\Controller {
    public function doSomethingAction() {

    }

    /**
     * @foo Bar
     */
    public function processAction() {

    }
}

class OneMoreClass {
}

class ThirdController extends Controller {
    public function dispatch($request) {
    }
}