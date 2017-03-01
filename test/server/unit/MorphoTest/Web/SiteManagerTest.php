<?php
namespace MorphoTest\Web;

use Morpho\Test\TestCase;
use Morpho\Web\SiteManager;
use Morpho\Web\Site;

class SiteManagerTest extends TestCase {
    public function setUp() {
        $this->siteManager = new SiteManager();
        $this->siteManager->setAllSitesDirPath($this->_testDirPath());
    }

    public function testSetSite_SetsSiteAsCurrentByDefault() {
        $site = new Site(['name' => 'foo']);
        $this->siteManager->setSite($site);
        $this->assertSame($site, $this->siteManager->currentSite());
    }

    public function testSite_ThrowsExceptionForNonExistingSiteName() {
        $this->expectException('RuntimeException', "Not allowed site name was provided");
        $this->siteManager->site('nonexistent');
    }

    public function testCurrentSite_ReturnsTheSameSiteInstance() {
        $this->assertSame($this->siteManager->currentSite(), $this->siteManager->currentSite());
    }

    public function dataForCurrentSite_ReturnsDefaultSiteWhenMultiSitingDisabled() {
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
     * @dataProvider dataForCurrentSite_ReturnsDefaultSiteWhenMultiSitingDisabled
     */
    public function testCurrentSite_ReturnsDefaultSiteWhenMultiSitingDisabled($siteName) {
        $_SERVER['HTTP_HOST'] = $siteName;
        $this->siteManager->useMultiSiting(false);
        $this->assertEquals('first-site', $this->siteManager->currentSite()->name());
    }

    public function dataForCurrentSite_ReturnsSiteByHostFieldWhenMultiSitingEnabled() {
        return $this->dataWithValidSiteNames();
    }

    /**
     * @dataProvider dataForCurrentSite_ReturnsSiteByHostFieldWhenMultiSitingEnabled
     */
    public function testCurrentSite_ReturnsSiteByHostFieldWhenMultiSitingEnabled($siteName, $expectedSite) {
        $_SERVER['HTTP_HOST'] = $siteName;
        $this->siteManager->useMultiSiting(true);
        $this->assertEquals($expectedSite ?: $siteName, $this->siteManager->currentSite()->name());
    }

    public function testCurrentSite_ExitsWhenHostFieldEmptyAndMultiSitingEnabled() {
        $this->siteManager->useMultiSiting(true);
        $_SERVER['HTTP_HOST'] = '';
        $this->expectException('\Morpho\Web\BadRequestException', "Empty value of the 'Host' field");
        $this->siteManager->currentSite();
    }

    public function testCurrentSiteConfig() {
        $this->assertEquals(['foo' => 'bar'], $this->siteManager->currentSiteConfig());
    }

    public function dataForCurrentSite_SetsSiteDir() {
        return $this->dataWithValidSiteNames();
    }

    /**
     * @dataProvider dataForCurrentSite_SetsSiteDir
     */
    public function testCurrentSite_SetsSiteDir($siteName, $expectedSite) {
        $_SERVER['HTTP_HOST'] = $siteName;
        $this->siteManager->useMultiSiting(true);
        $this->assertEquals(
            $this->_testDirPath() . '/' . ($expectedSite ?: $siteName),
            $this->siteManager->currentSite()->dirPath()
        );
    }

    public function testUseMultiSiting() {
        $this->assertBoolAccessor([$this->siteManager, 'useMultiSiting'], false);
    }

    public function testCurrentSite_Ipv4WithoutPort() {
        $this->siteManager->useMultiSiting(true);
        $_SERVER['HTTP_HOST'] = '192.0.2.3';
        $this->assertEquals('by-ip', $this->siteManager->currentSite()->name());
    }

    public function testCurrentSite_Ipv4WithPort() {
        $this->siteManager->useMultiSiting(true);
        $_SERVER['HTTP_HOST'] = '192.0.2.3:1234';
        $this->assertEquals('by-ip', $this->siteManager->currentSite()->name());
    }

    public function testCurrentSite_CanUseIpv6() {
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