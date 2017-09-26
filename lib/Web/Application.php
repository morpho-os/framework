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
use Morpho\Di\IServiceManager;
use Morpho\Error\ErrorHandler;

class Application extends BaseApplication {
    public function newServiceManager($site = null): IServiceManager {
        $fs = new Fs(Fs::detectBaseDirPath(__DIR__));
        if (null === $site) {
            $site = (new SiteFactory())($fs);
        }
        $services = [
            'app'  => $this,
            'site' => $site,
            'fs' => $fs,
        ];
        if ($site->isFallbackMode()) {
            $serviceManager = new FallbackServiceManager($services);
        } else {
            $siteConfig = $site->config();
            if (isset($siteConfig['serviceManager'])) {
                $serviceManager = new $siteConfig['serviceManager']($services);
            } else {
                $serviceManager = new ServiceManager($services);
            }
        }
        return $serviceManager;
    }

    protected function configure(IServiceManager $serviceManager): void {
        parent::configure($serviceManager);

        $site = $serviceManager->get('site');

        $config = $site->config();
        $this->applyIniSettings($config['iniSettings']);
        if (isset($config['umask'])) {
            umask($config['umask']);
        }
        if (!$config['useOwnPublicDir']) {
            $site->fs()->setPublicDirPath($serviceManager->get('fs')->publicDirPath());
        }

        $moduleManager = $serviceManager->get('moduleManager');
        $moduleManager->append($site);
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
