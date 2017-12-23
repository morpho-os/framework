<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace Morpho\Web;

use Morpho\Ioc\IServiceManager;
use Morpho\Core\Application as BaseApplication;
use Morpho\Web\View\Html;

class Application extends BaseApplication {
    protected function newServiceManager(): IServiceManager {
        $appConfig = $this->config;

        // factory can have a type: string (class name) | \Closure | IBootstrapFactory (instance)
        if (isset($appConfig['factory'])) {
            if (is_object($appConfig['factory'])) {
                if ($appConfig['factory'] instanceof \Closure) {
                    $factory = $appConfig['factory']();
                } else {
                    // factory is IBootstrapFactory instance
                    $factory = $appConfig['factory'];
                }
            } else {
                // factory is a string containing a class name
                $factory = new $appConfig['factory'];
            }
        } else {
            $factory = new BootstrapFactory();
        }

        $site = $factory->newSite($appConfig);

        $siteConfig = $site->config();

        if (isset($siteConfig['iniSettings'])) {
            $this->applyIniSettings($siteConfig['iniSettings']);
        }
        if (isset($siteConfig['umask'])) {
            umask($siteConfig['umask']);
        }

        $services = [
            'app'  => $this,
            'site' => $site,
        ];
        /** @var ServiceManager $serviceManager */
        $serviceManager = $factory->newServiceManager($services);

        $serviceManager->setConfig($siteConfig['services']);

        $serviceManager->get('environment')->init();
        $serviceManager->get('errorHandler')->register();

        return $serviceManager;
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

    protected function showError(\Throwable $e): void {
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
        for ($i = 0, $n = ob_get_level(); $i < $n; $i++) {
            //ob_end_flush();
            \ob_end_clean();
        }
        die(Html::encode($message) . '.');
    }
}
