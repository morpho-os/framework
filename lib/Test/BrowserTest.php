<?php
declare(strict_types = 1);
namespace Morpho\Test;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

/**
 * @TODO: Check the https://github.com/lmc-eu/steward/blob/master/src/Test/SyntaxSugarTrait.php
 */
class BrowserTest extends TestCase {
    private const WAIT_TIMEOUT       = 10;    // sec
    private const WAIT_INTERVAL      = 1000;  // ms
    private const CONNECTION_TIMEOUT = 30000; // ms, corresponds to CURLOPT_CONNECTTIMEOUT_MS
    private const REQUEST_TIMEOUT    = 30000; // ms, corresponds to CURLOPT_TIMEOUT_MS

    /**
     * @var \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected $browser;

    protected $baseUri;

    public function setUp() {
        parent::setUp();

        $this->baseUri = TestSettings::get('siteUri');
        $capabilities = DesiredCapabilities::firefox();
        //$capabilities->setCapability('firefox_binary', '/usr/lib/firefox/firefox');
        $this->browser = RemoteWebDriver::create('http://localhost:4444/wd/hub', $capabilities, self::CONNECTION_TIMEOUT, self::REQUEST_TIMEOUT);
        /*
        $browser->manage()->timeouts()->implicitlyWait(10);
            /*->setScriptTimeout()
            ->pageLoadTimeout();*/
    }

    public function tearDown() {
        parent::tearDown();
        $this->browser->quit();
        $this->browser = null;
    }

    protected function checkLink(string $expectedUri, string $expectedText, WebDriverElement $el): void {
        $this->assertEquals($expectedUri, $el->getAttribute('href'));
        $this->assertEquals($expectedText, $el->getText());
    }

    protected function waitUntilTitleIs(string $title): void {
        $this->browser->wait(self::WAIT_TIMEOUT, self::WAIT_INTERVAL)->until(
            WebDriverExpectedCondition::titleIs($title)
        );
    }

    /*
    protected function waitEnterKey() {
        // http://codeception.com/11-12-2013/working-with-phpunit-and-selenium-webdriver.html
        if (trim(fgets(fopen("php://stdin","r"))) != chr(13)) {

        }
    }
    */

    protected function waitUntilElementIsVisible(WebDriverBy $by): void {
        $this->browser->wait(self::WAIT_TIMEOUT, self::WAIT_INTERVAL)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated($by)
        );
    }
}