<?php
namespace Morpho\Base;

class Timer {
    const DEFAULT_NAME = 'default';

    public static $requestStartedAt = null;

    protected $startedAt;

    public function __construct($name = self::DEFAULT_NAME) {
        $this->startedAt = microtime(true);
    }

    /**
     * Main method to get current time.
     *
     * @return float Number of seconds.
     */
    public function diff($sinceRequestStarted = true) {
        return microtime(true) - ($sinceRequestStarted ? self::$requestStartedAt : $this->startedAt);
    }
}

// The code below was found at PHP_Timer.
if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
    Timer::$requestStartedAt = $_SERVER['REQUEST_TIME_FLOAT'];
} elseif (isset($_SERVER['REQUEST_TIME'])) {
    Timer::$requestStartedAt = $_SERVER['REQUEST_TIME'];
} else {
    Timer::$requestStartedAt = microtime(true);
}
