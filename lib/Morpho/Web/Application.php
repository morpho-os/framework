<?php
declare(strict_types=1);

namespace Morpho\Web;

use Morpho\Core\Application as BaseApplication;
use Morpho\Di\IServiceManager;

class Application extends BaseApplication {
    protected function createServiceManager(): IServiceManager {
        $siteManager = new SiteManager();
        $siteConfig = $siteManager->getSiteConfig();
        $services = [
            'app' => $this,
            'siteManager' => $siteManager,
        ];
        if (isset($siteConfig['serviceManager'])) {
            $serviceManager = new $siteConfig['serviceManager']($siteConfig, $services);
        } else {
            $serviceManager = new ServiceManager($siteConfig, $services);
        }
        return $serviceManager;
    }

    protected function logFailure(\Throwable $e, IServiceManager $serviceManager = null) {
        while (@ob_end_flush());

        if (null !== $serviceManager) {
            try {
                // Last chance handler.
                $serviceManager->get('errorHandler')
                    ->handleException($e);
            } catch (\Throwable $e) {
                if (!headers_sent()) {
                    header('HTTP/1.1 500 Internal Server Error');
                }
                die("Unable to handle request, please contact site's support.");
            }
        }
    }
}
