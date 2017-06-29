<?php
declare(strict_types=1);
namespace MorphoTest\Unit\Web;

use Morpho\Test\TestCase;
use Morpho\Web\SiteFactory;
use Morpho\Web\BadRequestException;

class SiteFactoryTest extends TestCase {
    /**
     * @var SiteFactory
     */
    private $factory;

    public function setUp() {
        $this->factory = new class () extends SiteFactory {
            // Make the protected method public for testing.
            public function detectHostName(): string {
                return parent::detectHostName();
            }
        };
    }

    public function dataForDetectHostName_ValidIps() {
        return [
            // IPv4
            [
                '192.0.2.3',
                '192.0.2.3',
            ],
            [
                '192.0.2.3',
                '192.0.2.3:1234',
            ],
            // IPv6, some cases found in OpenJDK and RFCs.
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
     * @dataProvider dataForDetectHostName_ValidIps
     */
    public function testDetectHostName_ValidIps(string $expected, string $ip) {
        $_SERVER['HTTP_HOST'] = $ip;
        $this->assertEquals(strtolower($expected), $this->factory->detectHostName());
    }

    public function dataForDetectHostName_InvalidIps() {
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
     * @dataProvider dataForDetectHostName_InvalidIps
     */
    public function testDetectHostName_InvalidIps(string $ip) {
        $_SERVER['HTTP_HOST'] = $ip;
        $this->expectException(BadRequestException::class);
        $this->factory->detectHostName();
    }

    public function testInvoke_MultiSitingEnabled() {
        $siteModuleName = 'vendor/foo-bar';
        $hostName = 'choose-me';
        $config = [
            'useMultiSiting' => true,
            'sites' => [
                'this' => 'test/failed',
                $hostName => $siteModuleName,
            ],
        ];
        $_SERVER['HTTP_HOST'] = $hostName;
        $this->assertEquals($siteModuleName, $this->factory->__invoke($config)->name());
    }

    public function testInvoke_MultiSitingDisabled() {
        $hostName = 'my-host';
        $config = [
            'useMultiSiting' => false,
            'sites' => [
                'this' => 'test/success',
                $hostName => 'vendor/foo-bar',
            ],
        ];
        $_SERVER['HTTP_HOST'] = $hostName;
        $this->assertEquals('test/success', $this->factory->__invoke($config)->name());
    }
}