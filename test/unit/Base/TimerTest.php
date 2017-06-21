<?php declare(strict_types=1);
namespace MorphoTest\Unit\Base;

use Morpho\Base\Timer;
use const Morpho\Base\EPS;
use Morpho\Test\TestCase;

class TimerTest extends TestCase {
    public function testTime() {
        $timer = new Timer();

        usleep(10 * 1000);  // Wait 10 ms

        $time = $timer->diff(false);
        $this->assertTrue(is_float($time));
        // It seems like usleep() and other time functions don't return
        // valid result, so we use half of value == 0.010/2.
        $this->assertTrue($time - 0.005 >= -EPS);  // At least 5 ms are passed?
    }
}
