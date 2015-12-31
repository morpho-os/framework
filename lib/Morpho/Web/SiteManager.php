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

    private $siteNames;

    private $config;

    const CONFIG_FILE_NAME = CONFIG_FILE_NAME;

    /**
     * @param null|bool $flag
     */
    public function useMultiSiting($flag = null): bool {
        if (null !== $flag) {
            $this->useMultiSiting = $flag;
        }
        if (null === $this->useMultiSiting) {
            $this->useMultiSiting = (bool)$this->getConfig()['useMultiSiting'];
        }
        return $this->useMultiSiting;
    }

    public function isFallbackMode($flag = null) {
        if (null !== $flag) {
            $this->isFallbackMode = $flag;
            return $flag;
        }
        if (null !== $this->isFallbackMode) {
            return $this->isFallbackMode;
        }
        return $this->getCurrentSite()->isFallbackConfigUsed();
    }

    public function getCurrentSite() {
        if (null === $this->currentSiteName) {
            $this->currentSiteName = $this->discoverCurrentSiteName();
        }

        return $this->getSite($this->currentSiteName);
    }

    public function getCurrentSiteName() {
        return $this->getCurrentSite()->getName();
    }

    public function setSite(Site $site, bool $makeCurrent = true) {
        $siteName = $site->getName();
        $this->checkSiteName($siteName);
        $this->sites[$siteName] = $site;
        if ($makeCurrent) {
            $this->currentSiteName = $siteName;
        }
    }

    public function getSite(string $siteName) {
        if (!isset($this->sites[$siteName])) {
            $this->sites[$siteName] = $this->createSite($siteName);
        }

        return $this->sites[$siteName];
    }

    public function setSiteConfig(array $config) {
        $this->getCurrentSite()->setConfig($config);
    }

    public function getSiteConfig() {
        return $this->getCurrentSite()->getConfig();
    }

    public function setAllSitesDirPath($dirPath) {
        $this->allSitesDirPath = Path::normalize($dirPath);

        return $this;
    }

    public function getAllSitesDirPath() {
        if (null === $this->allSitesDirPath) {
            $this->allSitesDirPath = SITE_DIR_PATH;
        }

        return $this->allSitesDirPath;
    }

    public function isValidSiteName($siteName) {
        return in_array($siteName, $this->listSiteNames(), true);
    }

    protected function createSite($siteName) {
        $this->checkSiteName($siteName);

        $realSiteName = $this->getAlias($siteName);

        return new Site([
            'name'    => $realSiteName,
            'dirPath' => $this->getAllSitesDirPath() . '/' . $realSiteName,
        ]);
    }

    protected function getAlias($siteName) {
        /*
         * @TODO:
         * $sitos = array(); $aliasFilePath = $this->allSitesDirPath .
         * '/site-alias.php'; // site-alias.php file can define aliases for
         * sites. if (file_exists($aliasFilePath)) { require $aliasFilePath; }
         */

        return $siteName;
    }

    protected function checkSiteName($siteName) {
        if (!$this->isValidSiteName($siteName)) {
            $this->exit("Invalid site name '$siteName' was provided.");
        }
    }

    protected function exit(string $message) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
        exit("<h2>Bad request (400).</h2>");
    }

    protected function discoverCurrentSiteName() {
        if (!$this->useMultiSiting()) {
            return $this->getConfig()['defaultSite'];
        }
        $siteName = null;
        if (!empty($_SERVER['HTTP_HOST'])) {
            $siteName = $_SERVER['HTTP_HOST'];
        }
        $this->checkSiteName($siteName);

        return $siteName;
    }

    protected function getConfig() {
        if (null === $this->config) {
            $this->config = require $this->getAllSitesDirPath() . '/' . self::CONFIG_FILE_NAME;
        }
        return $this->config;
    }

    protected function listSiteNames(): array {
        if (!$this->siteNames) {
            // @TODO: Add writing of the detected sites to the config.
            $this->siteNames = $this->getConfig()['sites'] ?? array_map(
                    'basename',
                    Directory::listDirs($this->getAllSitesDirPath(), null, ['recursive' => false])
                );
        }
        return $this->siteNames;
    }

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }
}
