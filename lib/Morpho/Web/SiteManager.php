<?php
namespace Morpho\Web;

use Morpho\Fs\Path;
use Morpho\Base\Object;
use Morpho\Validator\HostNameValidator;
use Zend\Validator\ValidatorInterface as IValidator;

class SiteManager extends Object {
    protected $currentSiteName;

    protected $allSiteDirPath;

    protected $sites = [];

    protected $siteNameValidator;

    protected $exitOnInvalidSite = true;

    protected $useMultiSiting = false;

    private $isFallbackMode;

    const DEFAULT_SITE = 'default';

    /**
     * @param null|bool $flag
     * @return bool Returns current value of multi-siting.
     */
    public function useMultiSiting($flag = null) {
        if (null !== $flag) {
            $this->useMultiSiting = $flag;
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

    public function setSite(Site $site, $asCurrent = true) {
        $siteName = $site->getName();
        $this->checkSiteName($siteName);
        $this->sites[$siteName] = $site;
        if ($asCurrent) {
            $this->currentSiteName = $siteName;
        }
    }

    public function getSite($siteName) {
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

    public function setAllSiteDirPath($dirPath) {
        $this->allSiteDirPath = Path::normalize($dirPath);

        return $this;
    }

    public function getAllSiteDirPath() {
        if (null === $this->allSiteDirPath) {
            $this->allSiteDirPath = SITE_DIR_PATH;
        }

        return $this->allSiteDirPath;
    }

    public function setSiteNameValidator(IValidator $validator) {
        $this->siteNameValidator = $validator;

        return $this;
    }

    public function isValidSiteName($siteName) {
        if (null !== $this->siteNameValidator) {
            return $this->siteNameValidator->isValid($siteName);
        }
        // @TODO: Change logic: list all dirs, cache list, search in list.
        return (new HostNameValidator())->isValid($siteName)
            && is_dir($this->getAllSiteDirPath() . '/' . $siteName);
    }

    protected function createSite($siteName) {
        $this->checkSiteName($siteName);

        $realSiteName = $this->getAlias($siteName);

        return new Site([
            'name' => $realSiteName,
            'dirPath' => $this->getAllSiteDirPath() . '/' . $realSiteName,
        ]);
    }

    protected function getAlias($siteName) {
        /*
         * @TODO:
         * $sitos = array(); $aliasFilePath = $this->allSiteDirPath .
         * '/site-alias.php'; // site-alias.php file can define aliases for
         * sites. if (file_exists($aliasFilePath)) { require $aliasFilePath; }
         */

        return $siteName;
    }

    protected function checkSiteName($siteName) {
        if (!$this->isValidSiteName($siteName)) {
            $message = "Invalid site name '$siteName' was provided.";
            if (!$this->exitOnInvalidSite) {
                throw new \RuntimeException($message);
            }
            $this->doExit($message);
        }
    }

    protected function doExit($message) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
        exit("<h2>Bad request (400).</h2>");
    }

    protected function discoverCurrentSiteName() {
        if (!$this->useMultiSiting) {
            return self::DEFAULT_SITE;
        }
        $siteName = null;
        if (!empty($_SERVER['HTTP_HOST'])) {
            $siteName = $_SERVER['HTTP_HOST'];
        }
        $this->checkSiteName($siteName);

        return $siteName;
    }
}
