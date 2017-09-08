<?php
namespace Morpho\System\Controller;

use Morpho\Web\Controller;
use Morpho\Web\ISite;
use Morpho\Web\SiteInstaller;

class InstallController extends Controller {
    public function indexAction() {
        return [
            'dbConfig' => $this->serviceManager->get('site')->config()['db'],
        ];
    }

    public function installAction() {
        $dbConfig = $this->args();

        $dbConfig += [
            'password' => '',
            'host' => '127.0.0.1',
            'port' => 3306,
        ];
        $dbConfig['driver'] = 'mysql';

        $dropTables = !empty($dbConfig['dropTables']);
        unset($dbConfig['dropTables']);

        $res = null;
        try {
            $site = $this->serviceManager->get('site');
            $siteInstaller = $this->newSiteInstaller($site);
            $siteInstaller->install(
                $dbConfig,
                $dropTables
            );
            if ($siteInstaller->isInstalled()) {
                return $this->success(['redirect' => true]);
            }
            return $this->error('Unknown error, please contact support');
        } catch (\Exception $e) {
            return $this->error((string) $e);
        }
    }

    protected function beforeEach(): void {
        parent::beforeEach();
        if ($this->newSiteInstaller($this->serviceManager->get('site'))->isInstalled()) {
            $this->accessDenied();
        }
        $this->setLayout('install');
    }

    protected function newSiteInstaller(ISite $site): SiteInstaller {
        return (new SiteInstaller($site))
            ->setServiceManager($this->serviceManager);
    }
}