<?php
declare(strict_types=1);
namespace MorphoTest\Functional;

use Facebook\WebDriver\WebDriverBy as By;
use Morpho\Test\BrowserTest;
use Morpho\Web\Application;
use Morpho\Web\SiteFactory;

class InstallerTest extends BrowserTest {
    private const DB_NAME = 'test';

    public function testInstallationAndClientTest() {
        $site = (new SiteFactory())->__invoke(require Application::configFilePath());

        $configFilePath = $site->configFilePath();
        if (is_file($configFilePath)) {
            unlink($configFilePath);
        }

        $this->browser->get($this->baseUri);

        $this->assertEquals('Installation', $this->browser->getTitle());

        $fallbackDbConfig = (require $site->fallbackConfigFilePath())['db'];
        $this->checkElValue($fallbackDbConfig['db'], By::id('db'));
        $this->checkElValue($fallbackDbConfig['user'], By::id('user'));
        $this->checkElValue($fallbackDbConfig['password'], By::id('password'));
        $this->checkElValue($fallbackDbConfig['host'], By::cssSelector('input[name=host]'));
        $this->checkElValue($fallbackDbConfig['port'], By::cssSelector('input[name=port]'));

        $this->browser->fillForm(['db' => self::DB_NAME]);

        $this->browser->findElement(By::id('drop-tables'))->click();
        $this->browser->findElement(By::id('install'))->click();
        $this->browser->waitUntilTitleIs('Modules');

        $this->assertNotEmpty((require $site->configFilePath())['db']);

        // @TODO: Extract to different file
        $this->runClientTests();

        /* @TODO
        browser.executeScript("window.confirm = function (){return true;}");
        browser.findElement({xpath: "//div[@id='page-messages']//*[contains(@class, 'alert-body')]"})
            .getText()
            .then(function (text) {
                assert.equal(text.trim(), "The system was installed successfully.");
                done();
            });
        */
    }

    private function runClientTests(): void {
        $this->browser->get($this->baseUri . '/system/test?selenium');
        $by = By::id('testing-results');
        $this->browser->waitUntilElementIsVisible($by);
        $numberOfFailedTests = $this->browser->findElement($by)->getText();
        $this->assertEquals(0, $numberOfFailedTests);
    }
}