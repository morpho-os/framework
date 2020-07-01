<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use Morpho\App\ISite;
use const Morpho\App\CLIENT_MODULE_DIR_NAME;
use const Morpho\App\CONF_DIR_NAME;
use Morpho\Testing\TestCase;
use Morpho\App\Web\SiteFactory;
use Morpho\App\Web\BadRequestException;

class SiteFactoryTest extends TestCase {
    private string $classLoaderRegisteredKey;

    public function setUp(): void {
        parent::setUp();
        $this->classLoaderRegisteredKey = __CLASS__ . 'classLoaderRegistered';
    }

    public function tearDown(): void {
        parent::tearDown();
        unset($GLOBALS[$this->classLoaderRegisteredKey]);
    }

    public function dataForCurrentHostName_ValidIps() {
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
     * @dataProvider dataForCurrentHostName_ValidIps
     */
    public function testCurrentHostName_ValidIps(string $expected, string $ip) {
        $_SERVER['HTTP_HOST'] = $ip;
        $siteFactory = $this->mkSiteFactory();
        $this->assertSame(\strtolower($expected), $siteFactory->currentHostName());
    }

    public function dataForCurrentHostName_InvalidIps() {
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
     * @dataProvider dataForCurrentHostName_InvalidIps
     */
    public function testCurrentHostName_InvalidIps(string $ip) {
        $_SERVER['HTTP_HOST'] = $ip;
        $siteFactory = $this->mkSiteFactory();
        $this->assertFalse($siteFactory->currentHostName());
    }

    public function dataForInvoke_ValidHost() {
        yield [
            'hostName' => 'foo.bar.com',
            'moduleName' => 'test/example',
            'siteDirPath' => $this->getTestDirPath() . '/example',
            'siteConf' => ['abc' => 123],
        ];
        yield [
            'hostName' => 'some-name',
            'moduleName' => 'foo/bar',
            'siteDirPath' => $this->getTestDirPath() . '/my-site',
            'siteConf' => ['hello' => 'world'],
        ];
    }

    /**
     * @dataProvider dataForInvoke_ValidHost
     */
    public function testInvoke_ValidHost(string $hostName, string $moduleName, string $moduleDirPath, array $siteConf) {
        $_SERVER['HTTP_HOST'] = $hostName;

        $siteFactoryConf = [
            'moduleName' => $moduleName,
            'moduleDirPath' => $moduleDirPath,
            'siteConf' => $siteConf,
            'clientModuleDirPath' => $moduleDirPath . '/' . CLIENT_MODULE_DIR_NAME,
            'confFilePath' => $moduleDirPath . '/' . CONF_DIR_NAME . '/site.conf.php',
        ];
        $siteFactory = new class ($siteFactoryConf) extends SiteFactory {
            private array $conf;
            public function __construct(array $conf) {
                $this->conf = $conf;
            }

            protected function hostNameToSiteModule(string $hostName) {
                if (!in_array($hostName, ['foo.bar.com', 'some-name'], true)) {
                    return false;
                }
                return [
                    'siteModule' => $this->conf['moduleName'],
                    'path' => [
                        'dirPath' => $this->conf['moduleDirPath'],
                        'confFilePath' => $this->conf['confFilePath'],
                        'clientModuleDirPath' => $this->conf['clientModuleDirPath'],
                    ],
                ];
            }

            protected function loadConfFile(string $confFilePath): array {
                return $this->conf['siteConf'];
            }
        };

        $site = $siteFactory->__invoke();

        $this->assertInstanceOf(ISite::class, $site);

        $this->assertSame($hostName, $site->hostName());
        $this->assertSame($moduleName, $site->moduleName());

        $this->assertTrue($GLOBALS[__CLASS__ . 'Registered']);

        $expectedSiteConf = new \ArrayObject(\array_merge($siteConf, [
            'path' => [
                'dirPath' => $moduleDirPath,
                'clientModuleDirPath' => $siteFactoryConf['clientModuleDirPath'],
                'confFilePath' => $siteFactoryConf['confFilePath'],
            ],
            'module' => [
                $moduleName => [],
            ],
            'siteModule' => $moduleName,
        ]));
        $this->assertEquals($expectedSiteConf, $site->conf());
    }

    public function testInvoke_InvalidHost() {
        $siteFactory = new SiteFactory();

        $hostName = 'abc';
        $appConf = [
            'siteConfProvider' => function ($hostName) {
            },
        ];
        $_SERVER['HTTP_HOST'] = $hostName;

        $this->expectException(BadRequestException::class, 'Invalid host or site');
        $siteFactory->__invoke($appConf);
    }

    private function mkSiteFactory() {
        return new class extends SiteFactory {
            public function currentHostName() { // make the protected method public
                return parent::currentHostName();
            }
        };
    }
}
