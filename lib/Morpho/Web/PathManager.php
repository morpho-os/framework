<?php
namespace Morpho\Web;

use Morpho\Base\NotImplementedException;
use Morpho\Fs\Path;
use Morpho\Di\IServiceManagerAware;
use Morpho\Di\IServiceManager;

class PathManager implements IServiceManagerAware {
    protected $serviceManager;

    protected $siteManager;

    private $currentSiteDirPath;

    private $cacheDirPath;

    private $webDirPath;

    private $logDirPath;

    public function __construct(SiteManager $siteManager) {
        $this->siteManager = $siteManager;
    }

    public function setAllSiteDirPath($dirPath) {
        $this->siteManager->setAllSiteDirPath($dirPath);
    }

    public function getAllSiteDirPath() {
        return $this->siteManager->getAllSiteDirPath();
    }

    public function getCurrentSiteDirPath() {
        if (null === $this->currentSiteDirPath) {
            $this->currentSiteDirPath = $this->getAllSiteDirPath() . '/' . $this->siteManager->getCurrentSiteName();
        }
        return $this->currentSiteDirPath;
    }

    public function setUploadDirPath($dirPath) {
        throw new NotImplementedException();
    }

    public function getUploadDirPath() {
        return $this->getCurrentSiteDirPath() . '/' . UPLOAD_DIR_NAME;
    }

    public function setCacheDirPath($dirPath) {
        $this->cacheDirPath = Path::normalize($dirPath);
    }

    public function getCacheDirPath() {
        if (null === $this->cacheDirPath) {
            $this->cacheDirPath = $this->getCurrentSiteDirPath() . '/' . CACHE_DIR_NAME;
        }
        return $this->cacheDirPath;
    }

    public function setLogDirPath($dirPath) {
        $this->logDirPath = Path::normalize($dirPath);
    }

    public function getLogDirPath() {
        if (null === $this->logDirPath) {
            $this->logDirPath = $this->getCurrentSiteDirPath() . '/' . LOG_DIR_NAME;
        }
        return $this->logDirPath;
    }

    public function setWebDirPath($dirPath) {
        $this->webDirPath = Path::normalize($dirPath);
    }

    public function getWebDirPath() {
        if (null === $this->webDirPath) {
            $this->webDirPath = Path::normalize(WEB_DIR_PATH);
        }
        return $this->webDirPath;
    }

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }
}
