<?php
//declare(strict_types=1);
namespace Morpho\Web;

use function Morpho\Base\escapeHtml;
use Morpho\Core\Application as BaseApplication;
use Morpho\Di\IServiceManager;
use Morpho\Error\ErrorHandler;

class Application extends BaseApplication {
    protected function init(IServiceManager $serviceManager) {
        parent::init($serviceManager);

        $iniSettings = $serviceManager->get('siteManager')->currentSite()->config()['iniSettings'];
        $this->applyIniSettings($iniSettings);

        if (!empty($SERVER['HTTPS']) && !isset($iniSettings['session']['cookie_secure'])) {
            ini_set('cookie_secure', true);
        }
    }

    protected function applyIniSettings(array $iniSettings, $parentName = null) {
        foreach ($iniSettings as $name => $value) {
            $settingName = $parentName ? $parentName . '.' . $name : $name;
            if (is_array($value)) {
                $this->applyIniSettings($value, $settingName);
            } else {
                ini_set($settingName, $value);
            }
        }
    }

    protected function createServiceManager(): IServiceManager {
        $siteManager = new SiteManager();
        $siteConfig = $siteManager->currentSite()->config();
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
        $header = null;
        if ($e instanceof NotFoundException) {
            $header = Environment::httpProtocolVersion() . ' 404 Not Found';
            $message = "The requested page was not found";
        } elseif ($e instanceof AccessDeniedException) {
            $header = Environment::httpProtocolVersion() . ' 403 Forbidden';
            $message = "You don't have access to the requested resource";
        } elseif ($e instanceof BadRequestException) {
            $header = Environment::httpProtocolVersion() . ' 400 Bad Request';
            $message = "Bad request, please contact site's support";
        } else {
            $header = Environment::httpProtocolVersion() . ' 500 Internal Server Error';
            $message = "Unable to handle the request. Please contact site's support and try to return to this page again later";
        }
        if (!headers_sent()) {
            // @TODO: Use http_response_code()?
            header($header);
        }
        while (@ob_end_clean());
        die(escapeHtml($message) . '.');
    }
}
