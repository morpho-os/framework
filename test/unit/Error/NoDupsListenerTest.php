<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Error;

use Morpho\Base\IFn;
use Morpho\Test\TestCase;
use Morpho\Error\NoDupsListener;

class NoDupsListenerTest extends TestCase {
    private $lockFileDirPath;

    public function setUp() {
        $this->lockFileDirPath = $this->createTmpDir();
    }

    public function testInterface() {
        $this->assertInstanceOf(IFn::class, new NoDupsListener($this->createMock(IFn::class), $this->lockFileDirPath));
    }

    public function testNoDupsOnException() {
        $listener = $this->createMock(IFn::class);
        $ex = new \Exception();
        $listener->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($ex));
        $listener = new NoDupsListener($listener, $this->lockFileDirPath);

        $listener->__invoke($ex);
        $listener->__invoke($ex);
    }
}
