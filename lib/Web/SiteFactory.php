<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace Morpho\Web;

class SiteFactory {
    public function __invoke(Fs $fs): Site {
        list($moduleName, $hostName) = $this->detectSite($fs->loadConfigFile());
        $dirName = explode('/', $moduleName)[1];
        $siteDirPath = $fs->baseModuleDirPath() . '/' . $dirName;
        $fs = new SiteFs($siteDirPath);
        return new Site($moduleName, $fs, $hostName);
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

    protected function detectSite(array $config): array {
        $sites = $config['sites'];
        $hostName = $siteName = null;
        if (!$config['useMultiSiting']) {
            // No multi-siting -> use first found site.
            $siteName = array_shift($sites);
        } else {
            $hostName = $this->detectHostName();
            foreach ($sites as $hostName1 => $moduleName) {
                if ($hostName === $hostName1) {
                    $siteName = $moduleName;
                    break;
                }
            }
        }
        if (null === $siteName) {
            throw new BadRequestException("Unable to detect the current site");
        }
        return [$siteName, $hostName];
    }
}