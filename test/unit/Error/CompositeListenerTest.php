<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Error;

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