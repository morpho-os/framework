<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use Morpho\Test\TestCase;
use Morpho\Web\Response;

class ResponseTest extends TestCase {
    /**
     * @var Response
     */
    private $response;

    public function setUp() {
        $this->response = new Response();
    }

    public function testIsContentEmpty() {
        $this->assertTrue($this->response->isContentEmpty());
        $this->response->setContent('foo');
        $this->assertFalse($this->response->isContentEmpty());
    }

    public function testRedirect() {
        $this->assertFalse($this->response->isRedirect());
        $this->response->redirect('/foo/bar');
        $this->assertTrue($this->response->isRedirect());
        $this->assertSame(Response::FOUND_STATUS_CODE, $this->response->statusCode());
    }

    public function testIsSuccess() {
        $this->assertTrue($this->response->isSuccess());
        $this->response->setStatusCode(Response::INTERNAL_SERVER_ERROR_STATUS_CODE);
        $this->assertFalse($this->response->isSuccess());
    }
}