<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Core;

use Morpho\Core\IMessage;
use Morpho\Core\Response;

class ResponseTest extends MessageTest {
    /**
     * @var Response
     */
    private $response;

    public function setUp() {
        parent::setUp();
        $this->response = new Response();
    }

    public function testBodyAccessors() {
        $this->assertTrue($this->response->isBodyEmpty());
        $this->assertSame('', $this->response->body());
        $newBody = 'foo';
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($this->response->setBody($newBody));
        $this->assertSame($newBody, $this->response->body());
        $this->assertFalse($this->response->isBodyEmpty());
    }

    protected function newMessage(): IMessage {
        return clone $this->response;
    }
}