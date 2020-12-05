<?php declare(strict_types=1);
namespace Morpho\App\Web;

use Morpho\App\AppInitializer as BaseAppInitializer;
use function ini_set;

class AppInitializer extends BaseAppInitializer {
    public function init(): void {
        Env::init();

        $siteConf = $this->serviceManager['site']->conf();
        $this->applySiteConf($siteConf);

        if (!empty($_SERVER['HTTPS']) && !isset($iniSettings['session']['cookie_secure'])) {
            ini_set('cookie_secure', '1');
        }
        ErrorHandler::trackErrors(function () {
            $this->serviceManager['errorHandler']->register();
        });
    }
}
