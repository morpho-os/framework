<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use Morpho\Web\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\Application;
use Morpho\Web\IBootstrapFactory;
use Morpho\Web\Site;

class ApplicationTest extends TestCase {
    private $umask;
    private $timezone;

    public function setUp() {
        parent::setUp();
        $this->umask = umask();
        $this->timezone = ini_get('date.timezone');
    }

    public function tearDown() {
        parent::tearDown();
        umask($this->umask);
        ini_set('date.timezone', $this->timezone);
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
        $siteConfig = array_merge(
            $this->defaultSiteConfig($this->getTestDirPath()),
            [
                'umask' => $newUmask,
            ]
        );
        $application = $this->newConfiguredApplication($siteConfig);

        $application->run();

        $this->assertSame($newUmask, umask());
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
        $siteConfig = array_merge(
            $this->defaultSiteConfig($this->getTestDirPath()),
            [
                'iniSettings' => [
                    'date.timezone' => $timeZone
                ],
            ]
        );

        $application = $this->newConfiguredApplication($siteConfig);

        $application->run();

        $this->assertSame($timeZone, ini_get('date.timezone'));
    }

    private function defaultSiteConfig($cacheDirPath) {
        return [
            'paths' => [
                'cacheDirPath' => $cacheDirPath,
            ],
            'modules' => [],
            'services' => [],
        ];
    }

    private function newConfiguredApplication($siteConfig) {
        $serviceManager = $this->createMock(ServiceManager::class);
        $serviceManager->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) {
                if ($id === 'environment') {
                    return new class { public function init() {} };
                }
                if ($id === 'errorHandler') {
                    return new class { public function register() {} };
                }
                throw new \UnexpectedValueException($id);
            }));
        $site = $this->createConfiguredMock(Site::class, [
            'config' => new \ArrayObject($siteConfig),
        ]);
        $appConfig = new \ArrayObject([
            'factory' => $this->createConfiguredMock(IBootstrapFactory::class, [
                'newSite' => $site,
                'newServiceManager' => $serviceManager,
            ]),
        ]);
        $application = new class ($appConfig) extends Application {
            public function run() {
                $this->init();
            }
        };
        return $application;
    }
}