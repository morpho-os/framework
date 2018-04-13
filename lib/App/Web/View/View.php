<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

use Morpho\Fs\Path;

class View extends \ArrayObject {
    /**
     * @var string
     */
    protected $name;

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
        parent::__construct($vars);
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
}
