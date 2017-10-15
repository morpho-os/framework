<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace MorphoTest\Unit\Web;

use Morpho\Di\IServiceManager;
use Morpho\Di\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\Application;
use Morpho\Web\ModuleProvider;
use Morpho\Web\Site;

class ApplicationTest extends TestCase {
    private $umask;
    private $timezone;
    private $application;

    public function setUp() {
        parent::setUp();
        $this->application = new class extends Application {
            public function configure(IServiceManager $serviceManager): void {
                parent::configure($serviceManager);
            }
        };
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
        $config = array_merge(
            $this->defaultSiteConfig(),
            [
                'iniSettings' => [],
                'umask' => $newUmask
            ]
        );
        $this->application->configure($this->newServiceManager($config));

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
        $siteConfig = array_merge($this->defaultSiteConfig(), [
            'iniSettings' => [
                'date.timezone' => $timeZone
            ],
        ]);
        $this->application->configure($this->newServiceManager($siteConfig));

        $this->assertSame($timeZone, ini_get('date.timezone'));
    }

    private function newServiceManager(array $siteConfig): ServiceManager {
        $serviceManager = new ServiceManager();
        $serviceManager->set('environment', new class {
            public function init() {
            }
        });
        $serviceManager->set('errorHandler', new class {
            public function register() {
            }
        });
        $serviceManager->set('moduleProvider', $this->createMock(ModuleProvider::class));
        $serviceManager->set('site', $this->createConfiguredMock(Site::class, ['config' => $siteConfig]));
        return $serviceManager;
    }

    private function defaultSiteConfig() {
        return ['useOwnPublicDir' => true];
    }
}