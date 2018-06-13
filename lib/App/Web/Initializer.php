<?php declare(strict_types=1);
namespace Morpho\App\Web;

use Morpho\App\Initializer as BaseInitializer;

class Initializer extends BaseInitializer {
    public function init(): void {
        Environment::init();

        $siteConfig = $this->serviceManager['site']->config();
        $this->applySiteConfig($siteConfig);

        if (!empty($_SERVER['HTTPS']) && !isset($iniSettings['session']['cookie_secure'])) {
            \ini_set('cookie_secure', '1');
        }
        $this->serviceManager['errorHandler']->register();

        //$app = $this->serviceManager['app'];

/*        $app->on('exit', function ($response) {
            d($response);
        });*/
    }
}
