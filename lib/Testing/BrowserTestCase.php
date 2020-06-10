<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Testing;

use Facebook\WebDriver\WebDriverBy as By;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Morpho\Network\Http\Browser;

/**
 * @TODO: Check the https://github.com/lmc-eu/steward/blob/master/src/Test/SyntaxSugarTrait.php
 */
class BrowserTestCase extends TestCase {
    /**
     * @var \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected $browser;

    public function setUp(): void {
        parent::setUp();
        $sut = $this->sut();
        BrowserTestSuite::startSeleniumServerOnce($sut);
        $this->browser = $this->browser();
    }

    public function tearDown(): void {
        parent::tearDown();
        if ($this->browser) {
            $this->browser->quit();
            $this->browser = null;
        }
    }

    protected function checkLink(string $expectedUri, string $expectedText, WebDriverElement $el): void {
        $this->assertEquals($expectedUri, $el->getAttribute('href'));
        $this->assertEquals($expectedText, $el->getText());
    }

    protected function checkElValue(string $expectedText, By $elSelector): void {
        $this->assertEquals($expectedText, $this->browser()->findElement($elSelector)->getAttribute('value'));
    }

    protected function browser(): Browser {
        if (null === $this->browser) {
            $browser = $this->mkBrowser();
            $this->configureBrowser($browser);
            $this->browser = $browser;
        }
        return $this->browser;
    }

    protected function mkBrowser() {
        return Browser::mk(DesiredCapabilities::firefox());
    }

    protected function configureBrowser($browser): void {
        // Can be overloaded in child classes.
    }

    protected function uri(string $relUri = null): string {
        return $this->sut()->uri()
            . (null !== $relUri ? '/' . \ltrim($relUri, '/') : '');
    }
}
