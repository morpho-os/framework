<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\Uri;

use function Morpho\Base\endsWith;
use function Morpho\Base\startsWith;
use function Morpho\Base\contains;
use Morpho\Fs\Path as FsPath;

class Path implements IUriComponent {
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
     * This method taken from https://github.com/zendframework/zend-uri/blob/master/src/Uri.php and changed to match our requirements.
     *
     * @link      http://github.com/zendframework/zf2 for the canonical source repository
     * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
     * @license   http://framework.zend.com/license/new-bsd New BSD License
     *
     * Remove any extra dot segments (/../, /./) from a path
     *
     * Algorithm is adapted from RFC-3986 section 5.2.4
     * (@link http://tools.ietf.org/html/rfc3986#section-5.2.4)
     *
     * @TODO   consider optimizing
     *
     * @param string|Path
     */
    public static function removeDotSegments($path): string {
        if (!is_string($path)) {
            /** @var Path $path */
            $path = $path->toStr(false);
        }

        $output = '';

        while ($path) {
            if ($path == '..' || $path == '.') {
                break;
            }

            switch (true) {
                case ($path == '/.'):
                    $path = '/';
                    break;
                case ($path == '/..'):
                    $path   = '/';
                    $lastSlashPos = mb_strrpos($output, '/', -1);
                    if (false === $lastSlashPos) {
                        break;
                    }
                    $output = mb_substr($output, 0, $lastSlashPos);
                    break;
                case (mb_substr($path, 0, 4) == '/../'):
                    $path   = '/' . mb_substr($path, 4);
                    $lastSlashPos = mb_strrpos($output, '/', -1);
                    if (false === $lastSlashPos) {
                        break;
                    }
                    $output = mb_substr($output, 0, $lastSlashPos);
                    break;
                case (mb_substr($path, 0, 3) == '/./'):
                    $path = mb_substr($path, 2);
                    break;
                case (mb_substr($path, 0, 2) == './'):
                    $path = mb_substr($path, 2);
                    break;
                case (mb_substr($path, 0, 3) == '../'):
                    $path = mb_substr($path, 3);
                    break;
                default:
                    $slash = mb_strpos($path, '/', 1);
                    if ($slash === false) {
                        $seg = $path;
                    } else {
                        $seg = mb_substr($path, 0, $slash);
                    }

                    $output .= $seg;
                    $path = mb_substr($path, mb_strlen($seg));
                    break;
            }
        }

        return $output;
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
                $this->relPath = FsPath::toRelative($this->path, $this->basePath);
            }
        }
        return $this->relPath;
    }

    public function isRel(): bool {
        return $this->path === '' || $this->path[0] !== '/';
    }
}