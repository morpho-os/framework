<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\View;

use ArrayObject;
use Morpho\Testing\TestCase;
use Morpho\App\Web\View\HtmlRenderer;

class HtmlRendererTest extends TestCase {
    public function testInvoke() {
        $response = new class extends ArrayObject {
            private $body;
            public function __construct() {
                parent::__construct();
                $this->headers = new ArrayObject();
            }
            public function headers() {
                return $this->headers;
            }
            public function body() {
                return $this->body;
            }
            public function setBody($body) {
                $this->body = $body;
            }
        };
        $request = new class ($response) {
            private $response;
            public function __construct($response) {
                $this->response = $response;
            }
            public function response() {
                return $this->response;
            }

        };
        $htmlSample = 'This is a <main>This is a body text.</main> page text.';
        $renderer = new class (new class {}, new class {}, 'foo/bar', $htmlSample) extends HtmlRenderer {
            private $htmlSample;

            public function __construct($templateEngine, $moduleIndex, string $pageRenderingModule, string $htmlSample) {
                parent::__construct($templateEngine, $moduleIndex, $pageRenderingModule);
                $this->htmlSample = $htmlSample;
            }

            protected function renderHtml($request) {
                return $this->htmlSample;
            }
        };

        $renderer->__invoke($request);

        $this->assertSame($htmlSample, $response->body());
        $this->assertSame(['Content-Type' => 'text/html;charset=utf-8'], $response->headers()->getArrayCopy());
    }
}
