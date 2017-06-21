<?php declare(strict_types=1);
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
