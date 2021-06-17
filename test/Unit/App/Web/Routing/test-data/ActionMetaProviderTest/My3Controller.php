<?php
namespace Morpho\Test\Unit\App\Web\Routing\ActionMetaProviderTest;

use Morpho\App\Web\Controller;

class MyFirst3Controller extends Controller {
    /**
     * @notAction
     */
    public function dispatch3($request) {
    }

    public function foo3() {
    }
}

class Some3Class {
}

class MySecond3Controller extends \Morpho\App\Web\Controller {
    public function doSomething3() {
    }

    /**
     * @foo Bar
     */
    public function process3() {
    }
}

class OneMore3Class {
}

class Third3Controller {
    /**
     * @notAction
     */
    public function dispatch3($request) {
    }
}
