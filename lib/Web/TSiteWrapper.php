<?php
declare(strict_types=1);
namespace Morpho\Web;

trait TSiteWrapper {
    /**
     * @var Site
     */
    private $site;

    public function setSite(ISite $site) {
        $this->site = $site;
    }

    public function hostName(): ?string {
        return $this->site->hostName();
    }

    public function setCacheDirPath(string $dirPath): void {
        $this->site->setCacheDirPath($dirPath);
    }

    public function cacheDirPath(): string {
        return $this->site->cacheDirPath();
    }

    public function setConfigDirPath(string $dirPath): void {
        $this->site->setConfigDirPath($dirPath);
    }

    public function configDirPath(): string {
        return $this->site->configDirPath();
    }

    public function setLogDirPath(string $dirPath): void {
        $this->site->setLogDirPath($dirPath);
    }

    public function logDirPath(): string {
        return $this->site->logDirPath();
    }

    public function setUploadDirPath(string $dirPath): void {
        $this->site->setUploadDirPath($dirPath);
    }

    public function uploadDirPath(): string {
        return $this->site->uploadDirPath();
    }

    public function setTmpDirPath(string $dirPath): void {
        $this->site->setTmpDirPath($dirPath);
    }

    public function tmpDirPath(): string {
        return $this->site->tmpDirPath();
    }

    public function setPublicDirPath(string $dirPath): void {
        $this->site->setPublicDirPath($dirPath);
    }

    public function publicDirPath(): string {
        return $this->site->publicDirPath();
    }

    public function useOwnPublicDir(): void {
        $this->site->useOwnPublicDir();
    }

    public function useCommonPublicDir(): void {
        $this->site->useCommonPublicDir();
    }

    public function usesOwnPublicDir(): bool {
        return $this->site->usesOwnPublicDir();
    }

    public function setConfigFilePath(string $filePath): void {
        $this->site->setConfigFilePath($filePath);
    }

    public function configFilePath(): string {
        return $this->site->configFilePath();
    }

    public function setConfigFileName(string $fileName): void {
        $this->site->setConfigFileName($fileName);
    }

    public function configFileName(): string {
        return $this->site->configFileName();
    }

    public function setConfig(array $config): void {
        $this->site->setConfig($config);
    }

    public function config(): array {
        return $this->site->config();
    }

    public function reloadConfig(): void {
        $this->site->reloadConfig();
    }

    public function writeConfig(array $config): void {
        $this->site->writeConfig($config);
    }

    public function isFallbackMode(bool $flag = null): bool {
        return $this->site->isFallbackMode($flag);
    }

    public function fallbackConfigFilePath(): string {
        return $this->site->fallbackConfigFilePath();
    }
}