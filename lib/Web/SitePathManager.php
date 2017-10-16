<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace Morpho\Web;

use const Morpho\Web\CACHE_DIR_NAME;
use const Morpho\Core\CONFIG_DIR_NAME;
use const Morpho\Web\CONFIG_FILE_NAME;
use const Morpho\Web\LOG_DIR_NAME;
use Morpho\Fs\File;
use Morpho\Fs\Path;

class SitePathManager extends ModulePathManager {
    /**
     * @var ?string
     */
    private $cacheDirPath;

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
}