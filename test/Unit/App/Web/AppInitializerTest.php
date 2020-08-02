<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use Morpho\App\Site;
use Morpho\App\Web\AppInitializer;
use Morpho\Error\IErrorHandler;
use Morpho\Ioc\ServiceManager;
use Morpho\Testing\TestCase;

class AppInitializerTest extends TestCase {
    private $timezone;

    public function setUp(): void {
        parent::setUp();
        $this->timezone = \ini_get('date.timezone');

    }

    public function tearDown(): void {
        parent::tearDown();
        \ini_set('date.timezone', $this->timezone);
    }

    public function dataForTimezoneCanBeSetThroughSiteConf() {
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
     * @dataProvider dataForTimezoneCanBeSetThroughSiteConf
     */
    public function testTimezoneCanBeSetThroughSiteConf(string $timeZone) {
        $siteConf = \array_merge(
            $this->mkSiteConf($this->getTestDirPath()),
            [
                'iniConf' => [
                    'date.timezone' => $timeZone
                ],
            ]
        );
        $serviceManager = $this->mkServiceManager($siteConf);

        $initializer = new AppInitializer($serviceManager);

        $initializer->init();

        $this->assertSame($timeZone, \ini_get('date.timezone'));
    }

    private function mkSiteConf(string $cacheDirPath): array {
        return [
            'path' => [
                'cacheDirPath' => $cacheDirPath,
            ],
            'module' => [],
            'service' => [],
        ];
    }

    private function mkServiceManager($siteConf) {
        $serviceManager = $this->createMock(ServiceManager::class);
        $site = $this->createConfiguredMock(Site::class, [
            'conf' => new \ArrayObject($siteConf),
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
