<?php
namespace Morpho\Web;

use const Morpho\Core\{
    CONTROLLER_DIR_NAME, LIB_DIR_NAME, MODULE_META_FILE_NAME, RC_DIR_NAME, TEST_DIR_NAME
};
use Morpho\Fs\Path;

trait TWithModuleDirs {
    /**
     * @var ?string
     */
    private $testDirPath;

    /**
     * @var ?string
     */
    private $viewDirPath;


    public function setTestDirPath(string $dirPath): void {
        $this->testDirPath = Path::normalize($dirPath);
    }

    public function testDirPath(): string {
        if (null === $this->testDirPath) {
            $this->testDirPath = $this->dirPath() . '/' . TEST_DIR_NAME;
        }
        return $this->testDirPath;
    }

    public function setViewDirPath(string $dirPath): void {
        $this->viewDirPath = $dirPath;
    }

    public function viewDirPath(): string {
        if (null === $this->viewDirPath) {
            $this->viewDirPath = $this->dirPath() . '/' . VIEW_DIR_NAME;
        }
        return $this->viewDirPath;
    }

    public function controllerDirPath(): string {
        return $this->dirPath() . '/' . CONTROLLER_DIR_NAME;
    }

    public function libDirPath(): string {
        return $this->dirPath() . '/' . LIB_DIR_NAME;
    }

    public function rcDirPath(): string {
        return $this->dirPath() . '/' . RC_DIR_NAME;
    }

    public function moduleMetaFilePath(): string {
        return $this->dirPath() . '/' . MODULE_META_FILE_NAME;
    }
}