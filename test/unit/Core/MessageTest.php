<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Qa\Test\Unit\Core;

use Morpho\Core\Message;
use Morpho\Test\TestCase;

abstract class MessageTest extends TestCase {
    public function testMessage() {
        $message = $this->newMessage();
        $this->assertInstanceOf(\ArrayObject::class, $message, 'Message is \\ArrayObject');

        $message->test = '123';
        $message['foo'] = 'bar';
        $this->assertSame(['foo' => 'bar'], $message->getArrayCopy(), 'Properties should be ignored');
    }

    abstract protected function newMessage(): Message;
}