<?php
namespace Morpho\Web;

use Morpho\Base\ArrayTool;
use Morpho\Fs\Path;

class Site {
    private $useDebug;

    private $name;

    private $dirPath;

    private $config;

    private $isFallbackConfigUsed;

    private $cacheDirPath;

    private $configDirPath;

    private $logDirPath;

    private $uploadDirPath;

    private $publicDirPath;

    private $mode;

    private $configFileName = self::CONFIG_FILE_NAME;

    const CONFIG_FILE_NAME = CONFIG_FILE_NAME;
    const FALLBACK_CONFIG_FILE_NAME = 'fallback.php';

    const DEV_MODE = 'dev';
    const STAGING_MODE = 'staging';
    const PRODUCTION_MODE = 'production';
    const TESTING_MODE = 'testing';

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

    public function isDebug(bool $flag = null): bool {
        if (null !== $flag) {
            $this->useDebug = $flag;
        } elseif (null === $this->useDebug) {
            $this->useDebug = $this->getConfig()['isDebug'];
        }
        return $this->useDebug;
    }

    public function setMode(string $mode) {
        $this->mode = $mode;
    }

    public function getMode(): string {
        if (null === $this->mode) {
            $this->mode = $this->getConfig()['mode'];
        }
        return $this->mode;
    }

    public function isProductionMode(): bool {
        return $this->getMode() === self::PRODUCTION_MODE;
    }

    public function isDevMode(): bool {
        return $this->getMode() === self::DEV_MODE;
    }

    public function isStagingMode(): bool {
        return $this->getMode() === self::STAGING_MODE;
    }

    public function isTestingMode(): bool {
        return $this->getMode() === self::TESTING_MODE;
    }

    /**
     * Returns true if site is not in any of mode (TESTING | PRODUCTION | STAGING | DEV).
     */
    public function isCustomMode(): bool {
        return !in_array(
            $this->mode,
            [self::PRODUCTION_MODE, self::STAGING_MODE, self::TESTING_MODE, self::DEV_MODE],
            true
        );
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
            throw new \LogicException('The loadConfig() must be called first');
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

    public function setConfigFileName(string $fileName) {
        $this->configFileName = $fileName;
    }

    public function getConfigFileName(): string {
        return $this->configFileName;
    }

    public function getConfigFilePath(): string {
        return $this->getConfigDirPath() . '/' . $this->configFileName;
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
