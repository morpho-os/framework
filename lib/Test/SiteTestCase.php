<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace Morpho\Test;

use Morpho\Web\Application;
use Morpho\Web\Site;
use Morpho\Web\SiteFactory;
use Morpho\Web\SiteInstaller;
use Morpho\Web\Fs;

class SiteTestCase extends BrowserTestCase {
    use TDbTestCase;

    protected const DB_NAME = 'test';

    protected $site;

    private const UMASK = 0007;

    public function setUp() {
        parent::setUp();
        $site = $this->newSite();
        $this->configureSite($site);
        $this->installSite($site);
        $this->site = $site;
    }

    protected function installSite(Site $site): void {
        $serviceManager = (new Application())
            ->newServiceManager([
                'site' => $site,
                'baseDirPath' => $this->sut()->baseDirPath()
            ]);
        $siteInstaller = (new SiteInstaller($site))
            ->setServiceManager($serviceManager);

        umask(self::UMASK);

        if (!$siteInstaller->isInstalled()) {
            $siteInstaller->install($site->config()['db'], true);
        } else {
            // Update site config
            $site->writeConfig($site->config());
        }
    }

    protected function configureSite(Site $site): void {
        $config = $this->sut()->siteConfig($this->dbConfig());
        $site->setConfig($config);
        $site->isFallbackMode(true);
    }

    protected function newSite(): Site {
        return (new SiteFactory())(new Fs($this->sut()->baseDirPath()));
    }
}
