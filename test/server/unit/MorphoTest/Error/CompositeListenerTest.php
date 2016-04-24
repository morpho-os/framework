<?php
namespace MorphoTest\Error;

use Morpho\Error\CompositeListener;
use Morpho\Test\TestCase;

class CompositeListenerTest extends TestCase {
    public function testOnException_SendsToElements() {
        $childListener = $this->createMock('Morpho\\Error\\IExceptionListener');
        $childListener->expects($this->once())
            ->method('onException');
        $listener = new CompositeListener([$childListener]);
        $listener->onException(new \Exception());
    }
}