<?php
namespace MorphoTest\Web\Routing\ActionsMetaProviderTest;

use Morpho\Core\Controller;

class MyFirst2Controller extends Controller {
    public function dispatch2($request) {
    }

    public function foo2Action() {

    }
}

class Some2Class {
}

class MySecond2Controller extends \Morpho\Web\Controller {
    public function doSomething2Action() {

    }

    /**
     * @foo Bar
     */
    public function process2Action() {

    }
}

class OneMore2Class {
}

class Third2Controller extends Controller {
    public function dispatch2($request) {
    }
}