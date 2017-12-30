<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Error;

use Morpho\Base\IFn;
use Morpho\Error\ExceptionHandler;
use Morpho\Test\TestCase;

class ExceptionHandlerTest extends TestCase {
    public function testListeners() {
        $exceptionHandler = new ExceptionHandler();
        $listeners = $exceptionHandler->listeners();
        $this->assertEquals(new \ArrayObject(), $listeners);
        $listeners->append(function () use (&$called) {
            $called = true;
        });
        $ifnListener = new class implements IFn {
            public $called;
            public function __invoke($value) {
                $this->called = true;
            }
        };
        $listeners->append($ifnListener);
        $exceptionHandler->handleException(new \RuntimeException());

        $this->assertTrue($called);
        $this->assertTrue($ifnListener->called);
    }
}