<?php
declare(strict_types=1);

namespace Morpho\Web;

use Morpho\Core\{Application as BaseApplication, ServiceManager as BaseServiceManager};

class Application extends BaseApplication {
    protected function createServiceManager(): BaseServiceManager {
        $siteManager = new SiteManager();
        $siteConfig = $siteManager->getSiteConfig();
        $services = [
            'app' => $this,
            'siteManager' => $siteManager,
        ];
        $serviceManager = isset($siteConfig['serviceManager'])
            ? new $siteConfig['serviceManager']($siteConfig, $services)
            : new ServiceManager($siteConfig, $services);
        return $serviceManager;
    }

    protected function handleErrorOrException(\Throwable $e, BaseServiceManager $serviceManager  = null): int {
        /*
        // @TODO: Try detect the current mode, if production don't show the error
        if ($serviceManager) {
            $serviceManager->get('errorHandler')
                ->handleThrowable($e);
        }
        */
        while (@ob_end_flush());
        echo '<pre>' . htmlspecialchars((string) $e, ENT_QUOTES, 'utf-8') . '</pre>';
        $exitCode = (int) $e->getCode();
        return $exitCode !== 0 ? $exitCode : 1;
    }
}
