<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\Uri;

use function Morpho\Base\endsWith;
use function Morpho\Base\startsWith;
use function Morpho\Base\contains;
use Morpho\Core\Path as BasePath;

class Path extends BasePath implements IUriComponent {
    /**
     * @var ?string
     */
    protected $basePath;

    /**
     * @var ?string
     */
    protected $relPath;

    /**
     * @var string
     */
    protected $path;

    public function __construct(string $path) {
        $this->path = $path;
    }

    public function toStr(bool $encode): string {
        if ($encode) {
            return str_replace('%2F', '/', rawurlencode($this->path));
        }
        return $this->path;
    }

    public function startsWith(string $path): bool {
        return startsWith($this->path, $path);
    }

    /**
     * @param string $path
     */
    public function endsWith(string $path): bool {
        return endsWith($this->path, $path);
    }

    public function contains(string $path): bool {
        return contains($this->path, $path);
    }

    /**
     * @param string $basePath
     */
    public function setBasePath(string $basePath): void {
        if (!$this->startsWith($basePath)) {
            throw new \RuntimeException('The base path is not begging of the path');
        }
        $this->basePath = $basePath;
    }

    public function basePath(): ?string {
        return $this->basePath;
    }

    public function relPath(): ?string {
        if (null === $this->relPath) {
            if (null !== $this->basePath) {
                $this->relPath = static::toRel($this->path, $this->basePath);
            }
        }
        return $this->relPath;
    }

    public function isRel(): bool {
        return $this->path === '' || $this->path[0] !== '/';
    }
}