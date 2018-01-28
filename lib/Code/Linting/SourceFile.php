<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Linting;

use function Morpho\Core\baseDirPath;

class SourceFile extends \ArrayObject {
    /**
     * @var string
     */
    private $filePath;

    private $moduleDirPath;

    public function __construct(string $filePath) {
        $this->filePath = $filePath;
    }

    public function filePath(): string {
        return $this->filePath;
    }

    public function setModuleDirPath(string $dirPath): void {
        $this->moduleDirPath = $dirPath;
    }

    public function moduleDirPath(): string {
        if (NULL === $this->moduleDirPath) {
            $this->moduleDirPath = baseDirPath($this->filePath);
        }
        return $this->moduleDirPath;
    }
}