<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace Morpho\Network\Http;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverBy as By;

class Browser extends RemoteWebDriver {
    protected const WAIT_TIMEOUT  = 10;    // sec
    protected const WAIT_INTERVAL = 1000;  // ms
    protected const CONNECTION_TIMEOUT = 30000; // ms, corresponds to CURLOPT_CONNECTTIMEOUT_MS
    protected const REQUEST_TIMEOUT    = 30000; // ms, corresponds to CURLOPT_TIMEOUT_MS

    /**
     * Timeout in sec, how long to wait() for condition
     * @var int
     */
    private $waitTimeout = self::WAIT_TIMEOUT;

    /**
     * Interval in ms, how often check for condition in wait()
     * @var int
     */
    private $waitInterval = self::WAIT_INTERVAL;

    public function setWaitTimeout(int $timeout): self {
        $this->waitTimeout = $timeout;
        return $this;
    }

    public function waitTimeout(): int {
        return $this->waitTimeout;
    }

    public function setWaitInterval(int $interval): self {
        $this->waitInterval = $interval;
        return $this;
    }

    public function waitInterval(): int {
        return $this->waitInterval;
    }

    public function fillForm(iterable $formValues): void {
        foreach ($formValues as $name => $value) {
            $this->findElement(By::name($name))->sendKeys($value);
        }
    }

    /**
     * @param \Facebook\WebDriver\Remote\DesiredCapabilities|array $capabilities
     */
    public static function new($capabilities) {
        return static::create('http://localhost:4444/wd/hub', $capabilities, self::CONNECTION_TIMEOUT, self::REQUEST_TIMEOUT);
        /*
        // @var \Facebook\WebDriver\WebDriverTimeouts
        $timeouts = $browser->manage()->timeouts();
        $timeouts->implicitlyWait(10);
            ->setScriptTimeout()
            ->pageLoadTimeout();
        */
    }

    /**
     * @param callable|\Facebook\WebDriver\WebDriverExpectedCondition $fnOrCondition
     * @param string $message
     * @param string $message
     * @return mixed
     */
    public function waitUntil($predicate, $message = '') {
        return $this->wait($this->waitTimeout, $this->waitInterval)->until($predicate, $message);
    }

    public function waitUntilTitleIs(string $expectedTitle): void {
        $this->waitUntil(WebDriverExpectedCondition::titleIs($expectedTitle));
    }

    public function waitUntilElementIsVisible(By $by): void {
        $this->waitUntil(WebDriverExpectedCondition::visibilityOfElementLocated($by));
    }

    /*
    protected function waitEnterKey() {
        // http://codeception.com/11-12-2013/working-with-phpunit-and-selenium-webdriver.html
        if (trim(fgets(fopen("php://stdin","r"))) != chr(13)) {

        }
    }
    */
}