<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use Morpho\App\Site;
use Morpho\App\Web\Initializer;
use Morpho\Error\IErrorHandler;
use Morpho\Ioc\ServiceManager;
use Morpho\Testing\TestCase;

class InitializerTest extends TestCase {
    private $umask;
    private $timezone;

    public function setUp(): void {
        parent::setUp();
        $this->umask = \umask();
        $this->timezone = \ini_get('date.timezone');

    }

    public function tearDown(): void {
        parent::tearDown();
        \umask($this->umask);
        \ini_set('date.timezone', $this->timezone);
    }

    public function dataForUmaskCanBeSetThroughSiteConfig() {
        return [
            [
                0027,
            ],
            [
                0000,
            ],
            [
                0666,
            ],
        ];
    }

    /**
     * @dataProvider dataForUmaskCanBeSetThroughSiteConfig
     */
    public function testUmaskCanBeSetThroughSiteConfig(int $newUmask) {
        $siteConfig = \array_merge(
            $this->mkSiteConfig($this->getTestDirPath()),
            [
                'umask' => $newUmask,
            ]
        );
        $serviceManager = $this->mkServiceManager($siteConfig);
        /** @noinspection PhpParamsInspection */
        $initializer = new Initializer($serviceManager);

        $initializer->init();

        $this->assertSame($newUmask, \umask());
    }

    public function dataForTimezoneCanBeSetThroughSiteConfig() {
        return [
            [
                'Europe/London',
            ],
            [
                'Asia/Bangkok',
            ],
        ];
    }

    /**
     * @dataProvider dataForTimezoneCanBeSetThroughSiteConfig
     */
    public function testTimezoneCanBeSetThroughSiteConfig(string $timeZone) {
        $siteConfig = \array_merge(
            $this->mkSiteConfig($this->getTestDirPath()),
            [
                'iniConfig' => [
                    'date.timezone' => $timeZone
                ],
            ]
        );
        $serviceManager = $this->mkServiceManager($siteConfig);

        /** @noinspection PhpParamsInspection */
        $initializer = new Initializer($serviceManager);

        $initializer->init();

        $this->assertSame($timeZone, \ini_get('date.timezone'));
    }

    private function mkSiteConfig(string $cacheDirPath): array {
        return [
            'path' => [
                'cacheDirPath' => $cacheDirPath,
            ],
            'module' => [],
            'service' => [],
        ];
    }

    private function mkServiceManager($siteConfig) {
        $serviceManager = $this->createMock(ServiceManager::class);
        $site = $this->createConfiguredMock(Site::class, [
            'config' => new \ArrayObject($siteConfig),
        ]);
        $errorHandler = $this->createMock(IErrorHandler::class);
        $serviceManager->expects($this->any())
            ->method('offsetGet')
            ->will($this->returnCallback(function ($id) use ($site, $errorHandler) {
                if ($id === 'site') {
                    return $site;
                }
                if ($id === 'errorHandler') {
                    return $errorHandler;
                }
                throw new \UnexpectedValueException($id);
            }));
        return $serviceManager;
    }
}
