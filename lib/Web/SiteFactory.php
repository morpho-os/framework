<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Base\IFn;
use const Morpho\Core\CONFIG_DIR_NAME;
use const Morpho\Core\CONFIG_FILE_NAME;
use const Morpho\Core\VENDOR_DIR_NAME;
use Zend\Stdlib\ArrayUtils;

class SiteFactory implements IFn {
    public function __invoke($config): Site {
        $hostName = $this->detectHostName();
        $siteConfig = $config['hostMapper']($hostName);
        if (!$siteConfig) {
            throw new BadRequestException("Unable to detect the current site");
        }
        $siteModuleName = $siteConfig['module'];
        unset($siteConfig['module']);
        return new Site($siteModuleName, $hostName, $this->loadMergedConfig($siteModuleName, $siteConfig));
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

    protected function loadMergedConfig(string $siteModuleName, array $siteConfig): \ArrayObject {
        require $siteConfig['paths']['dirPath'] . '/' . VENDOR_DIR_NAME . '/autoload.php';

        $configFilePath = $siteConfig['paths']['configFilePath'];
        $loadedConfig = ArrayUtils::merge($siteConfig, $this->requireFile($configFilePath));

        if (!isset($loadedConfig['modules'])) {
            $loadedConfig['modules'] = [];
        }
        $newModules = [$siteModuleName => []]; // Store the site config as first item
        foreach ($loadedConfig['modules'] as $name => $moduleConfig) {
            if (is_numeric($name)) {
                $newModules[$moduleConfig] = [];
            } else {
                $newModules[$name] = $moduleConfig;
            }
        }
        $loadedConfig['modules'] = $newModules;

        return new \ArrayObject($loadedConfig);
    }

    protected function requireFile(string $filePath) {
        return require $filePath;
    }
}