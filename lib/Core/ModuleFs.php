<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace Morpho\Core;

use Morpho\Base\NotImplementedException;
use Morpho\Fs\Path;

class ModuleFs {
    /**
     * @var string
     */
    protected $dirPath;

    /**
     * @var ?string
     */
    private $testDirPath;

    /**
     * @var ?string
     */
    private $libDirPath;

    /**
     * @var ?string
     */
    private $rcDirPath;

    public function __construct(string $dirPath) {
        $this->dirPath = $dirPath;
    }

    public function setDirPath(string $dirPath): void {
        $this->dirPath = $dirPath;
    }

    public function dirPath(): string {
        return $this->dirPath;
    }

    public function setTestDirPath(string $dirPath): void {
        $this->testDirPath = Path::normalize($dirPath);
    }

    public function testDirPath(): string {
        if (null === $this->testDirPath) {
            $this->testDirPath = $this->dirPath() . '/' . TEST_DIR_NAME;
        }
        return $this->testDirPath;
    }

    public function setLibDirPath(string $dirPath): void {
        $this->libDirPath = $dirPath;
    }

    public function libDirPath(): string {
        if (null === $this->libDirPath) {
            $this->libDirPath = $this->dirPath() . '/' . LIB_DIR_NAME;
        }
        return $this->libDirPath;
    }

    public function setRcDirPath(string $dirPath): void {
        $this->rcDirPath = $dirPath;
    }

    public function rcDirPath(): string {
        if (null === $this->rcDirPath) {
            $this->rcDirPath = $this->dirPath() . '/' . RC_DIR_NAME;
        }
        return $this->rcDirPath;
    }

    public function setMetaFilePath(string $filePath): void {
        // @TODO
        throw new NotImplementedException();
    }

    public function metaFilePath(): string {
        return $this->dirPath() . '/' . META_FILE_NAME;
    }
}