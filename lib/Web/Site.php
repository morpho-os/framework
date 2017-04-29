<?php
declare(strict_types = 1);

namespace Morpho\Web;

use function Morpho\Base\requireFile;
use const Morpho\Core\CACHE_DIR_NAME;
use const Morpho\Core\CONFIG_DIR_NAME;
use const Morpho\Core\CONFIG_FILE_NAME;
use const Morpho\Core\LOG_DIR_NAME;
use Morpho\Core\Module;
use const Morpho\Core\TMP_DIR_NAME;
use Morpho\Fs\File;
use Morpho\Fs\Path;

class Site extends Module {
    /**
     * @var ?array
     */
    private $config;

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
    private $tmpDirPath;

    /**
     * @var ?string
     */
    private $publicDirPath;

    /**
     * @var string
     */
    private $configFileName = self::CONFIG_FILE_NAME;

    /**
     * @var ?bool
     */
    private $fallbackConfigUsed;

    private $usesOwnPublicDir = false;

    public const CONFIG_FILE_NAME = CONFIG_FILE_NAME;
    public const FALLBACK_CONFIG_FILE_NAME = 'fallback.php';

    public function __construct(string $name, string $dirPath) {
        $this->name = $name;
        $this->setDirPath($dirPath);
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

    public function setTmpDirPath(string $dirPath): void {
        $this->tmpDirPath = Path::normalize($dirPath);
    }

    public function tmpDirPath(): string {
        if (null === $this->tmpDirPath) {
            $this->tmpDirPath = $this->dirPath() . '/' . TMP_DIR_NAME;
        }
        return $this->tmpDirPath;
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

    public function useOwnPublicDir(): void {
        $this->setPublicDirPath($this->dirPath() . '/' . PUBLIC_DIR_NAME);
        $this->usesOwnPublicDir = true;
    }
    
    public function useCommonPublicDir(): void {
        $this->setPublicDirPath(PUBLIC_DIR_PATH);
        $this->usesOwnPublicDir = false;
    }
    
    public function usesOwnPublicDir(): bool {
        return $this->usesOwnPublicDir;
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

    public function setConfig(array $config): self {
        $this->config = $config;
        return $this;
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

    public function isFallbackMode(): bool {
        $this->initConfig();
        return $this->fallbackConfigUsed;
    }

    public function fallbackConfigFilePath(): string {
        return $this->configDirPath() . '/' . self::FALLBACK_CONFIG_FILE_NAME;
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