<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Core;

use Morpho\Core\IMessage;
use Morpho\Test\TestCase;

abstract class MessageTest extends TestCase {
    public function testParams() {
        $message = $this->newMessage();

        $params = $message->params();
        $this->assertInstanceOf(\ArrayObject::class, $params);
        $params['foo'] = 'bar';
        $this->assertSame(['foo' => 'bar'], $message->params()->getArrayCopy());

        $newParams = new \ArrayObject(['test' => '123']);
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($message->setParams($newParams));
        $this->assertSame($newParams, $message->params());

        $newParams = ['hello' => 456];
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($message->setParams($newParams));
        $this->assertSame($newParams, $message->params()->getArrayCopy());
    }

    abstract protected function newMessage(): IMessage;
}