<?php declare(strict_types=1);
namespace Morpho\App\Web;

use Morpho\App\IAppInitializer;
use Morpho\App\ISite;
use Morpho\App\Web\View\Html;
use Morpho\Ioc\IServiceManager;
use Morpho\App\AppInitializer as BaseAppInitializer;

class AppInitializer extends BaseAppInitializer {
    public function mkSite(\ArrayObject $appConfig): ISite {
        return (new SiteFactory())($appConfig);
    }

    public function mkServiceManager(array $services): IServiceManager {
        return new ServiceManager($services);
    }

    public function init(IServiceManager $serviceManager): void {
        Environment::init();
        if (!empty($_SERVER['HTTPS']) && !isset($iniSettings['session']['cookie_secure'])) {
            \ini_set('cookie_secure', '1');
        }
        $serviceManager['errorHandler']->register();
        parent::init($serviceManager);
    }

    public function mkFallbackErrorHandler(): callable {
        return function (\Throwable $e) {
            $statusLine = null;
            if ($e instanceof NotFoundException) {
                $statusLine = Environment::httpVersion() . ' 404 Not Found';
                $message = "The requested resource was not found";
            } elseif ($e instanceof AccessDeniedException) {
                $statusLine = Environment::httpVersion() . ' 403 Forbidden';
                $message = "You don't have access to the requested resource";
            } elseif ($e instanceof BadRequestException) {
                $statusLine = Environment::httpVersion() . ' 400 Bad Request';
                $message = "Bad request, please contact site's support";
            } else {
                $statusLine = Environment::httpVersion() . ' 500 Internal Server Error';
                $message = "Unable to handle the request. Please contact site's support and try to return to this page again later";
            }
            if (!\headers_sent()) {
                // @TODO: Use http_response_code()?
                \header($statusLine);
            }
            for ($i = 0, $n = \ob_get_level(); $i < $n; $i++) {
                //ob_end_flush();
                \ob_end_clean();
            }
            echo Html::encode($message) . '.';
        };
    }
}
