<?php
namespace MorphoTest\Web;

use Morpho\Test\TestCase;
use Morpho\Web\SiteManager;
use Morpho\Web\Site;

class SiteManagerTest extends TestCase {
    public function setUp() {
        $this->siteManager = new class() extends SiteManager {
            protected function exit(string $message) {
                $this->exitMessage = $message;
            }
        };
        $this->siteManager->setAllSitesDirPath($this->getTestDirPath());
        $this->siteManager->useMultiSiting(true);
    }

    public function testSetSite_SetsSiteAsCurrentByDefault() {
        $site = new Site(['name' => 'foo']);
        $this->siteManager->setSite($site);
        $this->assertSame($site, $this->siteManager->getCurrentSite());
    }

    public function testSetSite_SiteNameValidation_ThrowsExceptionForEmptySiteName() {
        $site = new Site(['name' => '']);
        $this->siteManager->setSite($site);
        $this->assertEquals("Invalid site name '' was provided.", $this->siteManager->exitMessage);
    }

    public function testGetSite_ThrowsExceptionForNonExistingSiteName() {
        $this->siteManager->getSite('nonexistent');
        $this->assertEquals("Invalid site name 'nonexistent' was provided.", $this->siteManager->exitMessage);
    }

    public function dataForGetCurrentSite_ExistingSite() {
        return [
            [
                'foo',
            ],
            [
                'bar',
            ],
        ];
    }

    /**
     * @dataProvider dataForGetCurrentSite_ExistingSite
     */
    public function testGetCurrentSite_ExistingSite($siteName) {
        $_SERVER['HTTP_HOST'] = $siteName;
        $this->assertEquals($siteName, $this->siteManager->getCurrentSite()->getName());
    }

    public function testGetCurrentSite_ReturnsTheSameSiteInstance() {
        $_SERVER['HTTP_HOST'] = 'foo';
        $this->assertSame($this->siteManager->getCurrentSite(), $this->siteManager->getCurrentSite());
    }

    public function testGetSiteConfig() {
        $_SERVER['HTTP_HOST'] = 'test';
        $this->assertEquals(['foo' => 'bar'], $this->siteManager->getSiteConfig());
    }

    public function testGetCurrentSiteDirPath() {
        $_SERVER['HTTP_HOST'] = 'foo';
        $this->assertEquals(
            $this->getTestDirPath() . '/foo',
            $this->siteManager->getCurrentSite()->getDirPath()
        );
    }

    public function testUseMultiSiting() {
        $this->assertBoolAccessor([new SiteManager, 'useMultiSiting'], false);
    }

    public function testReturnsDefaultSiteWhenMultiSitingDisabled() {
        $this->siteManager->useMultiSiting(false);
        $this->assertEquals('my-default', $this->siteManager->getCurrentSiteName());
    }
}