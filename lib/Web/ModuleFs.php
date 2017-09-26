<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace Morpho\Web;

use Morpho\Base\NotImplementedException;
use const Morpho\Core\LIB_DIR_NAME;
use const Morpho\Core\RC_DIR_NAME;
use const Morpho\Core\TMP_DIR_NAME;
use Morpho\Fs\Path;

class ModuleFs {
    public const CONTROLLER_DIR_NAME = 'Controller';
    public const LIB_DIR_NAME = LIB_DIR_NAME;
    public const META_FILE_NAME = 'composer.json';
    public const RC_DIR_NAME = RC_DIR_NAME;
    public const TEST_DIR_NAME = Fs::TEST_DIR_NAME;
    public const TMP_DIR_NAME = TMP_DIR_NAME;
    public const VIEW_DIR_NAME = 'view';

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
    private $controllerDirPath;

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
            $this->testDirPath = $this->dirPath() . '/' . self::TEST_DIR_NAME;
        }
        return $this->testDirPath;
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

    public function setControllerDirPath(string $dirPath): void {
        $this->controllerDirPath = $dirPath;
    }

    public function controllerDirPath(): string {
        if (null === $this->controllerDirPath) {
            $this->controllerDirPath = $this->libDirPath() . '/' . self::CONTROLLER_DIR_NAME;
        }
        return $this->controllerDirPath;
    }

    public function setLibDirPath(string $dirPath): void {
        $this->libDirPath = $dirPath;
    }

    public function libDirPath(): string {
        if (null === $this->libDirPath) {
            $this->libDirPath = $this->dirPath() . '/' . self::LIB_DIR_NAME;
        }
        return $this->libDirPath;
    }

    public function setRcDirPath(string $dirPath): void {
        $this->rcDirPath = $dirPath;
    }

    public function rcDirPath(): string {
        if (null === $this->rcDirPath) {
            $this->rcDirPath = $this->dirPath() . '/' . self::RC_DIR_NAME;
        }
        return $this->rcDirPath;
    }

    public function setTmpDirPath(string $dirPath): void {
        $this->tmpDirPath = Path::normalize($dirPath);
    }

    public function tmpDirPath(): string {
        if (null === $this->tmpDirPath) {
            $this->tmpDirPath = $this->dirPath() . '/' . self::TMP_DIR_NAME;
        }
        return $this->tmpDirPath;
    }

    public function setMetaFilePath(string $filePath): void {
        // @TODO
        throw new NotImplementedException();
    }

    public function metaFilePath(): string {
        return $this->dirPath() . '/' . self::META_FILE_NAME;
    }
}