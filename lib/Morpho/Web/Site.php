<?php
namespace Morpho\Web;

use Morpho\Base\ArrayTool;
use Morpho\Fs\Path;

class Site {
    private $name;

    private $dirPath;

    private $config;

    private $isFallbackConfigUsed;

    private $cacheDirPath;

    private $configDirPath;

    private $logDirPath;

    private $uploadDirPath;

    private $webDirPath;

    const CONFIG_FILE_NAME = 'config.php';

    public function __construct(array $options = array()) {
        ArrayTool::ensureHasOnlyKeys($options, ['dirPath', 'name']);
        foreach ($options as $name => $value) {
            $this->$name = $value;
        }
    }
    
    public function setDirPath(string $dirPath) {
        $this->dirPath = $dirPath;
    }
    
    public function getDirPath() {
        return $this->dirPath;
    }

    public function setName(string $name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

    public function setCacheDirPath(string $dirPath) {
        $this->cacheDirPath = Path::normalize($dirPath);
    }

    public function getCacheDirPath() {
        if (null === $this->cacheDirPath) {
            $this->cacheDirPath = $this->getDirPath() . '/' . CACHE_DIR_NAME;
        }
        return $this->cacheDirPath;
    }

    public function setConfigDirPath(string $dirPath) {
        $this->configDirPath = Path::normalize($dirPath);
    }

    public function getConfigDirPath() {
        if (null === $this->configDirPath) {
            $this->configDirPath = $this->getDirPath() . '/' . CONFIG_DIR_NAME;
        }
        return $this->configDirPath;
    }

    public function setLogDirPath(string $dirPath) {
        $this->logDirPath = Path::normalize($dirPath);
    }

    public function getLogDirPath() {
        if (null === $this->logDirPath) {
            $this->logDirPath = $this->getDirPath() . '/' . LOG_DIR_NAME;
        }
        return $this->logDirPath;
    }

    public function setUploadDirPath(string $dirPath) {
        $this->uploadDirPath = Path::normalize($dirPath);
    }

    public function getUploadDirPath() {
        if (null === $this->uploadDirPath) {
            $this->uploadDirPath = $this->getDirPath() . '/' . UPLOAD_DIR_NAME;
        }
        return $this->uploadDirPath;
    }

    public function setWebDirPath(string $dirPath) {
        $this->webDirPath = Path::normalize($dirPath);
    }

    public function getWebDirPath() {
        if (null === $this->webDirPath) {
            $this->webDirPath = WEB_DIR_PATH;
        }
        return $this->webDirPath;
    }

    public function isFallbackConfigUsed() {
        if (null === $this->isFallbackConfigUsed) {
            throw new \LogicException('eThe loadConfig() must be called first');
        }
        return $this->isFallbackConfigUsed;
    }

    public function setConfig(array $config) {
        $this->config = $config;
    }

    public function getConfig() {
        if (null === $this->config) {
            $this->config = $this->loadConfig();
        }

        return $this->config;
    }

    public function getConfigFilePath() {
        return $this->getConfigDirPath() . '/' . self::CONFIG_FILE_NAME;
    }

    protected function loadConfig() {
        $filePath = $this->getConfigFilePath();
        if (!file_exists($filePath) || !is_readable($filePath)) {
            /* . (PHP_SAPI == 'cli' ? 'cli-' : '')*/
            $filePath = dirname($filePath) . '/fallback.php';
            $this->isFallbackConfigUsed = true;
        } else {
            $this->isFallbackConfigUsed = false;
        }

        return require $filePath;
    }
}
