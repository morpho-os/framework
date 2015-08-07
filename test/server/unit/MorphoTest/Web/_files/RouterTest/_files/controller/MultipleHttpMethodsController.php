<?php
namespace MorphoTest\Web\RouterTest\Controller;

use Morpho\Web\Controller;

class MultipleHttpMethodsController extends Controller {
    /**
     * @GET|POST /login
     */
    public function logInAction() {

    }
}