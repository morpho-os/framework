<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace Morpho\Web;

use Morpho\Base\IFn;
use const Morpho\Core\CONFIG_DIR_NAME;
use const Morpho\Core\VENDOR_DIR_NAME;
use Zend\Stdlib\ArrayUtils;

class SiteFactory implements IFn {
    public function __invoke($config): array {
        if ($config['multiSiting']) {
            $hostName = $this->detectHostName();
            foreach ($config['sites'] as $hostName1 => $siteConfig) {
                if ($hostName === $hostName1) {
                    return $this->newSiteAndConfig($hostName, $siteConfig, $config['publicDirPath']);
                }
            }
            throw new BadRequestException("Unable to detect the current site");
        } else {
            // No multi-siting -> use first found site.
            $sitesConfig = $config['sites'];
            reset($sitesConfig);
            $siteConfig = $sitesConfig[key($sitesConfig)];
            return $this->newSiteAndConfig(null, $siteConfig, $config['publicDirPath']);
        }
    }

    /**
     * @throws BadRequestException
     */
    public static function detectHostName(): string {
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

    protected function newSiteAndConfig(?string $hostName, $siteConfig, string $publicDirPath): array {
        require_once $siteConfig['dirPath'] . '/' . VENDOR_DIR_NAME . '/autoload.php';
        $siteModuleName = $siteConfig['module'];
        $normalizedConfig = $this->normalizeConfig($siteConfig, $publicDirPath);
        return [new Site($siteModuleName, $hostName), $normalizedConfig];
    }
    
    protected function normalizeConfig($config, string $publicDirPath) {
        $siteDirPath = $config['dirPath'];
        $configFilePath = $config['dirPath'] . '/' . CONFIG_DIR_NAME . '/config.php';
        $siteModuleName = $config['module'];
        unset($config['module'], $config['dirPath']);
        $normalizedConfig = ArrayUtils::merge($config, $this->loadSiteConfig($configFilePath));
        $normalizedConfig['paths'] += ['dirPath' => $siteDirPath, 'publicDirPath' => $publicDirPath];
        if (!isset($normalizedConfig['modules'])) {
            $normalizedConfig['modules'] = [];
        }
        $newModules = [
            $siteModuleName => [],
        ]; // We use a new array to preserve ordering
        foreach ($normalizedConfig['modules'] as $name => $conf) {
            if (is_numeric($name)) {
                $newModules[$conf] = [];
            } else {
                $newModules[$name] = $conf;
            }
        }
        $normalizedConfig['modules'] = $newModules;

        return $normalizedConfig;
    }

    protected function loadSiteConfig(string $configFilePath) {
        return require $configFilePath;
    }
}