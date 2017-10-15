<?php
namespace MorphoTest\Unit\Web\Routing\ActionMetaProviderTest;

use Morpho\Web\Controller;

class MyFirst3Controller extends Controller {
    public function dispatch3($request) {
    }

    public function foo3Action() {

    }
}

class Some3Class {
}

class MySecond3Controller extends \Morpho\Web\Controller {
    public function doSomething3Action() {

    }

    /**
     * @foo Bar
     */
    public function process3Action() {

    }
}

class OneMore3Class {
}

class Third3Controller {
    public function dispatch3($request) {
    }
}