<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types = 1);
namespace Morpho\Test;

use Facebook\WebDriver\WebDriverBy as By;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Morpho\Network\Http\Browser;

/**
 * @TODO: Check the https://github.com/lmc-eu/steward/blob/master/src/Test/SyntaxSugarTrait.php
 */
class BrowserTestCase extends TestCase {
    protected const WAIT_TIMEOUT       = 10;    // sec, how long to wait() for condition
    protected const WAIT_INTERVAL      = 1000;  // ms, how often check for condition in wait()
    protected const CONNECTION_TIMEOUT = 30000; // ms, corresponds to CURLOPT_CONNECTTIMEOUT_MS
    protected const REQUEST_TIMEOUT    = 30000; // ms, corresponds to CURLOPT_TIMEOUT_MS

    /**
     * @var \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected $browser;

    /**
     * @var string
     */
    protected $baseUri;

    public function setUp() {
        parent::setUp();
        $this->baseUri = TestSettings::get('siteUri');
        //$capabilities->setCapability('firefox_binary', '/usr/lib/firefox/firefox');
        $this->browser = $this->newBrowser();
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

    protected function checkElValue(string $expectedText, By $elSelector): void {
        $this->assertEquals($expectedText, $this->browser->findElement($elSelector)->getAttribute('value'));
    }

    protected function newBrowser() {
        return Browser::new(DesiredCapabilities::firefox());
    }
}