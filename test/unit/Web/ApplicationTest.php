<?php
declare(strict_types=1);
namespace MorphoTest\Unit\Web;

use Morpho\Di\IServiceManager;
use Morpho\Di\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\Application;

class ApplicationTest extends TestCase {
    private $umask;
    private $timezone;
    private $application;

    public function setUp() {
        parent::setUp();
        $this->application = new class extends Application {
            public function init(IServiceManager $serviceManager): void {
                parent::init($serviceManager);
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
        $application = new class extends Application {
            public function init(IServiceManager $serviceManager): void {
                parent::init($serviceManager);
            }
        };

        $config = ['iniSettings' => [], 'umask' => $newUmask];
        $application->init($this->newServiceManager($config));

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
        $config = ['iniSettings' => [
            'date.timezone' => $timeZone
        ]];
        $this->application->init($this->newServiceManager($config));

        $this->assertSame($timeZone, ini_get('date.timezone'));
    }

    private function newServiceManager(array $config): ServiceManager {
        $serviceManager = new ServiceManager();
        $serviceManager->set('environment', new class {
            public function init() {
            }
        });
        $serviceManager->set('errorHandler', new class {
            public function register() {
            }
        });
        $serviceManager->set('site', new class ($config) {
            private $config;
            public function __construct(array $config) {
                $this->config = $config;
            }
            public function config() {
                return $this->config;
            }
        });
        return $serviceManager;
    }
}