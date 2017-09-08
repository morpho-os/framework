<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace MorphoTest\Functional;

use Facebook\WebDriver\WebDriverBy as By;
use Morpho\Test\SiteTestCase;
use Morpho\Web\Site;
use Morpho\Web\SiteInstaller;

class InstallationTest extends SiteTestCase {
    protected function installSite(Site $site): void {
        // Don't install the site, but reset it to the initial state
        (new SiteInstaller($site))->resetToInitialState();
    }

    public function testInstallationAndClientTest() {
        $this->browser->get($this->baseUri);

        $this->assertEquals('Installation', $this->browser->getTitle());

        $fallbackDbConfig = (require $this->site->fallbackConfigFilePath())['db'];
        $this->checkElValue($fallbackDbConfig['db'], By::id('db'));
        $this->checkElValue($fallbackDbConfig['user'], By::id('user'));
        $this->checkElValue($fallbackDbConfig['password'], By::id('password'));
        $this->checkElValue($fallbackDbConfig['host'], By::cssSelector('input[name=host]'));
        $this->checkElValue($fallbackDbConfig['port'], By::cssSelector('input[name=port]'));

        $this->browser->fillForm(['db' => self::DB_NAME]);

        $this->browser->findElement(By::id('drop-tables'))->click();
        $this->browser->findElement(By::id('install'))->click();

        $this->browser->wait(30);
        $this->browser->waitUntilTitleIs('Modules');


        $this->assertNotEmpty((require $this->site->configFilePath())['db']);
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
}