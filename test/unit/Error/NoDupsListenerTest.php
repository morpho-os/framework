<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Error;

use Morpho\Test\TestCase;
use Morpho\Error\NoDupsListener;
use Morpho\Error\ExceptionEvent;

class NoDupsListenerTest extends TestCase {
    public function setUp() {
        $this->lockFileDirPath = $this->createTmpDir();
    }

    public function testNoDupsOnException() {
        $listener = $this->createMock('\Morpho\Error\DumpListener');
        $ex = new \Exception();
        $listener->expects($this->once())
            ->method('onException')
            ->with($this->identicalTo($ex));
        $listener = new NoDupsListener($listener, $this->lockFileDirPath);

        $listener->onException($ex);
        $listener->onException($ex);
    }
}
