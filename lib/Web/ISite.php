<?php
declare(strict_types=1);
namespace Morpho\Web;

interface ISite {
    public function name(): string;

    public function dirPath(): string;

    public function hostName(): ?string;

    public function setCacheDirPath(string $dirPath): void;

    public function cacheDirPath(): string;

    public function setConfigDirPath(string $dirPath): void;

    public function configDirPath(): string;

    public function setLogDirPath(string $dirPath): void;

    public function logDirPath(): string;

    public function setUploadDirPath(string $dirPath): void;

    public function uploadDirPath(): string;

    public function setTmpDirPath(string $dirPath): void;

    public function tmpDirPath(): string;

    public function setPublicDirPath(string $dirPath): void;

    public function publicDirPath(): string;

    public function useOwnPublicDir(): void;

    public function useCommonPublicDir(): void;

    public function usesOwnPublicDir(): bool;

    public function setConfigFilePath(string $filePath): void;

    public function configFilePath(): string;

    public function setConfigFileName(string $fileName): void;

    public function configFileName(): string;

    public function setConfig(array $config): void;

    public function config(): array;

    public function reloadConfig(): void;

    public function writeConfig(array $config): void;

    public function isFallbackMode(bool $flag = null): bool;

    public function fallbackConfigFilePath(): string;
}
