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

    protected function handleException(\Exception $e, IServiceManager $serviceManager = null) {
        while (@ob_end_flush());
        $isDevMode = $this->isDevMode();
        $showFailure = function ($e) use ($isDevMode) {
            if ($isDevMode) {
                echo '<pre>' . htmlspecialchars((string) $e, ENT_QUOTES) . '</pre>';
            }
        };
        $showFailure($e);
        try {
            $this->logException($e, $serviceManager, $isDevMode);
        } catch (\Exception $e) {
            $showFailure($e);
        }
    }

    protected function logException(\Exception $e, IServiceManager $serviceManager = null, bool $isDevMode) {
        // @TODO
    }

    protected function isDevMode(): bool {
        // @TODO
        return true;
    }
}
