<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace Morpho\Web;

use const Morpho\Core\CONTROLLER_DIR_NAME;
use const Morpho\Core\TMP_DIR_NAME;
use Morpho\Fs\Path;
use Morpho\Core\ModuleFs as BaseModuleFs;

class ModuleFs extends BaseModuleFs {
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

    public function setViewDirPath(string $dirPath): void {
        $this->viewDirPath = $dirPath;
    }

    public function viewDirPath(): string {
        if (null === $this->viewDirPath) {
            $this->viewDirPath = $this->dirPath() . '/' . VIEW_DIR_NAME;
        }
        return $this->viewDirPath;
    }

    public function setControllerDirPath(string $dirPath): void {
        $this->controllerDirPath = $dirPath;
    }

    public function controllerDirPath(): string {
        if (null === $this->controllerDirPath) {
            $this->controllerDirPath = $this->libDirPath() . '/' . CONTROLLER_DIR_NAME;
        }
        return $this->controllerDirPath;
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
}