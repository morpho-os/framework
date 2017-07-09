<?php
declare(strict_types=1);
namespace MorphoTest\Unit\Web;

use Morpho\Di\IServiceManager;
use Morpho\Di\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\Application;

class ApplicationTest extends TestCase {
    private $umask;

    public function setUp() {
        parent::setUp();
        $this->umask = umask();
    }

    public function tearDown() {
        umask($this->umask);
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
    public function testUmaskCanBeSetThroughSiteConfig($newUmask) {
        $application = new class extends Application {
            public function init(IServiceManager $serviceManager): void {
                parent::init($serviceManager);
            }
        };
        $newServiceManager = function () use ($newUmask) {
            $serviceManager = new ServiceManager();
            $serviceManager->set('environment', new class {
                public function init() {
                }
            });
            $serviceManager->set('errorHandler', new class {
                public function register() {
                }
            });
            $serviceManager->set('site', new class (['iniSettings' => [], 'umask' => $newUmask]) {
                private $config;
                public function __construct(array $config) {
                    $this->config = $config;
                }
                public function config() {
                    return $this->config;
                }
            });
            return $serviceManager;
        };
        $application->init($newServiceManager());

        $this->assertEquals($newUmask, umask());
    }
}