<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace Morpho\Web;

use Morpho\Fs\File;
use Morpho\Fs\Path;

class SiteFs extends ModuleFs {
    public const CACHE_DIR_NAME = 'cache';
    public const CONFIG_DIR_NAME = Fs::CONFIG_DIR_NAME;
    public const LOG_DIR_NAME = 'log';
    public const PUBLIC_DIR_NAME = Fs::PUBLIC_DIR_NAME;
    public const UPLOAD_DIR_NAME = 'upload';

    public const FALLBACK_CONFIG_FILE_NAME = 'fallback.php';
    public const CONFIG_FILE_NAME = Fs::CONFIG_FILE_NAME;

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
            $this->configDirPath = $this->dirPath() . '/' . self::CONFIG_DIR_NAME;
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
            $this->cacheDirPath = $this->dirPath . '/' . self::CACHE_DIR_NAME;
        }
        return $this->cacheDirPath;
    }

    public function setLogDirPath(string $dirPath): void {
        $this->logDirPath = Path::normalize($dirPath);
    }

    public function logDirPath(): string {
        if (null === $this->logDirPath) {
            $this->logDirPath = $this->dirPath() . '/' . self::LOG_DIR_NAME;
        }
        return $this->logDirPath;
    }

    public function setUploadDirPath(string $dirPath): void {
        $this->uploadDirPath = Path::normalize($dirPath);
    }

    public function uploadDirPath(): string {
        if (null === $this->uploadDirPath) {
            $this->uploadDirPath = $this->dirPath() . '/' . self::UPLOAD_DIR_NAME;
        }
        return $this->uploadDirPath;
    }

    public function setPublicDirPath(string $dirPath): void {
        $this->publicDirPath = Path::normalize($dirPath);
    }

    public function publicDirPath(): string {
        if (null === $this->publicDirPath) {
            $this->publicDirPath = $this->dirPath() . '/' . self::PUBLIC_DIR_NAME;
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