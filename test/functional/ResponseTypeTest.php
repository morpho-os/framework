<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Functional;

use Morpho\Network\Http\HttpClient;
use Morpho\Testing\BrowserTestCase;

class ResponseTypeTest extends BrowserTestCase {
    public function testFoo() {
        $response = (new HttpClient())
            ->setMaxNumberOfRedirects(0)
            ->get($this->uri('/system/'), null);
        /*
        $this->assertEquals($expectedCode, $response->statusCode(), 'Response: ' . \substr($response->body(), 0, 1000));

        if (null !== $expectedTitle || null !== $expectedText) {
            $html = $response->body();

            if (null !== $expectedTitle) {
                $this->assertContains('<title>' . \htmlspecialchars($expectedTitle, ENT_QUOTES) . '</title>', $html);
                $this->assertRegExp('~<h1[^>]*>' . \preg_quote(\htmlspecialchars($expectedTitle, ENT_QUOTES), '~') . '</h1>~s', $html);
            }
            if (null !== $expectedText) {
                $this->assertContains(\htmlspecialchars($expectedText, ENT_QUOTES), $html);
            }
        }*/
    }
}
