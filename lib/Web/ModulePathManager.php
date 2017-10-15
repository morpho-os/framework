<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */

//declare(strict_types=1);
namespace Morpho\Web;

use const Morpho\Web\LIB_DIR_NAME;
use const Morpho\Web\META_FILE_NAME;
use const Morpho\Web\RC_DIR_NAME;
use const Morpho\Web\TEST_DIR_NAME;
use const Morpho\Web\TMP_DIR_NAME;
use Morpho\Fs\Path;

class ModulePathManager {
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
    private $viewDirPath;

    /**
     * @var ?string
     */
    private $tmpDirPath;

    /**
     * @var ?string
     */
    private $libDirPath;

    /**
     * @var ?string
     */
    private $controllerDirPath;

    /**
     * @var ?string
     */
    private $rcDirPath;

    public const VIEW_DIR_NAME = 'view';

    public function __construct(string $dirPath) {
        $this->dirPath = $dirPath;
    }

    public function dirPath(): string {
        return $this->dirPath;
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

    public function setControllerDirPath(string $dirPath): void {
        $this->controllerDirPath = $dirPath;
    }

    public function controllerDirPath(): string {
        if (null === $this->controllerDirPath) {
            $this->controllerDirPath = $this->libDirPath() . '/Web';
        }
        return $this->controllerDirPath;
    }

    public function setViewDirPath(string $dirPath): void {
        $this->viewDirPath = $dirPath;
    }

    public function viewDirPath(): string {
        if (null === $this->viewDirPath) {
            $this->viewDirPath = $this->dirPath() . '/' . self::VIEW_DIR_NAME;
        }
        return $this->viewDirPath;
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

    public function setTestDirPath(string $dirPath): void {
        $this->testDirPath = Path::normalize($dirPath);
    }

    public function testDirPath(): string {
        if (null === $this->testDirPath) {
            $this->testDirPath = $this->dirPath() . '/' . TEST_DIR_NAME;
        }
        return $this->testDirPath;
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

    public function metaFilePath(): string {
        return $this->dirPath() . '/' . META_FILE_NAME;
    }
}