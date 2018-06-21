<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

use Morpho\App\IActionResult;
use Morpho\Fs\Path;

class View implements IActionResult {
    /**
     * @var string
     */
    protected $name;

    /**
     * @var null|string
     */
    protected $dirPath;

    /**
     * @var array|\ArrayObject
     */
    protected $vars;

    /**
     * @var View|null
     */
    private $parent;

    /**
     * @param string $name
     * @param array|null|\ArrayObject $vars
     * @param View|null $parent
     */
    public function __construct(string $name, $vars = null, View $parent = null) {
        $this->name = $name;
        if (null === $vars) {
            $vars = [];
        }
        $this->vars = \is_array($vars) ? new \ArrayObject($vars) : $vars;
        $this->parent = $parent;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function name(): string {
        return $this->name;
    }

    public function vars(): \ArrayObject {
        return $this->vars;
    }

    public function setDirPath(string $dirPath): void {
        $this->dirPath = $dirPath;
    }

    public function dirPath(): ?string {
        return $this->dirPath;
    }

    public function path(): string {
        return Path::combine($this->dirPath, $this->name);
    }
    
    public function setParent(View $view): void {
        $this->parent = $view;
    }

    public function parent(): ?View {
        return $this->parent;
    }
}