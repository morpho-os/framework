<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace Morpho\Web\View;

use Morpho\Fs\Path;

class View {
    /**
     * @var string
     */
    protected $name;
    /**
     * @var array
     */
    protected $vars;
    /**
     * @var null|string
     */
    protected $dirPath;

    /**
     * @param array|null|\ArrayObject $vars
     */
    public function __construct(string $name, $vars = null) {
        $this->name = $name;
        if (null === $vars) {
            $vars = [];
        }
        if (is_array($vars)) {
            $this->vars = new \ArrayObject($vars);
        } else {
            $this->vars = $vars;
        }
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function name(): string {
        return $this->name;
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

    public function vars(): \ArrayObject {
        return $this->vars;
    }
}