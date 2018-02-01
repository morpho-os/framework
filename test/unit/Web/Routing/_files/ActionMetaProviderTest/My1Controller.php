<?php
namespace Morpho\Test\Unit\Web\Routing\ActionMetaProviderTest;

use Morpho\Web\Controller;

class My1FirstController extends Controller {
    public function dispatch1($request) {
    }

    public function foo1Action() {

    }
}

class Some1Class {
}

class MySecond1Controller extends \Morpho\Web\Controller {
    public function doSomething1Action() {

    }

    /**
     * @foo Bar
     */
    public function process1Action() {

    }
}

class OneMore1Class {
}

class Third1Controller extends Controller {
    public function dispatch1($request) {
    }
}