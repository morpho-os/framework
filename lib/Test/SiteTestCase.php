<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace Morpho\Test;

use Morpho\Web\Application;
use Morpho\Web\Site;
use Morpho\Web\SiteFactory;
use Morpho\Web\SiteInstaller;
use Morpho\Web\Fs;

class SiteTestCase extends BrowserTestCase {
    use TDbTestCase;

    protected const DB_NAME = 'test';

    protected $site;

    private const UMASK = 0007;

    public function setUp() {
        parent::setUp();
        $site = $this->newSite();
        $this->configureSite($site);
        $this->installSite($site);
        $this->site = $site;
    }

    protected function installSite(Site $site): void {
        $siteInstaller = (new SiteInstaller($site))
            ->setServiceManager((new Application())->newServiceManager($site));

        umask(self::UMASK);

        if (!$siteInstaller->isInstalled()) {
            $siteInstaller->install($site->config()['db'], true);
        } else {
            // Update site config
            $site->writeConfig($site->config());
        }
    }

    protected function configureSite(Site $site): void {
        $site->setConfig([
            'serviceManager'      => 'Morpho\Web\ServiceManager',
            'db'                  => $this->dbConfig(),
            'modules'             => [
                \Morpho\Core\VENDOR . '/system',
                \Morpho\Core\VENDOR . '/user',
            ],
            'moduleAutoloader'    => [
                'useCache' => false,
            ],
            'templateEngine'      => [
                'useCache'       => false,
                'forceCompileTs' => false,
                'nodeBinDirPath' => '/opt/nodejs/4.2.3/bin',
                'tsOptions'      => [
                    '--forceConsistentCasingInFileNames',
                    '--removeComments',
                    '--noImplicitAny',
                    '--suppressImplicitAnyIndexErrors',
                    '--noEmitOnError',
                    '--newLine LF',
                    '--allowJs',
                ],
            ],
            'errorHandler'        => [
                'addDumpListener' => true,
            ],
            'errorLogger'         => [
                'mailOnError' => false,
                'mailFrom'    => 'admin@localhost',
                'mailTo'      => 'admin@localhost',
                'logToFile'   => true,
            ],
            'throwDispatchErrors' => false,
            'iniSettings'         => [
                'session' => [
                    // Type: bool
                    'use_strict_mode'   => true,
                    // Type: string
                    'name'              => 's',
                    'serialize_handler' => 'php_serialize',
                ],
            ],
            'umask'               => self::UMASK,
            'useOwnPublicDir' => false,
        ]);
        $site->isFallbackMode(true);
    }

    protected function newSite(): Site {
        return (new SiteFactory())(new Fs(Sut::instance()->baseDirPath()));
    }
}
