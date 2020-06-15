<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Integration;

use Facebook\WebDriver\WebDriverBy as By;
use Morpho\Testing\BrowserTestCase;

class UiPagesTest extends BrowserTestCase {
    private string $homePageTitle = 'Hello World!';

    public function testJsTestsPage() {
        $this->browser->get($this->uri('localhost/test?bot=1'));
        $by = By::id('testing-results');
        $this->browser->waitUntilElementIsVisible($by);
        $numberOfFailedTests = $this->browser->findElement($by)->getText();
        $this->assertEquals(0, $numberOfFailedTests);
    }

    public function testHomePage() {
        $this->browser->get($this->uri());
        $this->checkPageTitle($this->homePageTitle);
    }

    public function dataForCacheAndIndexPages() {
        yield ['Routes have been rebuilt successfully', ['Caches and indexes', 'Rebuild routes']];
        yield ['The cache has been cleared successfully', ['Caches and indexes', 'Clear cache']];
    }
    /**
     * @dataProvider dataForCacheAndIndexPages
     */
    public function testCacheAndIndexPages($expectedMessage, $menuItemsText) {
        $this->browser->get($this->uri());
        $this->clickMenuItems($menuItemsText);
        $this->assertStringContainsString(
            $expectedMessage,
            $this->browser->findElement(By::cssSelector('#page-messages .alert-success'))->getText()
        );
        $this->checkPageTitle($this->homePageTitle);
    }

    private function checkPageTitle(string $expectedTitle) {
        $this->browser->waitUntilTitleIsEqual($expectedTitle);
        $this->assertSame($expectedTitle, $this->browser->findElement(By::tagName('h1'))->getText());
    }

    private function clickMenuItems(iterable $menuItemsText) {
        foreach ($menuItemsText as $menuItemText) {
            $this->browser->findElement(By::xpath("//a[contains(text(), '$menuItemText')]"))->click();
        }
    }
}
