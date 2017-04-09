<?php
namespace MorphoTest\Web;

use Morpho\Test\TestCase;
use Morpho\Web\Response;

class ResponseTest extends TestCase {
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
        $this->assertEquals(Response::STATUS_CODE_302, $this->response->getStatusCode());
    }

    public function testIsSuccessful() {
        $this->assertTrue($this->response->isSuccessful());
        $this->response->setStatusCode(Response::STATUS_CODE_500);
        $this->assertFalse($this->response->isSuccessful());
    }
}