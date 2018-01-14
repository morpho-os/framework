<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Qa\Test\Unit\Web;

use const Morpho\Core\CONFIG_DIR_NAME;
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

    public function dataForInvoke_ValidHost() {
        yield [
            'hostName' => 'foo.bar.com',
            'moduleName' => 'test/example',
            'siteDirPath' => $this->getTestDirPath() . '/example',
            'siteConfig' => ['abc' => 123],
        ];
        yield [
            'hostName' => 'some-name',
            'moduleName' => 'foo/bar',
            'siteDirPath' => $this->getTestDirPath() . '/my-site',
            'siteConfig' => ['hello' => 'world'],
        ];
    }

    /**
     * @dataProvider dataForInvoke_ValidHost
     */
    public function testInvoke_ValidHost(string $hostName, string $moduleName, string $siteDirPath, array $siteConfig) {
        $siteConfigFilePath = $siteDirPath . '/' . CONFIG_DIR_NAME . '/config.php';

        $expectedSiteConfig = new \ArrayObject(array_merge($siteConfig, [
            'paths' => [
                'dirPath' => $siteDirPath,
                'configFilePath' => $siteConfigFilePath,
            ],
            'modules' => [
                $moduleName => [],
            ],
        ]));

        $_SERVER['HTTP_HOST'] = $hostName;

        $appConfig = [
            'hostMapper' => function ($hostName1) use ($siteConfigFilePath, &$called, $hostName, $moduleName, $siteDirPath) {
                $called = true;
                if ($hostName1 === $hostName) {
                    return [
                        'module' => $moduleName,
                        'paths' => [
                            'dirPath' => $siteDirPath,
                            'configFilePath' => $siteConfigFilePath,
                        ],
                    ];
                }
            }
        ];

        $map = [
            $siteConfigFilePath => $siteConfig,
        ];

        $siteFactory = new class ($map) extends SiteFactory {
            private $map;
            public function __construct(array $map) {
                $this->map = $map;
            }

            protected function loadConfigFile(string $filePath) {
                return $this->map[$filePath];
            }
        };
        $siteFactory->__invoke($appConfig);

        $site = $siteFactory->__invoke($appConfig);

        $this->assertTrue($called);
        $this->assertTrue($GLOBALS[__CLASS__ . 'Registered']);

        $this->assertSame($hostName, $site->hostName());
        $this->assertSame($moduleName, $site->moduleName());
        $this->assertEquals($expectedSiteConfig, $site->config());
    }

    public function testInvoke_InvalidHost() {
        $siteFactory = new SiteFactory();

        $hostName = 'abc';
        $appConfig = [
            'hostMapper' => function ($hostName) {
            },
        ];
        $_SERVER['HTTP_HOST'] = $hostName;

        $this->expectException(BadRequestException::class, 'Unable to detect the current site');
        $siteFactory->__invoke($appConfig);
    }
}