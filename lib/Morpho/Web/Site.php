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

    private $publicDirPath;

    const CONFIG_FILE_NAME          = CONFIG_FILE_NAME;
    const FALLBACK_CONFIG_FILE_NAME = 'fallback.php';

    public function __construct(array $options = []) {
        ArrayTool::ensureHasOnlyKeys($options, ['dirPath', 'name']);
        foreach ($options as $name => $value) {
            $this->$name = $value;
        }
    }

    public function setDirPath(string $dirPath) {
        $this->dirPath = $dirPath;
    }
    
    public function getDirPath(): string {
        return $this->dirPath;
    }

    public function setName(string $name) {
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setCacheDirPath(string $dirPath) {
        $this->cacheDirPath = Path::normalize($dirPath);
    }

    public function getCacheDirPath(): string {
        if (null === $this->cacheDirPath) {
            $this->cacheDirPath = $this->getDirPath() . '/' . CACHE_DIR_NAME;
        }
        return $this->cacheDirPath;
    }

    public function setConfigDirPath(string $dirPath) {
        $this->configDirPath = Path::normalize($dirPath);
    }

    public function getConfigDirPath(): string {
        if (null === $this->configDirPath) {
            $this->configDirPath = $this->getDirPath() . '/' . CONFIG_DIR_NAME;
        }
        return $this->configDirPath;
    }

    public function setLogDirPath(string $dirPath) {
        $this->logDirPath = Path::normalize($dirPath);
    }

    public function getLogDirPath(): string {
        if (null === $this->logDirPath) {
            $this->logDirPath = $this->getDirPath() . '/' . LOG_DIR_NAME;
        }
        return $this->logDirPath;
    }

    public function setUploadDirPath(string $dirPath) {
        $this->uploadDirPath = Path::normalize($dirPath);
    }

    public function getUploadDirPath(): string {
        if (null === $this->uploadDirPath) {
            $this->uploadDirPath = $this->getDirPath() . '/' . UPLOAD_DIR_NAME;
        }
        return $this->uploadDirPath;
    }

    public function setPublicDirPath(string $dirPath) {
        $this->publicDirPath = Path::normalize($dirPath);
    }

    public function getPublicDirPath(): string {
        if (null === $this->publicDirPath) {
            $this->publicDirPath = PUBLIC_DIR_PATH;
        }
        return $this->publicDirPath;
    }

    public function isFallbackConfigUsed(): bool {
        if (null === $this->isFallbackConfigUsed) {
            throw new \LogicException('eThe loadConfig() must be called first');
        }
        return $this->isFallbackConfigUsed;
    }

    public function setConfig(array $config) {
        $this->config = $config;
    }

    public function getConfig(): array {
        if (null === $this->config) {
            $this->config = $this->loadConfig();
        }

        return $this->config;
    }

    public function getConfigFilePath(): string {
        return $this->getConfigDirPath() . '/' . self::CONFIG_FILE_NAME;
    }

    public function getFallbackConfigFilePath(): string {
        return $this->getConfigDirPath() . '/' . self::FALLBACK_CONFIG_FILE_NAME;
    }

    protected function loadConfig(): array {
        $filePath = $this->getConfigFilePath();
        if (!file_exists($filePath) || !is_readable($filePath)) {
            $filePath = $this->getFallbackConfigFilePath();
            $this->isFallbackConfigUsed = true;
        } else {
            $this->isFallbackConfigUsed = false;
        }

        return require $filePath;
    }
}
