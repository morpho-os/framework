<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Integration;

use Morpho\Network\Http\HttpClient;
use Morpho\Testing\BrowserTestCase;

class ErrorPagesTest extends BrowserTestCase {
    public function dataForResponseCodes() {
        return [
            [
                '/localhost/test',
                200,
                null,
                null,
            ],
            [
                '/localhost/test/status400',
                400,
                'Bad request (400)',
                'The server can\'t process your request. Contact technical support if you think that this page should show different result.',
            ],
            [
                '/localhost/test/status403',
                403,
                'Access denied (403)',
                'You don\'t have enough permissions to access the requested resource. Contact technical support if you think that this page should show different result.',
            ],
            [
                '/localhost/test/status404',
                404,
                'Such page does not exist (404)',
                'Contact technical support if you think that this page should show different result.',
                null,
            ],
            [
                '/localhost/test/status500',
                500,
                'Internal error has occurred (500)',
                'Please contact technical support.',
            ],
        ];
    }

    /**
     * @dataProvider dataForResponseCodes
     */
    public function testResponseCodes($relUri, $expectedCode, ?string $expectedTitle, ?string $expectedText) {
        $uri = $this->uri($relUri);
        $client = new HttpClient();
        $client->setMaxNumberOfRedirects(0);
        $response = $client->get($uri);
        $this->assertEquals($expectedCode, $response->statusCode(), 'URI: ' . $uri . "\n, Response: " . \substr($response->body(), 0, 1000));

        if (null !== $expectedTitle || null !== $expectedText) {
            $html = $response->body();

            if (null !== $expectedTitle) {
                $this->assertStringContainsString('<title>' . \htmlspecialchars($expectedTitle, ENT_QUOTES) . '</title>', $html);
                $this->assertRegExp('~<h1[^>]*>' . \preg_quote(\htmlspecialchars($expectedTitle, ENT_QUOTES), '~') . '</h1>~s', $html);
            }
            if (null !== $expectedText) {
                $this->assertStringContainsString(\htmlspecialchars($expectedText, ENT_QUOTES), $html);
            }
        }
    }
}
