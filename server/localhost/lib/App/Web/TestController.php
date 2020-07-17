<?php declare(strict_types=1);
namespace Morpho\Site\Localhost\App\Web;

use Morpho\App\Web\Controller;

class TestController extends Controller {
    public function index() {
        $this->setParentViewResult('test/test');
    }

    public function status400() {
        return $this->mkBadRequestResult();
    }

    public function status403() {
        return $this->mkForbiddenResult();
    }

    public function status404() {
        return $this->mkNotFoundResult();
    }

    public function status405() {
        // For testing clients should send: POST $prefix/test/status405
    }

    public function status500() {
        throw new \RuntimeException();
    }

    /**
     * @POST
     */
    public function redirect() {
        return $this->mkJsonResult([
            'ok' => [
                'redirect' => '/go/to/linux',
            ]
        ]);
    }

    /**
     * @POST
     */
    public function error() {
        return $this->mkJsonResult([
            'err' => [
                [
                   "text" => 'This is a<br>multiple line error with the {0} and {1} arguments.',
                    "args" => [
                        "Should<br>not be escaped",
                        "Contains '"
                    ]
               ],
            ]
        ]);
    }
}
