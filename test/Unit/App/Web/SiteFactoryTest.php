<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use Morpho\App\ISite;
use Morpho\Ioc\IServiceManager;
use UnexpectedValueException;
use function strtolower;
use const Morpho\App\CLIENT_MODULE_DIR_NAME;
use const Morpho\App\CONF_DIR_NAME;
use Morpho\Testing\TestCase;
use Morpho\App\Web\SiteFactory;
use Morpho\App\Web\BadRequestException;
use const Morpho\App\SITE_CONF_FILE_NAME;

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
        $this->assertSame(strtolower($expected), $siteFactory->currentHostName());
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
        $common = [
            'paths' => [
                'clientModuleDirPath' => $this->getTestDirPath(),
                'serverModuleDirPath' => $this->getTestDirPath(),
            ],
        ];
        yield [
            'siteName' => 'bar',
            'hostName' => 'foo.bar.com',
            'moduleName' => 'test/example',
            'moduleDirPath' => $this->getTestDirPath() . '/example',
            'siteConf' => array_merge($common, ['modules' => ['abc' => [123]]]),
        ];
        yield [
            'siteName' => 'foo',
            'hostName' => 'some-name',
            'moduleName' => 'foo/bar',
            'moduleDirPath' => $this->getTestDirPath() . '/my-site',
            'siteConf' => array_merge($common, ['modules' => ['hello' => ['world']]]),
        ];
    }

    /**
     * @dataProvider dataForInvoke_ValidHost
     */
    public function testInvoke_ValidHost(string $siteName, string $hostName, string $moduleName, string $moduleDirPath, array $siteConf) {
        $_SERVER['HTTP_HOST'] = $hostName;

        $siteConfFilePath = $moduleDirPath . '/' . CONF_DIR_NAME . '/' . SITE_CONF_FILE_NAME;

        $app = new class ($siteName, $hostName, $moduleName, $moduleDirPath, $siteConfFilePath) {
            private $hostName;
            private $moduleName;
            private $moduleDirPath;
            private $siteConfFilePath;

            public function __construct($siteName, $hostName, $moduleName, $moduleDirPath, $siteConfFilePath) {
                $this->siteName = $siteName;
                $this->hostName = $hostName;
                $this->moduleName = $moduleName;
                $this->moduleDirPath = $moduleDirPath;
                $this->siteConfFilePath = $siteConfFilePath;
            }
            public function conf(): array {
                return [
                    'sites' => [
                        $this->siteName => [
                            'hosts' => [$this->hostName],
                            'module' => [
                                'name' => $this->moduleName,
                                'paths' => [
                                    'dirPath' => $this->moduleDirPath,
                                    'confFilePath' => $this->siteConfFilePath,
                                ],
                            ],
                        ],
                    ],
                ];
            }
        };
        $serviceManager = $this->mkConfiguredServiceManager($app);

        $siteFactory = new class ($siteConfFilePath, $siteConf) extends SiteFactory {
            public function __construct(string $siteConfFilePath, array $siteConf) {
                $this->siteConfFilePath = $siteConfFilePath;
                $this->siteConf = $siteConf;
            }

            protected function loadConfFile(string $confFilePath): array {
                if ($confFilePath === $this->siteConfFilePath) {
                    return $this->siteConf;
                }
                throw new UnexpectedValueException();
            }
        };
        $siteFactory->setServiceManager($serviceManager);

        $site = $siteFactory->__invoke();

        $this->assertInstanceOf(ISite::class, $site);

        $this->assertSame($hostName, $site->hostName());
        $this->assertSame($moduleName, $site->moduleName());

        $this->assertTrue($GLOBALS[__CLASS__ . 'Registered']);

        $this->assertSame($siteName, $site->name());
        $this->assertSame($moduleName, $site->moduleName());
        $this->assertSame($hostName, $site->hostName());
    }

    public function testInvoke_InvalidHost() {
        $siteFactory = new SiteFactory();

        $app = new class {
            public function conf() {
                return [
                    'sites' => [],
                ];
            }
        };
        $serviceManager = $this->mkConfiguredServiceManager($app);

        $siteFactory->setServiceManager($serviceManager);
        $hostName = 'abc';
        $_SERVER['HTTP_HOST'] = $hostName;

        $this->expectException(BadRequestException::class, 'Invalid host or site');

        $siteFactory->__invoke();
    }

    private function mkSiteFactory() {
        return new class extends SiteFactory {
            public function currentHostName() { // make the protected method public
                return parent::currentHostName();
            }
        };
    }

    private function mkConfiguredServiceManager($app) {
        $serviceManager = $this->createMock(IServiceManager::class);
        $serviceManager->expects($this->any())
            ->method('offsetGet')
            ->with('app')
            ->willReturn($app);
        return $serviceManager;
    }
}
