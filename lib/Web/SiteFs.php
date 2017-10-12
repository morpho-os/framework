<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace Morpho\Web;

use const Morpho\Core\CACHE_DIR_NAME;
use const Morpho\Core\CONFIG_DIR_NAME;
use const Morpho\Core\CONFIG_FILE_NAME;
use const Morpho\Core\LOG_DIR_NAME;
use Morpho\Fs\File;
use Morpho\Fs\Path;

class SiteFs extends ModuleFs {
    /**
     * @var ?string
     */
    private $cacheDirPath;

    /**
     * @var ?string
     */
    private $configDirPath;

    /**
     * @var ?string
     */
    private $logDirPath;

    /**
     * @var ?string
     */
    private $uploadDirPath;

    /**
     * @var ?string
     */
    private $publicDirPath;

    protected const CONFIG_FILE_NAME = CONFIG_FILE_NAME;
    protected const FALLBACK_CONFIG_FILE_NAME = FALLBACK_CONFIG_FILE_NAME;

    public function writeConfig(array $newConfig): void {
        File::writePhpVar($this->configFilePath(), $newConfig);
    }

    public function deleteConfigFile(): void {
        $configFilePath = $this->configFilePath();
        if (is_file($configFilePath)) {
            unlink($configFilePath);
        }
    }

    public function canLoadConfigFile(): bool {
        return $this->configFileExists();// && $this->configFileReadable();
    }

    public function setConfigDirPath(string $dirPath): void {
        $this->configDirPath = Path::normalize($dirPath);
    }

    public function configDirPath(): string {
        if (null === $this->configDirPath) {
            $this->configDirPath = $this->dirPath() . '/' . CONFIG_DIR_NAME;
        }
        return $this->configDirPath;
    }

    public function loadConfigFile(): array {
        return require $this->configFilePath();
    }

    public function loadFallbackConfigFile(): array {
        return require $this->fallbackConfigFilePath();
    }

    public function setCacheDirPath(string $dirPath): void {
        $this->cacheDirPath = Path::normalize($dirPath);
    }

    public function cacheDirPath(): string {
        if (null === $this->cacheDirPath) {
            $this->cacheDirPath = $this->dirPath . '/' . CACHE_DIR_NAME;
        }
        return $this->cacheDirPath;
    }

    public function setLogDirPath(string $dirPath): void {
        $this->logDirPath = Path::normalize($dirPath);
    }

    public function logDirPath(): string {
        if (null === $this->logDirPath) {
            $this->logDirPath = $this->dirPath() . '/' . LOG_DIR_NAME;
        }
        return $this->logDirPath;
    }

    public function setUploadDirPath(string $dirPath): void {
        $this->uploadDirPath = Path::normalize($dirPath);
    }

    public function uploadDirPath(): string {
        if (null === $this->uploadDirPath) {
            $this->uploadDirPath = $this->dirPath() . '/' . UPLOAD_DIR_NAME;
        }
        return $this->uploadDirPath;
    }

    public function setPublicDirPath(string $dirPath): void {
        $this->publicDirPath = Path::normalize($dirPath);
    }

    public function publicDirPath(): string {
        if (null === $this->publicDirPath) {
            $this->publicDirPath = $this->dirPath() . '/' . PUBLIC_DIR_NAME;
        }
        return $this->publicDirPath;
    }

    protected function configFilePath(): string {
        return $this->configDirPath() . '/' . self::CONFIG_FILE_NAME;
    }

    protected function fallbackConfigFilePath(): string {
        return $this->configDirPath() . '/' . self::FALLBACK_CONFIG_FILE_NAME;
    }

/*    protected function configFileReadable(): bool {
        return is_readable($this->configFilePath());
    }*/

    protected function configFileExists(): bool {
        return is_file($this->configFilePath());
    }
}