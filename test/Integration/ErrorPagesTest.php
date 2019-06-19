<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Integration;

use Morpho\Network\Http\HttpClient;
use Morpho\Testing\BrowserTestCase;
use Zend\Http\Request as HttpMethods;

class ErrorPagesTest extends BrowserTestCase {
    public function dataForResponseCodes() {
        return [
            [
                HttpMethods::METHOD_GET,
                '',
                200,
                null,
                null,
            ],
            [
                HttpMethods::METHOD_GET,
                'status400',
                400,
                'Bad request (400)',
                'The server can\'t process your request. Contact technical support if you think that this page should show different result.',
            ],
            [
                HttpMethods::METHOD_GET,
                'status403',
                403,
                'Access denied (403)',
                'You don\'t have enough permissions to access the requested resource. Contact technical support if you think that this page should show different result.',
            ],
            [
                HttpMethods::METHOD_GET,
                'status404',
                404,
                'Such page does not exist (404)',
                'Contact technical support if you think that this page should show different result.',
                null,
            ],
            [
                HttpMethods::METHOD_POST,
                'status405',
                405,
                'Such request is not allowed (405)',
                "The server can't process your request. Contact technical support if you think that this page should show different result.",
            ],
            [
                HttpMethods::METHOD_GET,
                'status500',
                500,
                'Internal error has occurred (500)',
                'Please contact technical support.',
            ],
        ];
    }

    /**
     * @dataProvider dataForResponseCodes
     */
    public function testResponseCodes(string $httpMethod, string $relUri, $expectedCode, ?string $expectedTitle, ?string $expectedText) {
        $uri = $this->uri('/localhost/test/' . $relUri);
        $client = new HttpClient();
        $client->setMaxNumberOfRedirects(0);
        $response = $client->send($httpMethod, $uri);
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

    public function dataForNoDirectAccessToErrorPages() {
        yield ['bad-request'];
        yield ['forbidden'];
        yield ['not-found'];
        yield ['method-not-allowed'];
        yield ['uncaught'];
    }

    /**
     * @dataProvider dataForNoDirectAccessToErrorPages
     */
    public function testNoDirectAccessToErrorPages($action) {
        $client = new HttpClient();
        $response = $client->get($this->uri('/localhost/error/' . $action));
        $this->assertSame(404, $response->statusCode());
    }
}
