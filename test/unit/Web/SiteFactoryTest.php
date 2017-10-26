<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use Morpho\Test\TestCase;
use Morpho\Web\SiteFactory;
use Morpho\Web\BadRequestException;

class SiteFactoryTest extends TestCase {
    private $classLoaderRegisteredKey;

    public function setUp() {
        parent::setUp();
        $this->classLoaderRegisteredKey = __CLASS__ . 'classLoaderRegistered';
    }

    public function tearDown() {
        parent::tearDown();
        unset($GLOBALS[$this->classLoaderRegisteredKey]);
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
        $this->assertSame(strtolower($expected), SiteFactory::detectHostName());
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
        SiteFactory::detectHostName();
    }

    public function dataForInvoke_MultiSiting() {
        $testDirPath = $this->getTestDirPath();

        $dataSet1 = function () use ($testDirPath) {
            $publicDirPath = $testDirPath . '/random/dir';
            $siteDirPath = $testDirPath . '/example';
            $siteModuleName = 'hello/world';
            $hostName = 'some-name.org';
            $siteConfig = [
                'module' => $siteModuleName,
                'foo' => 'bar',
                'dirPath' => $siteDirPath,
            ];
            $appConfig = [
                'sites' => [
                    $hostName => $siteConfig,
                    'this' => [
                        'module' => 'test/failed',
                    ],
                ],
                'multiSiting' => false,
                'publicDirPath' => $publicDirPath,
            ];
            $expectedSiteConfig = [
                'foo' => 'bar',
                'paths' => [
                    'dirPath' => $siteDirPath,
                    'publicDirPath' => $publicDirPath,
                ],
                'modules' => [
                    $siteModuleName => [],
                    'galaxy/mars' => [],
                    'planet/earth' => []
                ],
                'test' => '123',
            ];
            return [
                $appConfig,
                $expectedSiteConfig,
                null,
                $hostName,
                $siteModuleName,
            ];
        };

        $dataSet2 = function () use ($testDirPath) {
            $publicDirPath = $testDirPath . '/foo';
            $siteDirPath = $testDirPath . '/my-site';
            $siteModuleName = 'vendor/foo-bar';
            $hostName = 'choose-me';
            $siteConfig = [
                'module' => $siteModuleName,
                'foo' => 'bar',
                'dirPath' => $siteDirPath,
            ];
            $appConfig = [
                'sites' => [
                    'this' => [
                        'module' => 'test/failed',
                    ],
                    $hostName => $siteConfig,
                ],
                'multiSiting' => true,
                'publicDirPath' => $publicDirPath,
            ];
            $expectedSiteConfig = [
                'foo' => 'bar',
                'paths' => [
                    'dirPath' => $siteDirPath,
                    'publicDirPath' => $publicDirPath,
                ],
                'modules' => [
                    $siteModuleName => [],
                    'galaxy/mars' => [],
                    'planet/earth' => []
                ],
                'test' => '123',
            ];
            return [
                $appConfig,
                $expectedSiteConfig,
                $hostName,
                $hostName,
                $siteModuleName,
            ];
        };
        yield $dataSet1();
        yield $dataSet2();
    }

    /**
     * @dataProvider dataForInvoke_MultiSiting
     */
    public function testInvoke_MultiSiting($appConfig, $expectedSiteConfig, $expectedHostName, $hostName, $siteModuleName) {
        $_SERVER['HTTP_HOST'] = $hostName;

        $siteFactory = $this->newSiteFactory();

        [$site, $newSiteConfig] = $siteFactory->__invoke($appConfig);

        $this->checkClassLoaderRegistered();
        $this->assertSame($expectedHostName, $site->hostName());
        $this->assertSame($siteModuleName, $site->moduleName());

        $this->assertSame($expectedSiteConfig, $newSiteConfig);

    }

    private function newSiteFactory() {
        return new class extends SiteFactory {
            public function loadSiteConfig(string $configFilePath) {
                return [
                    'paths' => [],
                    'modules' => [
                        'galaxy/mars',
                        'planet/earth' => [],
                    ],
                    'test' => '123',
                ];
            }
        };
    }

    private function checkClassLoaderRegistered(): void {
        $this->assertTrue($GLOBALS[$this->classLoaderRegisteredKey]);
    }
}