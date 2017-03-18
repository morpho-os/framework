<?php
namespace Morpho\Web;

use function Morpho\Base\requireFile;
use Morpho\Di\{
    IServiceManager, IServiceManagerAware
};
use Morpho\Fs\Path;
use Morpho\Base\Object;

class SiteManager extends Object implements IServiceManagerAware {
    public const CONFIG_FILE_NAME = CONFIG_FILE_NAME;

    protected $allSitesDirPath;

    protected $sites = [];

    protected $useMultiSiting;

    protected $serviceManager;

    private $isFallbackMode;

    private $config;

    private $current;

    public function useMultiSiting(bool $flag = null): bool {
        if (null !== $flag) {
            $this->useMultiSiting = $flag;
        }
        if (null === $this->useMultiSiting) {
            $this->useMultiSiting = (bool)$this->config()['useMultiSiting'];
        }
        return $this->useMultiSiting;
    }

    public function isFallbackMode(bool $flag = null): bool {
        if (null !== $flag) {
            $this->isFallbackMode = $flag;
            return $flag;
        }
        if (null !== $this->isFallbackMode) {
            return $this->isFallbackMode;
        }
        return $this->currentSite()->fallbackConfigUsed();
    }

    public function currentSite(): Site {
        if (null === $this->current) {
            $hostName = $this->detectHostName();
            $alias = $this->aliasByHostName($hostName);
            if (!$alias) {
                throw new BadRequestException("Invalid Host");
            }
            if (!isset($this->sites[$alias])) {
                $site = new Site(
                    new Host($alias, $hostName),
                    $this->allSitesDirPath() . '/' . $alias
                );
                $this->sites[$alias] = $site;
            }
            $this->current = $alias;
        }
        return $this->sites[$this->current];
    }

    public function setSite(Site $site, bool $setAsCurrent = true): void {
        $host = $site->host();
        if (!$this->hostNameByAlias($host->alias)) {
            throw new \RuntimeException("Invalid host alias");
        }
        $this->sites[$host->alias] = $site;
        if ($setAsCurrent) {
            $this->current = $host->alias;
        }
    }

    public function site(string $alias): Site {
        if (!isset($this->sites[$alias])) {
            $hostName = $this->hostNameByAlias($alias);
            if (!$hostName) {
                throw new \RuntimeException("Invalid host alias");
            }
            $this->sites[$alias] = new Site(
                new Host($alias, $hostName),
                $this->allSitesDirPath() . '/' . $alias
            );
        }
        return $this->sites[$alias];
    }

    public function setAllSitesDirPath(string $dirPath): void {
        $this->allSitesDirPath = Path::normalize($dirPath);
    }

    public function allSitesDirPath(): string {
        if (null === $this->allSitesDirPath) {
            $this->allSitesDirPath = SITE_DIR_PATH;
        }
        return $this->allSitesDirPath;
    }

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }

    protected function detectHostName(): string {
        if (!$this->useMultiSiting()) {
            // No multi-siting -> use first found site.
            $sites = $this->config()['sites'];
            return array_shift($sites);
        }

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

    protected function config(): array {
        if (null === $this->config) {
            $this->config = requireFile($this->allSitesDirPath() . '/' . self::CONFIG_FILE_NAME);
        }
        return $this->config;
    }

    /**
     * @return string|false
     */
    protected function hostNameByAlias(string $alias) {
        if (empty($alias)) {
            return false;
        }
        $sites = $this->config()['sites'];
        foreach ($sites as $alias1 => $name) {
            if (is_numeric($alias1)) {
                if ($name === $alias) {
                    return $name;
                }
            } elseif ($alias1 === $alias) {
                return $name;
            }
        }
        return false;
    }

    /**
     * @return string|false
     */
    protected function aliasByHostName(string $hostWithoutPort) {
        $knownHosts = $this->config()['sites'];
        foreach ($knownHosts as $alias => $name) {
            if (is_numeric($alias)) {
                if ($name === $hostWithoutPort) {
                    return $name;
                }
            } elseif ($name === $hostWithoutPort) {
                return $alias;
            }
        }
        return false;
    }
}