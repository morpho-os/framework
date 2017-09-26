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
        $browser = $this->browser();
        
        $browser->get($this->uri());

        $this->assertEquals('Installation', $browser->getTitle());

        $fallbackDbConfig = $this->site->fs()->loadFallbackConfigFile()['db'];
        $this->checkElValue($fallbackDbConfig['db'], By::id('db'));
        $this->checkElValue($fallbackDbConfig['user'], By::id('user'));
        $this->checkElValue($fallbackDbConfig['password'], By::id('password'));
        $this->checkElValue($fallbackDbConfig['host'], By::cssSelector('input[name=host]'));
        $this->checkElValue($fallbackDbConfig['port'], By::cssSelector('input[name=port]'));

        $browser->fillForm(['db' => self::DB_NAME]);

        $browser->findElement(By::id('drop-tables'))->click();
        $browser->findElement(By::id('install'))->click();

        $browser->wait(30);
        $browser->waitUntilTitleIs('Modules');

        $this->assertNotEmpty($this->site->reloadConfig()['db']);
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