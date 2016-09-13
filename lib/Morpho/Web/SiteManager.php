<?php
namespace Morpho\Web;

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
            $this->useMultiSiting = (bool)$this->getConfig()['useMultiSiting'];
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
        return $this->getCurrentSite()->isFallbackConfigUsed();
    }

    public function getCurrentSite(): Site {
        if (null === $this->currentSiteName) {
            $siteName = $this->discoverCurrentSiteName();
            if (!isset($this->sites[$siteName])) {
                $this->sites[$siteName] = $this->createSite($siteName);
            }
            $this->currentSiteName = $siteName;
        }
        return $this->sites[$this->currentSiteName];
    }

    public function setSite(Site $site, bool $setAsCurrent = true) {
        $siteName = $site->getName();
        $this->mustBeAllowedSiteName($siteName);
        $this->sites[$siteName] = $site;
        if ($setAsCurrent) {
            $this->currentSiteName = $siteName;
        }
    }

    public function getSite(string $siteName): Site {
        if (!isset($this->sites[$siteName])) {
            $this->mustBeAllowedSiteName($siteName);
            $this->sites[$siteName] = $this->createSite($siteName);
        }
        return $this->sites[$siteName];
    }

    public function setCurrentSiteConfig(array $config) {
        $this->getCurrentSite()->setConfig($config);
    }

    public function getCurrentSiteConfig(): array {
        return $this->getCurrentSite()->getConfig();
    }

    public function setAllSitesDirPath(string $dirPath) {
        $this->allSitesDirPath = Path::normalize($dirPath);
    }

    public function getAllSitesDirPath(): string {
        if (null === $this->allSitesDirPath) {
            $this->allSitesDirPath = SITE_DIR_PATH;
        }
        return $this->allSitesDirPath;
    }

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    protected function mustBeAllowedSiteName(string $siteName) {
        if (false === $this->resolveSiteName($siteName)) {
            throw new \RuntimeException("Not allowed site name was provided");
        }
    }

    protected function discoverCurrentSiteName(): string {
        $sites = $this->getConfig()['sites'];
        if (!$this->useMultiSiting()) {
            return array_shift($sites);
        }
        $siteName = $_SERVER['HTTP_HOST'] ?? null;
        if (empty($siteName)) {
            $this->invalidSiteError("Empty value of the 'Host' field");
        }
        $siteName = explode(':', strtolower((string)$siteName), 2)[0];
        if (substr($siteName, 0, 4) === 'www.' && strlen($siteName) > 4) {
            $siteName = substr($siteName, 4);
        }
        $siteName = $this->resolveSiteName($siteName);
        if (false === $siteName) {
            $this->invalidSiteError("Invalid value of the 'Host' field");
        }
        return $siteName;
    }

    /**
     * @param string|bool Returns site name on success and false otherwise.
     */
    protected function resolveSiteName(string $siteName) {
        if (null === $this->allowedSiteNames) {
            $sites = $this->getConfig()['sites'];
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

    protected function invalidSiteError(string $message) {
        throw new BadRequestException($message);
    }

    protected function getConfig(): array {
        if (null === $this->config) {
            $this->config = require $this->getAllSitesDirPath() . '/' . self::CONFIG_FILE_NAME;
        }
        return $this->config;
    }

    protected function createSite(string $siteName): Site {
        $siteDirPath = $this->getAllSitesDirPath() . '/' . $siteName;
        Directory::mustExist($siteDirPath);
        return new Site([
            'name'    => $siteName,
            'dirPath' => $siteDirPath,
        ]);
    }
}
