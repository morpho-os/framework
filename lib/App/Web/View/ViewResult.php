<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

use Morpho\App\IActionResult;

class ViewResult implements IActionResult {
    /**
     * @var string
     */
    protected $path;

    /**
     * @var null|string
     */
    //protected $dirPath;

    /**
     * @var array|\ArrayObject
     */
    protected $vars;

    /**
     * @var ViewResult|null
     */
    private $parent;

    /**
     * @param string $path
     * @param array|null|\ArrayObject $vars
     * @param ViewResult|null|string $parent
     */
    public function __construct(string $path, $vars = null, $parent = null) {
        $this->path = $path;
        if (null === $vars) {
            $vars = [];
        }
        $this->vars = \is_array($vars) ? new \ArrayObject($vars) : $vars;
        $this->parent = null !== $parent ? $this->normalizeParent($parent) : $parent;
    }

    /*public function setName(string $name): void {
        $this->name = $name;
    }*/

/*    public function name(): string {
        return $this->name;
    }*/

    public function vars(): \ArrayObject {
        return $this->vars;
    }

/*    public function setDirPath(string $dirPath): void {
        $this->dirPath = $dirPath;
    }

    public function dirPath(): ?string {
        return $this->dirPath;
    }*/

    public function setPath(string $path): void {
        $this->path = $path;
    }

    public function path(): string {
        return $this->path;//Path::combine($this->dirPath, $this->name);
    }

    /**
     * @param string|ViewResult $viewResult
     */
    public function setParent($viewResult): void {
        $this->parent = $this->normalizeParent($viewResult);
    }

    public function parent(): ?ViewResult {
        return $this->parent;
    }

    private function normalizeParent($parent): ViewResult {
        return is_string($parent) ? new ViewResult($parent) : $parent;
    }
}
