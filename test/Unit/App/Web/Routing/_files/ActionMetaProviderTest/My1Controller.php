<?php
namespace Morpho\Test\Unit\App\Web\Routing\ActionMetaProviderTest;

use Morpho\App\Web\Controller;

class My1FirstController extends Controller {
    /**
     * @notAction
     */
    public function dispatch1($request) {
    }

    public function foo1() {

    }
}

class Some1Class {
}

class MySecond1Controller extends \Morpho\App\Web\Controller {
    public function doSomething1() {

    }

    /**
     * @foo Bar
     */
    public function process1() {

    }
}

class OneMore1Class {
}

class Third1Controller extends Controller {
    /**
     * @notAction
     */
    public function dispatch1($request) {
    }
}
