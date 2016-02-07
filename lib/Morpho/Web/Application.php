<?php
//declare(strict_types=1);
namespace Morpho\Web;

use Morpho\Core\Application as BaseApplication;
use Morpho\Di\IServiceManager;
use Morpho\Error\ErrorHandler;

class Application extends BaseApplication {
    protected function createServiceManager(): IServiceManager {
        $siteManager = new SiteManager();
        $siteConfig = $siteManager->getCurrentSiteConfig();
        $services = [
            'app'         => $this,
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
        if (null !== $serviceManager) {
            try {
                // Last chance handler.
                $serviceManager->get('errorHandler')
                    ->handleException($e);
            } catch (\Throwable $e) {
                if (ErrorHandler::isErrorLogEnabled()) {
                    error_log(addslashes((string)$e));
                }
            }
        }
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }
        while (@ob_end_clean());
        die("Unable to handle the request. Please contact site's support and try to return to this page again later.");
    }
}
