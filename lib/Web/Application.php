<?php
//declare(strict_types=1);
namespace Morpho\Web;

use function Morpho\Base\escapeHtml;
use Morpho\Core\Application as BaseApplication;
use const Morpho\Core\CONFIG_FILE_NAME;
use const Morpho\Core\MODULE_DIR_PATH;
use Morpho\Di\IServiceManager;
use Morpho\Error\ErrorHandler;

class Application extends BaseApplication {
    protected function init(IServiceManager $serviceManager) {
        parent::init($serviceManager);
        $iniSettings = $serviceManager->get('site')->config()['iniSettings'];
        $this->applyIniSettings($iniSettings);
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
        if (!empty($_SERVER['HTTPS']) && !isset($iniSettings['session']['cookie_secure'])) {
            ini_set('cookie_secure', true);
        }
    }

    protected function createServiceManager(): IServiceManager {
        $moduleDirPath = MODULE_DIR_PATH;

        $config = require $moduleDirPath . '/' . CONFIG_FILE_NAME;

        $sites = $config['sites'];
        $current = null;
        if (!$config['useMultiSiting']) {
            // No multi-siting -> use first found site.
            $current = array_shift($sites);
        } else {
            $hostName = $this->detectHostName();
            foreach ($sites as $hostName1 => $moduleName) {
                if ($hostName === $hostName1) {
                    $current = $moduleName;
                    break;
                }
            }
        }
        if (null === $current) {
            throw new BadRequestException("Unable to detect the current site");
        }

        $siteDirPath = $moduleDirPath . '/' . explode('/', $current)[1];
        $site = new Site($current, $siteDirPath);

        $siteConfig = $site->config();
        $services = [
            'app'  => $this,
            'site' => $site,
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

    protected function detectHostName(): string {
        // Use the `Host` header field-value, see https://tools.ietf.org/html/rfc3986#section-3.2.2
        $host = $_SERVER['HTTP_HOST'] ?? null;

        if (empty($host)) {
            throw new BadRequestException("Empty value of the 'Host' field");
        }

        // @TODO: Unicode and internationalized domains, see https://tools.ietf.org/html/rfc5892
        if (false !== ($startOff = strpos($host, '['))) {
            // IPv6 or later.
            if ($startOff !== 0) {
                throw new BadRequestException("Invalid value of the 'Host' field");
            }
            $endOff = strrpos($host, ']', 2);
            if (false === $endOff) {
                throw new BadRequestException("Invalid value of the 'Host' field");
            }
            $hostWithoutPort = strtolower(substr($host, 0, $endOff + 1));
        } else {
            // IPv4 or domain name
            $hostWithoutPort = explode(':', strtolower((string)$host), 2)[0];
            if (substr($hostWithoutPort, 0, 4) === 'www.' && strlen($hostWithoutPort) > 4) {
                $hostWithoutPort = substr($hostWithoutPort, 4);
            }
        }
        return $hostWithoutPort;
    }
}
