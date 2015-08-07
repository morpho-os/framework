<?php
namespace MorphoTest\Web;

use Morpho\Test\TestCase;
use Morpho\Web\SiteManager;
use Morpho\Web\Site;
use Morpho\Validator\TrueValidator;

class SiteManagerTest extends TestCase {
    public function setUp() {
        $this->siteManager = new SiteManager(array('exitOnInvalidSite' => false));
        $this->siteManager->setAllSiteDirPath($this->getTestDirPath());
        $this->siteManager->useMultiSiting(true);
    }

    public function testSetSiteSetsSiteAsCurrentByDefault() {
        $site = new Site(['name' => 'foo']);
        $this->siteManager->setSite($site);
        $this->assertSame($site, $this->siteManager->getCurrentSite());
    }

    public function testAllowsDontCheckSiteName() {
        $this->siteManager->setSiteNameValidator(new TrueValidator());
        $site = new Site();
        $this->siteManager->setSite($site);
    }

    /*
        public function testGetCurrentSite1()
        {
            $_SERVER['HTTP_HOST'] = 'foo';
            $this->assertEquals('foo', $this->siteManager->getCurrentSite()->getName());
        }

        public function testGetCurrentSite2()
        {
            $_SERVER['HTTP_HOST'] = 'bar';
            $this->assertEquals('bar', $this->siteManager->getCurrentSite()->getName());
        }

        public function testGetCurrentSiteReturnsTheSameInstance()
        {
            $_SERVER['HTTP_HOST'] = 'foo';
            $site1 = $this->siteManager->getCurrentSite();
            $site2 = $this->siteManager->getCurrentSite();
            $this->assertSame($site1, $site2);
        }

        public function testGetSiteConfig()
        {
            $_SERVER['HTTP_HOST'] = 'test';
            $config = $this->siteManager->getSiteConfig();
            $this->assertEquals(array(
                'foo' => 'bar'
            ), $config->toArray());
        }

        public function testGetCurrentSiteDirPath()
        {
            $_SERVER['HTTP_HOST'] = 'foo';
            $this->assertEquals($this->getTestDirPath() . '/foo', $this->siteManager->getCurrentSite()->getDirPath());
        }

     */
    public function testUseMultiSiting() {
        $this->assertBoolAccessor([new SiteManager, 'useMultiSiting'], false);
    }

    public function testThrowsExceptionOnSiteWithEmptyName() {
        $site = new Site();
        $this->setExpectedException('\RuntimeException', "Invalid site name '' was provided.");
        $this->siteManager->setSite($site);
    }

    public function testThrowsExceptionOnNonExistingSite() {
        $this->setExpectedException('\RuntimeException', "Invalid site name 'nonexistent' was provided.");
        $this->siteManager->getSite('nonexistent');
    }

    public function testReturnsDefaultSiteWhenMultiSitingDisabled() {
        $this->siteManager->useMultiSiting(false);
        $this->assertEquals(SiteManager::DEFAULT_SITE, $this->siteManager->getCurrentSiteName());
    }
}
