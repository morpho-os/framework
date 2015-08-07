<?php
namespace MorphoTest\Web\RouterTest\Controller;

use Morpho\Web\Controller;

class MyOtherController extends Controller {
    public function firstAction($p1, $p2) {
    }

    public function secondAction() {
    }

    /**
     * @POST /
     */
    public function thirdAction() {
    }
}
