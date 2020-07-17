<?php
namespace Morpho\Test\Unit\App\Web\Routing\ActionMetaProviderTest;

use Morpho\App\Web\Controller;

class MyFirst2Controller extends Controller {
    /**
     * @notAction
     */
    public function dispatch2($request) {
    }

    public function foo2() {

    }
}

class Some2Class {
}

class MySecond2Controller extends Controller {
    public function doSomething2() {

    }

    /**
     * @foo Bar
     */
    public function process2() {

    }
}

class OneMore2Class {
}

class Third2Controller extends Controller {
    /**
     * @notAction
     */
    public function dispatch2($request) {
    }
}
