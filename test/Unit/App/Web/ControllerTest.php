<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use Morpho\App\Web\ContentFormat;
use Morpho\App\Web\Response;
use Morpho\Testing\TestCase;
use Morpho\Base\Ok;
use Morpho\App\Web\Controller;

class ControllerTest extends TestCase {
    public function testReturnResultFromAction() {
        $val = 'test';

        $controller = new class ($val) extends Controller {
            public bool $called = false;
            private $val;

            public function __construct($val) {
                $this->val = $val;
            }

            public function someAction() {
                $this->called = true;
                return new Ok($this->val);
            }
        };

        $response = new Response();

        $request = new class ($response) {
            private $response;

            public function __construct($response) {
                $this->response = $response;
            }

            public function handler() {
                return ['method' => 'someAction'];
            }

            public function setResponse($response) {
                $this->response = $response;
            }

            public function response() {
                return $this->response;
            }
        };

        $request = $controller->__invoke($request);

        $this->assertTrue($controller->called);
        $result = $request->response()['result'];
        $this->assertEquals(new Ok($val), $result);
        $this->assertTrue($response->allowAjax());
        $this->assertSame([ContentFormat::JSON], $response->formats());
    }
}
