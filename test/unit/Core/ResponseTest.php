<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Core;

use Morpho\Core\Response;
use Morpho\Test\TestCase;

class ResponseTest extends TestCase {
    private $response;

    public function setUp() {
        parent::setUp();
        $this->response = new Response();
    }

    public function testMetaAccessors() {
        $this->assertSame([], $this->response->meta()->getArrayCopy());
        $this->response->meta()['foo'] = 'bar';
        $this->assertSame('bar', $this->response->meta()['foo']);
    }

    public function testBodyAccessors() {
        $this->assertTrue($this->response->isBodyEmpty());
        $this->assertSame('', $this->response->body());
        $newBody = 'foo';
        $this->assertNull($this->response->setBody($newBody));
        $this->assertSame($newBody, $this->response->body());
        $this->assertFalse($this->response->isBodyEmpty());
    }
}