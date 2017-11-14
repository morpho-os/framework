<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Functional;

use Morpho\Network\Http\HttpClient;
use Morpho\Test\BrowserTestCase;

class StatusCodeTest extends BrowserTestCase {
    public function dataForResponseCodes() {
        return [
            [
                '/system/test', 200, null, null,
            ],
            [
                '/system/test/status400', 400, 'Bad request (400)', 'Bad request. If you sure that it is not an error then please contact technical support.'
            ],
            [
                '/system/test/status403', 403, 'Access denied (403)', 'You don\'t have enough permissions to access the requested resource.',
            ],
            [
                '/system/test/status404', 404, 'Such page does not exist (404)', null,
            ],
            [
                '/system/test/status500', 500, 'Internal error has occurred (500)', 'Please contact technical support.'
            ],
        ];
    }

    /**
     * @dataProvider dataForResponseCodes
     */
    public function testResponseCodes($relUri, $expectedCode, ?string $expectedTitle, ?string $expectedText) {
        $response = (new HttpClient())
            ->setMaxNumberOfRedirects(0)
            ->get($this->uri($relUri), null);
        $this->assertEquals($expectedCode, $response->statusCode(), 'Response: ' . substr($response->body(), 0, 1000));

        if (null !== $expectedTitle || null !== $expectedText) {
            $html = $response->body();

            if (null !== $expectedTitle) {
                $this->assertContains('<title>' . htmlspecialchars($expectedTitle, ENT_QUOTES) . '</title>', $html);
                $this->assertRegExp('~<h1[^>]*>' . preg_quote(htmlspecialchars($expectedTitle, ENT_QUOTES), '~') . '</h1>~s', $html);
            }
            if (null !== $expectedText) {
                $this->assertContains(htmlspecialchars($expectedText, ENT_QUOTES), $html);
            }
        }
    }
}