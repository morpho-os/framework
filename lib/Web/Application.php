<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace Morpho\Web;

use function Morpho\Base\escapeHtml;
use Morpho\Di\IServiceManager;
use Morpho\Error\ErrorHandler;

class Application {
    /**
     * @var \ArrayAccess|array
     */
    protected $config;

    public function __construct($config = null) {
        $this->config = $config;
    }

    public static function main(array $config = null): int {
        $app = new static($config);
        $res = $app->run();
        return $res ? Environment::SUCCESS_CODE : Environment::FAILURE_CODE;
    }

    public function run(): bool {
        try {
            $serviceManager = $this->newServiceManager();
            $this->configure($serviceManager);
            $request = $serviceManager->get('request');
            $serviceManager->get('router')->route($request);
            $serviceManager->get('dispatcher')->dispatch($request);
            $request->response()->send();
            return true;
        } catch (\Throwable $e) {
            $this->handleError($e, $serviceManager ?? null);
            return false;
        }
    }

    public function config() {
        return $this->config;
    }

    protected function newServiceManager(): IServiceManager {
        $config = $this->config;
        $baseDirPath = isset($config['baseDirPath'])
            ? realpath(str_replace('\\', '/', $config['baseDirPath']))
            : PathManager::detectBaseDirPath(__DIR__);
        $pathManager = new PathManager($baseDirPath);
        $site = $config['site'] ?? (new SiteFactory())($pathManager, $config);
        $services = [
            'app'  => $this,
            'site' => $site,
            'pathManager' => $pathManager,
        ];
        $siteConfig = $site->config();
        $serviceManager = $siteConfig['serviceManager']
            ? new $siteConfig['serviceManager']($services)
            : new ServiceManager($services);
        $serviceManager->setConfig($siteConfig['services']);
        return $serviceManager;
    }

    protected function handleError(\Throwable $e, ?IServiceManager $serviceManager): void {
        if ($serviceManager) {
            try {
                $serviceManager->get('errorHandler')->handleException($e);
            } catch (\Throwable $e) {
                $this->logErrorFallback($e);
            }
        } else {
            $this->logErrorFallback($e);
        }
        $this->showError($e);
    }

    protected function logErrorFallback(\Throwable $e): void {
        if (ErrorHandler::isErrorLogEnabled()) {
            // @TODO: check how error logging works on PHP core level, remove unnecessary calls and checks.
            error_log(addslashes((string)$e));
        }
    }

    /**
     * Configures an application.
     */
    protected function configure(IServiceManager $serviceManager): void {
        $serviceManager->get('environment')->init();
        $serviceManager->get('errorHandler')->register();

        /** @var Site $site */
        $site = $serviceManager->get('site');

        $config = $site->config();
        $this->applyIniSettings($config['iniSettings']);
        if (isset($config['umask'])) {
            umask($config['umask']);
        }
        if (!$config['useOwnPublicDir']) {
            $site->pathManager()->setPublicDirPath($serviceManager->get('pathManager')->publicDirPath());
        }

        $serviceManager->get('moduleProvider')
            ->append($site);
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
        while (\ob_get_level() > 0) {
            \ob_end_clean();
        }
        die(escapeHtml($message) . '.');
    }
}
