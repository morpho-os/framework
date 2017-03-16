<?php
namespace Morpho\Web;

use function Morpho\Base\requireFile;
use Morpho\Di\{
    IServiceManager, IServiceManagerAware
};
use Morpho\Fs\Directory;
use Morpho\Fs\Path;
use Morpho\Base\Object;

class SiteManager extends Object implements IServiceManagerAware {
    protected $currentSiteName;

    protected $allSitesDirPath;

    protected $sites = [];

    protected $useMultiSiting;

    protected $serviceManager;

    private $isFallbackMode;

    private $allowedSiteNames;

    private $config;

    const CONFIG_FILE_NAME = CONFIG_FILE_NAME;

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
        if (null === $this->currentSiteName) {
            $siteName = $this->detectSiteName();
            if (!isset($this->sites[$siteName])) {
                $this->sites[$siteName] = $this->createSite($siteName);
            }
            $this->currentSiteName = $siteName;
        }
        return $this->sites[$this->currentSiteName];
    }

    public function setSite(Site $site, bool $setAsCurrent = true): void {
        $siteName = $site->name();
        $this->checkSiteName($siteName);
        $this->sites[$siteName] = $site;
        if ($setAsCurrent) {
            $this->currentSiteName = $siteName;
        }
    }

    public function site(string $siteName): Site {
        if (!isset($this->sites[$siteName])) {
            $this->checkSiteName($siteName);
            $this->sites[$siteName] = $this->createSite($siteName);
        }
        return $this->sites[$siteName];
    }

    public function setCurrentSiteConfig(array $config): void {
        $this->currentSite()->setConfig($config);
    }

    public function currentSiteConfig(): array {
        return $this->currentSite()->config();
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

    protected function checkSiteName(string $siteName): void {
        if (false === $this->resolveSiteName($siteName)) {
            throw new \RuntimeException("Not allowed site name was provided");
        }
    }

    protected function detectSiteName(): string {
        if (!$this->useMultiSiting()) {
            // No multi-siting -> use first found site.
            $sites = $this->config()['sites'];
            return array_shift($sites);
        }

        // Use the `Host` header field-value, see https://tools.ietf.org/html/rfc3986#section-3.2.2
        $host = $_SERVER['HTTP_HOST'] ?? null;

        if (empty($host)) {
            $this->invalidHostError("Empty value of the 'Host' field");
        }

        // @TODO: Unicode and internationalized domains, see https://tools.ietf.org/html/rfc5892
        if (false !== ($startOff = strpos($host, '['))) {
            if ($startOff !== 0) {
                $this->invalidHostError("Invalid value of the 'Host' field");
            }
            // IPv6 or later.
            $endOff = strrpos($host, ']', 2);
            if (false === $endOff) {
                $this->invalidHostError("Invalid value of the 'Host' field");
            }
            $hostWithoutPort = strtolower(substr($host, 0, $endOff + 1));
        } else {
            // IPv4 or domain name
            $hostWithoutPort = explode(':', strtolower((string)$host), 2)[0];
            if (substr($hostWithoutPort, 0, 4) === 'www.' && strlen($hostWithoutPort) > 4) {
                $hostWithoutPort = substr($hostWithoutPort, 4);
            }
        }

        $siteName = $this->resolveSiteName($hostWithoutPort);
        if (false === $siteName) {
            $this->invalidHostError("Invalid value of the 'Host' field");
        }
        return $siteName;
    }

    /**
     * @param string|bool Returns site name on success and false otherwise.
     */
    protected function resolveSiteName(string $siteName) {
        if (null === $this->allowedSiteNames) {
            $sites = $this->config()['sites'];
            foreach ($sites as $alias => $resolvedSiteName) {
                if (is_numeric($alias)) {
                    if ($resolvedSiteName === $siteName) {
                        return $siteName;
                    }
                } elseif ($alias === $siteName) {
                    return $resolvedSiteName;
                }
            }
        }
        return false;
    }

    protected function config(): array {
        if (null === $this->config) {
            $this->config = requireFile($this->allSitesDirPath() . '/' . self::CONFIG_FILE_NAME);
        }
        return $this->config;
    }

    protected function createSite(string $siteName): Site {
        $siteDirPath = $this->allSitesDirPath() . '/' . $siteName;
        Directory::mustExist($siteDirPath);
        return new Site([
            'name' => $siteName,
            'dirPath' => $siteDirPath,
        ]);
    }

    private function invalidHostError(string $message): void {
        throw new BadRequestException($message);
    }
}
