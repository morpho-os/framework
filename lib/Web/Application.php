<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace Morpho\Web;

use function Morpho\Base\escapeHtml;
use Morpho\Core\Application as BaseApplication;
use const Morpho\Core\CONFIG_FILE_NAME;
use const Morpho\Core\MODULE_DIR_PATH;
use Morpho\Di\IServiceManager;
use Morpho\Error\ErrorHandler;

class Application extends BaseApplication {
    public static function configFilePath(): string {
        return MODULE_DIR_PATH . '/' . CONFIG_FILE_NAME;
    }

    public function newServiceManager(ISite $site = null): IServiceManager {
        if (null === $site) {
            $site = (new SiteFactory())(require self::configFilePath());
        }
        $siteConfig = $site->config();
        $services = [
            'app'  => $this,
            'site' => $site,
        ];
        if ($site->isFallbackMode()) {
            $serviceManager = new FallbackServiceManager($services);
        } else {
            if (isset($siteConfig['serviceManager'])) {
                $serviceManager = new $siteConfig['serviceManager']($services);
            } else {
                $serviceManager = new ServiceManager($services);
            }
        }
        return $serviceManager;
    }

    protected function init(IServiceManager $serviceManager): void {
        parent::init($serviceManager);
        $this->configure($serviceManager->get('site'));
    }

    protected function configure($site): void {
        $config = $site->config();
        $this->applyIniSettings($config['iniSettings']);
        if (isset($config['umask'])) {
            umask($config['umask']);
        }
    }

    protected function applyIniSettings(array $iniSettings, $parentName = null): void {
        foreach ($iniSettings as $name => $value) {
            $settingName = $parentName ? $parentName . '.' . $name : $name;
            if (is_array($value)) {
                $this->applyIniSettings($value, $settingName);
            } else {
                ini_set($settingName, $value);
            }
        }
        if (!empty($_SERVER['HTTPS']) && !isset($iniSettings['session']['cookie_secure'])) {
            ini_set('cookie_secure', true);
        }
    }

    protected function logFailure(\Throwable $e, IServiceManager $serviceManager): void {
        try {
            $serviceManager->get('errorHandler')
                ->handleException($e);
        } catch (\Throwable $e) {
            if (ErrorHandler::isErrorLogEnabled()) {
                // @TODO: check how error logging works on PHP core level, remove unnecessary calls and checks.
                error_log(addslashes((string)$e));
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
        while (\ob_get_level() > 0) {
            \ob_end_clean();
        }
        die(escapeHtml($message) . '.');
    }
}
