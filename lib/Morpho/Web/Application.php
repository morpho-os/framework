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
        die($message . '.');
    }
}
