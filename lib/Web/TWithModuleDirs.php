<?php
namespace Morpho\Web;

use const Morpho\Core\TEST_DIR_NAME;
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

    abstract public function dirPath(): string;

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
}