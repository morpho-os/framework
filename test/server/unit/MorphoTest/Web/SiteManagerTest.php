<?php
namespace MorphoTest\Web;

use Morpho\Test\TestCase;
use Morpho\Web\BadRequestException;
use Morpho\Web\SiteManager;
use Morpho\Web\Site;

class SiteManagerTest extends TestCase {
    private $siteManager;

    public function setUp() {
        $this->siteManager = new SiteManager();
        $this->siteManager->setAllSitesDirPath($this->getTestDirPath());
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
            $this->getTestDirPath() . '/' . ($expectedSite ?: $siteName),
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

    public function dataForCurrentSite_Ipv6_ValidIps() {
        return [
            // Some cases found in OpenJDK and RFCs.
            [
                '[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]',
                '[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]:80',
            ],
            [
                '[1080:0:0:0:8:800:200C:417A]',
                '[1080:0:0:0:8:800:200C:417A]',
            ],
            [
                '[3ffe:2a00:100:7031::1]',
                '[3ffe:2a00:100:7031::1]',
            ],
            [
                '[1080::8:800:200C:417A]',
                '[1080::8:800:200C:417A]',
            ],
            [
                '[::192.9.5.5]',
                '[::192.9.5.5]',
            ],
            [
                '[::FFFF:129.144.52.38]',
                '[::FFFF:129.144.52.38]:80',
            ],
            [
                '[2010:836B:4179::836B:4179]',
                '[2010:836B:4179::836B:4179]',
            ],
            [
                '[::1]',
                '[::1]',
            ],
        ];
    }

    /**
     * @dataProvider dataForCurrentSite_Ipv6_ValidIps
     */
    public function testCurrentSite_Ipv6_ValidIps($expectedHost, $httpFieldValue) {
        $siteManager = $this->siteManagerForIpv6();
        $_SERVER['HTTP_HOST'] = $httpFieldValue;
        $this->assertEquals(strtolower($expectedHost), $siteManager->currentSite()->name());
    }

    public function dataForCurrentSIte_Ipv6_InvalidIps() {
        return [
            // Some cases found in OpenJDK and RFCs.
            [
                '[::foo',
            ],
            [
                "[foo",
            ],
            [
                'www.[]',
            ],
            [
                '[]',
            ],
            [
                '[].',
            ],
            [
                '[].www',
            ],
            [
                '[].www:80',
            ],
        ];
    }

    /**
     * @dataProvider dataForCurrentSIte_Ipv6_InvalidIps
     */
    public function testCurrentSite_Ipv6_InvalidIps($invalidIp) {
        $siteManager = $this->siteManagerForIpv6();
        $_SERVER['HTTP_HOST'] = $invalidIp;
        $this->expectException(BadRequestException::class);
        $siteManager->currentSite();
    }

    private function siteManagerForIpv6() {
        $siteManager = new class extends SiteManager {
            protected function resolveSiteName(string $siteName) {
                return $siteName;
            }

            protected function createSite(string $siteName): Site {
                return new Site(['name' => $siteName]);
            }
        };
        $siteManager->useMultiSiting(true);
        return $siteManager;
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