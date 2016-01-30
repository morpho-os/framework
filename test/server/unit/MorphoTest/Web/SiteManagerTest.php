<?php
namespace MorphoTest\Web;

use Morpho\Test\TestCase;
use Morpho\Web\SiteManager;
use Morpho\Web\Site;


class SiteManagerTest extends TestCase {
    public function setUp() {
        $this->siteManager = new class() extends SiteManager {
            protected function exit(string $message) {
                throw new ExitException($message);
            }
        };
        $this->siteManager->setAllSitesDirPath($this->getTestDirPath());
    }

    public function testSetSite_SetsSiteAsCurrentByDefault() {
        $site = new Site(['name' => 'foo']);
        $this->siteManager->setSite($site);
        $this->assertSame($site, $this->siteManager->getCurrentSite());
    }

    public function testGetSite_ThrowsExceptionForNonExistingSiteName() {
        $this->setExpectedException('RuntimeException', "Not allowed site name was provided");
        $this->siteManager->getSite('nonexistent');
    }

    public function testGetCurrentSite_ReturnsTheSameSiteInstance() {
        $this->assertSame($this->siteManager->getCurrentSite(), $this->siteManager->getCurrentSite());
    }

    public function dataForGetCurrentSite_ReturnsDefaultSiteWhenMultiSitingDisabled() {
        return [
            [
                '',
            ],
            [
                'bar',
            ],
            [
                'foo',
            ],
            [
                'someINvalid value',
            ],
        ];
    }

    /**
     * @dataProvider dataForGetCurrentSite_ReturnsDefaultSiteWhenMultiSitingDisabled
     */
    public function testGetCurrentSite_ReturnsDefaultSiteWhenMultiSitingDisabled($siteName) {
        $_SERVER['HTTP_HOST'] = $siteName;
        $this->siteManager->useMultiSiting(false);
        $this->assertEquals('first-site', $this->siteManager->getCurrentSite()->getName());
    }

    public function dataForGetCurrentSite_ReturnsSiteByHostFieldWhenMultiSitingEnabled() {
        return $this->dataWithValidSiteNames();
    }

    /**
     * @dataProvider dataForGetCurrentSite_ReturnsSiteByHostFieldWhenMultiSitingEnabled
     */
    public function testGetCurrentSite_ReturnsSiteByHostFieldWhenMultiSitingEnabled($siteName, $expectedSite) {
        $_SERVER['HTTP_HOST'] = $siteName;
        $this->siteManager->useMultiSiting(true);
        $this->assertEquals($expectedSite ?: $siteName, $this->siteManager->getCurrentSite()->getName());
    }

    public function testGetCurrentSite_ExitsWhenHostFieldEmptyAndMultiSitingEnabled() {
        $this->siteManager->useMultiSiting(true);
        $_SERVER['HTTP_HOST'] = '';
        $this->setExpectedException(__NAMESPACE__ . '\\ExitException', "Empty value of the 'Host' field");
        $this->siteManager->getCurrentSite();
    }

    public function testGetCurrentSiteConfig() {
        $this->assertEquals(['foo' => 'bar'], $this->siteManager->getCurrentSiteConfig());
    }

    public function dataForGetCurrentSite_SetsSiteDir() {
        return $this->dataWithValidSiteNames();
    }

    /**
     * @dataProvider dataForGetCurrentSite_SetsSiteDir
     */
    public function testGetCurrentSite_SetsSiteDir($siteName, $expectedSite) {
        $_SERVER['HTTP_HOST'] = $siteName;
        $this->siteManager->useMultiSiting(true);
        $this->assertEquals(
            $this->getTestDirPath() . '/' . ($expectedSite ?: $siteName),
            $this->siteManager->getCurrentSite()->getDirPath()
        );
    }

    public function testUseMultiSiting() {
        $this->assertBoolAccessor([$this->siteManager, 'useMultiSiting'], false);
    }

    public function testGetCurrentSite_CanUseIpv4() {
        $this->siteManager->useMultiSiting(true);
        $_SERVER['HTTP_HOST'] = '192.0.2.3';
        $this->assertEquals('by-ip', $this->siteManager->getCurrentSite()->getName());
    }

    public function testGetCurrentSite_CanUseIpv6() {
        $this->markTestIncomplete();
    }

    private function dataWithValidSiteNames() {
        return [
            [
                'foo',
                null,
            ],
            [
                'default',
                null,
            ],
            [
                'some',
                'first-site',
            ],
        ];
    }
}

class ExitException extends \RuntimeException {
}