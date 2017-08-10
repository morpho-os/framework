<?php
declare(strict_types = 1);
namespace Morpho\Test;

use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Morpho\Network\Http\Browser;

/**
 * @TODO: Check the https://github.com/lmc-eu/steward/blob/master/src/Test/SyntaxSugarTrait.php
 */
class BrowserTest extends TestCase {
    protected const WAIT_TIMEOUT       = 10;    // sec, how long to wait() for condition
    protected const WAIT_INTERVAL      = 1000;  // ms, how often check for condition in wait()
    protected const CONNECTION_TIMEOUT = 30000; // ms, corresponds to CURLOPT_CONNECTTIMEOUT_MS
    protected const REQUEST_TIMEOUT    = 30000; // ms, corresponds to CURLOPT_TIMEOUT_MS

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
        $this->browser = Browser::new($capabilities);
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
}