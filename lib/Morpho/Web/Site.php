<?php
namespace Morpho\Web;

use Morpho\Base\Must;
use function Morpho\Base\requireFile;
use Morpho\Fs\File;
use Morpho\Fs\Path;

class Site {
    private $name;

    private $dirPath;

    private $config;

    private $cacheDirPath;

    private $configDirPath;

    private $logDirPath;

    private $uploadDirPath;

    private $publicDirPath;

    private $configFileName = self::CONFIG_FILE_NAME;

    private $viewDirPath;

    private $fallbackConfigUsed;

    public const CONFIG_FILE_NAME = CONFIG_FILE_NAME;
    public const FALLBACK_CONFIG_FILE_NAME = 'fallback.php';

    public function __construct(array $options = []) {
        Must::haveOnlyKeys($options, ['dirPath', 'name']);
        if (isset($options['dirPath'])) {
            $this->setDirPath($options['dirPath']);
        }
        if (isset($options['name'])) {
            $this->setName($options['name']);
        }
    }

    public function setDirPath(string $dirPath): void {
        $this->dirPath = $dirPath;
    }

    public function dirPath(): string {
        return $this->dirPath;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function name(): string {
        return $this->name;
    }

    public function setCacheDirPath(string $dirPath): void {
        $this->cacheDirPath = Path::normalize($dirPath);
    }

    public function cacheDirPath(): string {
        if (null === $this->cacheDirPath) {
            $this->cacheDirPath = $this->dirPath() . '/' . CACHE_DIR_NAME;
        }
        return $this->cacheDirPath;
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
            $this->publicDirPath = PUBLIC_DIR_PATH;
        }
        return $this->publicDirPath;
    }

    public function setConfig(array $config): void {
        $this->config = $config;
    }

    public function config(): array {
        $this->initConfig();
        return $this->config;
    }

    public function reloadConfig(): void {
        $this->config = null;
        $this->initConfig();
    }

    public function writeConfig(array $config): void {
        File::writePhpVar($this->configFilePath(), $config);
        $this->config = null;
    }

    public function setConfigFilePath(string $filePath): void {
        $this->setConfigDirPath(dirname($filePath));
        $this->setConfigFileName(basename($filePath));
    }

    public function configFilePath(): string {
        return $this->configDirPath() . '/' . $this->configFileName;
    }

    public function setConfigFileName(string $fileName): void {
        $this->configFileName = $fileName;
    }

    public function configFileName(): string {
        return $this->configFileName;
    }

    public function fallbackConfigUsed(): bool {
        $this->initConfig();
        return $this->fallbackConfigUsed;
    }

    public function fallbackConfigFilePath(): string {
        return $this->configDirPath() . '/' . self::FALLBACK_CONFIG_FILE_NAME;
    }

    public function setViewDirPath(string $dirPath): void {
        $this->viewDirPath = $dirPath;
    }

    public function viewDirPath(): string {
        if (null === $this->viewDirPath) {
            $this->viewDirPath = $this->dirPath . '/' . VIEW_DIR_NAME;
        }
        return $this->viewDirPath;
    }

    private function initConfig(): void {
        if (null === $this->config) {
            $filePath = $this->configFilePath();
            if (!file_exists($filePath) || !is_readable($filePath)) {
                $filePath = $this->fallbackConfigFilePath();
                $this->fallbackConfigUsed = true;
            } else {
                $this->fallbackConfigUsed = false;
            }

            $this->config = requireFile($filePath);
        }
    }
}